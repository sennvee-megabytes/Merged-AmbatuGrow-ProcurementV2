<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\RequisitionComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Requisitions where the CURRENT active step (lowest step_order still
        // pending) is assigned to this user - i.e. it's genuinely their turn.
        $myQueueIds = Requisition::with('approvalSteps')
            ->where('status', 'pending_approval')
            ->whereHas('approvalSteps', function ($q) use ($userId) {
                $q->where('approver_id', $userId)->where('status', 'pending');
            })
            ->get()
            ->filter(function (Requisition $r) use ($userId) {
                if ($r->approval_type === 'parallel') {
                    return true;
                }
                $current = $r->currentStep();

                return $current && (int)$current->approver_id === (int)$userId;
            })
            ->pluck('id')
            ->unique()
            ->values();

        $pendingForMe = Requisition::with(['requestor', 'approvalSteps.approver', 'items'])
            ->whereIn('id', $myQueueIds)
            ->orderByDesc('urgency')
            ->latest()
            ->get();

        $history = Requisition::with(['requestor', 'approvalSteps'])
            ->whereIn('status', ['approved', 'rejected'])
            ->whereHas('approvalSteps', fn ($q) => $q->where('approver_id', $userId))
            ->latest()
            ->limit(25)
            ->get();

        $selectedId = $request->get('requisition') ?? $pendingForMe->first()?->id;
        $selected = $selectedId
            ? Requisition::with(['requestor', 'items', 'approvalSteps.approver', 'comments.user'])->find($selectedId)
            : null;

        $stats = [
            'pending_count' => $pendingForMe->count(),
            'value_awaiting' => $pendingForMe->sum('total'),
        ];

        $currentUser = Auth::user();
        $delegateRoles = [];
        if ($currentUser && $currentUser->role === 'manager') {
            $delegateRoles = ['finance_manager', 'department_head'];
        } elseif ($currentUser && in_array($currentUser->role, ['finance_manager', 'department_head'])) {
            $delegateRoles = ['manager'];
        } else {
            $delegateRoles = ['manager', 'finance_manager', 'department_head'];
        }

        $delegates = User::whereIn('role', $delegateRoles)
            ->where('id', '!=', $userId)
            ->where('name', '!=', 'Robin Lapa')
            ->where('username', '!=', 'robin.lapa')
            ->get();

        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        if (app()->environment('testing') && request()->has('requisition') && request()->get('requisition') == 1) {
            // debug
        }

        return view('approvals.index', compact('pendingForMe', 'history', 'selected', 'stats', 'delegates', 'suppliers'));
    }

    public function show(Requisition $requisition)
    {
        return redirect()->route('approvals.index', ['requisition' => $requisition->id]);
    }

    public function act(Request $request, Requisition $requisition)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject,delegate'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'delegate_to' => ['nullable', 'required_if:decision,delegate', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $step = $requisition->approvalSteps()
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('step_order')
            ->first();

        abort_if(! $step, 403, 'You do not have a pending approval step for this requisition.');

        abort_unless($step->canBeActedOnBy($user, $requisition), 403, 'You are not authorized to act on this approval step.');

        DB::transaction(function () use ($data, $requisition, $step, $user) {
            if ($data['decision'] === 'approve') {
                $step->update([
                    'status' => 'approved',
                    'comment' => $data['comment'] ?? null,
                    'acted_at' => now(),
                ]);

                $this->ensureNextApprovalStep($requisition, $step);

                $remaining = $requisition->approvalSteps()->where('status', 'pending')->count();
                if ($remaining === 0) {
                    $requisition->update(['status' => 'approved']);
                    $this->autoGeneratePurchaseOrder($requisition);
                }
            } elseif ($data['decision'] === 'reject') {
                $step->update([
                    'status' => 'rejected',
                    'comment' => $data['comment'] ?? null,
                    'acted_at' => now(),
                ]);
                $requisition->update(['status' => 'rejected']);
                \App\Models\PurchaseOrder::where('requisition_id', $requisition->id)->update(['status' => 'cancelled']);
            } elseif ($data['decision'] === 'delegate') {
                $delegateUser = User::find($data['delegate_to']);
                if ($user->role === 'manager') {
                    if (!in_array($delegateUser->role, ['finance_manager', 'department_head'])) {
                        abort(403, 'A manager can only delegate to Finance Manager or Department Head.');
                    }
                } elseif (in_array($user->role, ['finance_manager', 'department_head'])) {
                    if ($delegateUser->role !== 'manager') {
                        abort(403, 'This role can only delegate to a Manager.');
                    }
                } else {
                    $allowedUsernames = ['sarah.jenkins', 'finance.manager', 'johny.papa'];
                    $allowedNames = ['Sarah Jenkins', 'Michael Finn', 'Johny Papa'];
                    if (!$delegateUser || (!in_array($delegateUser->username, $allowedUsernames) && !in_array($delegateUser->name, $allowedNames) && !in_array($delegateUser->role, ['manager', 'finance_manager', 'department_head']))) {
                        abort(403, 'Delegation is only permitted to authorized approval workers.');
                    }
                }
                $step->update(['approver_id' => $data['delegate_to']]);
            }

            if (! empty($data['comment'])) {
                RequisitionComment::create([
                    'requisition_id' => $requisition->id,
                    'user_id' => $user->id,
                    'body' => $data['comment'],
                ]);
            }
        });

        $message = match ($data['decision']) {
            'approve' => 'Requisition step approved.',
            'reject' => 'Requisition rejected.',
            'delegate' => 'Approval delegated.',
        };

        return redirect()->route('approvals.index')->with('status', $message);
    }

    protected function ensureNextApprovalStep(Requisition $requisition, \App\Models\ApprovalStep $actedStep): void
    {
        $michael = User::where('username', 'finance.manager')->first() ?? User::where('role', 'finance_manager')->first();
        $johny = User::where('username', 'johny.papa')->first() ?? User::where('role', 'department_head')->first();

        if ((int)$actedStep->step_order === 1) {
            if ($michael) {
                $step2s = $requisition->approvalSteps()->where('step_order', 2)->get();
                if ($step2s->count() > 1) {
                    foreach ($step2s->slice(1) as $dup) {
                        $dup->delete();
                    }
                }

                $step2 = $requisition->approvalSteps()->where('step_order', 2)->first();
                if ($step2) {
                    $step2->update([
                        'approver_id' => $michael->id,
                        'step_type' => 'finance_approval',
                        'label' => 'Finance Manager Approval',
                        'description' => 'Level 2: Michael Finn (Finance Manager)',
                        'status' => $step2->status === 'approved' ? 'approved' : 'pending',
                    ]);
                } else {
                    \App\Models\ApprovalStep::create([
                        'requisition_id' => $requisition->id,
                        'step_order' => 2,
                        'step_type' => 'finance_approval',
                        'label' => 'Finance Manager Approval',
                        'description' => 'Level 2: Michael Finn (Finance Manager)',
                        'required' => true,
                        'approver_id' => $michael->id,
                        'status' => 'pending',
                    ]);
                }
            }
        } elseif ((int)$actedStep->step_order === 2) {
            if ($johny) {
                $step3s = $requisition->approvalSteps()->where('step_order', 3)->get();
                if ($step3s->count() > 1) {
                    foreach ($step3s->slice(1) as $dup) {
                        $dup->delete();
                    }
                }

                $step3 = $requisition->approvalSteps()->where('step_order', 3)->first();
                if ($step3) {
                    $step3->update([
                        'approver_id' => $johny->id,
                        'step_type' => 'department_head_approval',
                        'label' => 'Head Approval',
                        'description' => 'Level 3: Johny Papa (Head)',
                        'status' => $step3->status === 'approved' ? 'approved' : 'pending',
                    ]);
                } else {
                    \App\Models\ApprovalStep::create([
                        'requisition_id' => $requisition->id,
                        'step_order' => 3,
                        'step_type' => 'department_head_approval',
                        'label' => 'Head Approval',
                        'description' => 'Level 3: Johny Papa (Head)',
                        'required' => true,
                        'approver_id' => $johny->id,
                        'status' => 'pending',
                    ]);
                }
            }
        }
    }

    protected function autoGeneratePurchaseOrder(Requisition $requisition): void
    {
        if (\App\Models\PurchaseOrder::where('requisition_id', $requisition->id)->exists()) {
            return;
        }

        $supplier = null;
        if ($requisition->supplier_id) {
            $supplier = \App\Models\Supplier::find($requisition->supplier_id);
        }
        if (!$supplier) {
            $supplier = \App\Models\Supplier::where('status', 'active')->first() ?? \App\Models\Supplier::first();
        }
        if (!$supplier) {
            $supplier = \App\Models\Supplier::create([
                'name' => 'Ambatugrow General Supplier',
                'status' => 'active',
                'contact_person' => 'Sales Department',
                'email' => 'sales@ambatugrow.test',
                'phone' => '(000) 000-0000',
                'address' => 'Indang, Cavite',
                'city' => 'Cavite',
            ]);
        }

        $year = date('Y');
        $latest = \App\Models\PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")
            ->orderByRaw('length(po_number) desc, po_number desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('-', $latest->po_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        $poNumber = 'PO-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $po = \App\Models\PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $supplier->id,
            'requisition_id' => $requisition->id,
            'status' => 'approved',
            'total' => $requisition->total,
            'expected_delivery' => now()->addDays(7),
            'issued_at' => now(),
            'created_by' => $requisition->requestor_id,
        ]);

        foreach ($requisition->items as $item) {
            $sku = $item->sku ?? ('ITEM-' . sprintf('%05d', $item->id));

            $po->items()->create([
                'sku' => $sku,
                'name' => $item->name,
                'quantity' => $item->qty,
                'unit' => $item->unit ?? 'Unit',
                'unit_price' => $item->unit_price,
                'line_total' => $item->total,
            ]);

            $product = \App\Models\Product::where('name', $item->name)->first();
            if (!$product) {
                $cat = \App\Models\Category::first() ?? \App\Models\Category::create(['category_name' => 'General']);
                $uom = \App\Models\UnitOfMeasure::first() ?? \App\Models\UnitOfMeasure::create(['uom_code' => 'Unit', 'uom_name' => 'Unit']);
                $curr = \App\Models\Currency::first() ?? \App\Models\Currency::create(['currency_code' => 'PHP', 'currency_name' => 'Philippine Peso', 'exchange_rate' => 1.0000]);

                $product = \App\Models\Product::create([
                    'sku' => $sku,
                    'name' => $item->name,
                    'category_id' => $cat->id,
                    'uom_id' => $uom->id,
                    'currency_id' => $curr->id,
                    'base_price' => $item->unit_price,
                    'min_quantity_threshold' => 10,
                    'lead_time_days' => 3,
                ]);
            }

            \App\Models\POItem::create([
                'po_id' => $po->id,
                'product_id' => $product->id,
                'quantity' => $item->qty,
                'uom_id' => $product->uom_id,
                'unit_price' => $item->unit_price,
            ]);
        }
    }
}
