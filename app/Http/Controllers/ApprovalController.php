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

        // Run deduplication & step fix on pending requisitions to prevent duplicate/invalid steps
        $pendingReqs = Requisition::where('status', 'pending_approval')->get();
        foreach ($pendingReqs as $pReq) {
            $this->deduplicateAndFixSteps($pReq);
        }

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

        if ($selected) {
            $this->deduplicateAndFixSteps($selected);
            $selected->load(['requestor', 'items', 'approvalSteps.approver', 'comments.user']);
        }

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
            ->get();

        // Log delegate IDs during testing to aid debugging
        if (app()->environment('testing')) {
            \Log::info('Delegates IDs: '.json_encode($delegates->pluck('id')->toArray()));
        }

        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        if (app()->environment('testing') && request()->has('requisition') && request()->get('requisition') == 1) {
            // debug
        }

        return view('approvals.index', compact('pendingForMe', 'history', 'selected', 'stats', 'delegates', 'suppliers'));
    }

    public function pendingCount()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['count' => 0]);
        }

        $pendingReqs = Requisition::where('status', 'pending_approval')->get();
        foreach ($pendingReqs as $pReq) {
            $this->deduplicateAndFixSteps($pReq);
        }

        $myQueueIds = Requisition::with('approvalSteps')
            ->where('status', 'pending_approval')
            ->whereHas('approvalSteps', function ($q) use ($userId) {
                $q->where('approver_id', $userId)->where('status', 'pending');
            })
            ->get()
            ->filter(function (Requisition $r) use ($userId) {
                $current = $r->currentStep();
                return $current && (int)$current->approver_id === (int)$userId;
            })
            ->pluck('id')
            ->unique();

        return response()->json(['count' => $myQueueIds->count()]);
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
        $this->deduplicateAndFixSteps($requisition);

        $currentStep = $requisition->currentStep();
        $step = $requisition->approvalSteps()
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('step_order')
            ->first();

        if (!$step && $currentStep && $currentStep->canBeActedOnBy($user, $requisition)) {
            $step = $currentStep;
        }

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

    public static function resolveWorkflowUsers(): array
    {
        $sarah = User::where('name', 'Sarah Jerkins')
            ->orWhere('name', 'Sarah Jenkins')
            ->orWhere('username', 'sarah.jerkins')
            ->orWhere('username', 'sarah.jenkins')
            ->first()
            ?? User::where('role', 'manager')->first();

        if ($sarah && $sarah->name !== 'Sarah Jerkins') {
            $sarah->update(['name' => 'Sarah Jerkins']);
        }

        $michael = User::where('name', 'Michael Finn')
            ->orWhere('username', 'finance.manager')
            ->orWhere('username', 'michael.finn')
            ->first()
            ?? User::where('role', 'finance_manager')->first();

        if ($michael && $michael->name !== 'Michael Finn') {
            $michael->update(['name' => 'Michael Finn']);
        }

        $johny = User::where('name', 'Johny Papa')
            ->orWhere('username', 'johny.papa')
            ->first()
            ?? User::where('role', 'department_head')->first();

        if ($johny && $johny->name !== 'Johny Papa') {
            $johny->update(['name' => 'Johny Papa']);
        }

        return [$sarah, $michael, $johny];
    }

    public function deduplicateAndFixSteps(Requisition $requisition): void
    {
        [$sarah, $michael, $johny] = self::resolveWorkflowUsers();
        $expectedMap = [
            1 => ['user' => $sarah, 'type' => 'manager_approval', 'label' => 'Manager Approval', 'desc' => 'Level 1: Sarah Jerkins (Manager)'],
            2 => ['user' => $michael, 'type' => 'finance_approval', 'label' => 'Finance Manager Approval', 'desc' => 'Level 2: Michael Finn (Finance Manager)'],
            3 => ['user' => $johny, 'type' => 'department_head_approval', 'label' => 'Head Approval', 'desc' => 'Level 3: Johny Papa (Head)'],
        ];

        $steps = $requisition->approvalSteps()->orderBy('step_order')->get();
        $grouped = $steps->groupBy('step_order');

        foreach ($grouped as $stepOrder => $stepGroup) {
            if ($stepGroup->count() > 1) {
                $keep = $stepGroup->firstWhere('status', 'approved') ?? $stepGroup->first();
                foreach ($stepGroup as $s) {
                    if ($s->id !== $keep->id) {
                        $s->delete();
                    }
                }
            }
        }

        $requisition->approvalSteps()->whereNotIn('step_order', [1, 2, 3])->delete();

        foreach ([1, 2, 3] as $order) {
            $expected = $expectedMap[$order] ?? null;
            if (!$expected || !$expected['user']) {
                continue;
            }

            $step = $requisition->approvalSteps()->where('step_order', $order)->first();
            if (!$step) {
                \App\Models\ApprovalStep::create([
                    'requisition_id' => $requisition->id,
                    'step_order' => $order,
                    'step_type' => $expected['type'],
                    'label' => $expected['label'],
                    'description' => $expected['desc'],
                    'required' => true,
                    'approver_id' => $expected['user']->id,
                    'status' => 'pending',
                ]);
            } else {
                if ((int)$step->approver_id !== (int)$expected['user']->id || $step->label !== $expected['label']) {
                    $step->update([
                        'approver_id' => $expected['user']->id,
                        'step_type' => $expected['type'],
                        'label' => $expected['label'],
                        'description' => $expected['desc'],
                    ]);
                }
            }
        }
    }

    protected function ensureNextApprovalStep(Requisition $requisition, \App\Models\ApprovalStep $actedStep): void
    {
        [$sarah, $michael, $johny] = self::resolveWorkflowUsers();
        $this->deduplicateAndFixSteps($requisition);

        if ((int)$actedStep->step_order === 1) {
            if ($michael) {
                $step2 = $requisition->approvalSteps()->where('step_order', 2)->first();
                if ($step2) {
                    $approverExists = User::where('id', $step2->approver_id)->exists();
                    $updateData = [
                        'step_type' => 'finance_approval',
                        'label' => 'Finance Manager Approval',
                        'description' => 'Level 2: Michael Finn (Finance Manager)',
                    ];
                    if (!$approverExists || empty($step2->approver_id)) {
                        $updateData['approver_id'] = $michael->id;
                    }
                    if ($step2->status !== 'approved') {
                        $updateData['status'] = 'pending';
                    }
                    $step2->update($updateData);
                } else {
                    $exists = \App\Models\ApprovalStep::where('requisition_id', $requisition->id)
                        ->where('step_order', 2)
                        ->where('approver_id', $michael->id)
                        ->first();
                    if (!$exists) {
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
            }
        } elseif ((int)$actedStep->step_order === 2) {
            if ($johny) {
                $step3 = $requisition->approvalSteps()->where('step_order', 3)->first();
                if ($step3) {
                    $approverExists = User::where('id', $step3->approver_id)->exists();
                    $updateData = [
                        'step_type' => 'department_head_approval',
                        'label' => 'Head Approval',
                        'description' => 'Level 3: Johny Papa (Head)',
                    ];
                    if (!$approverExists || empty($step3->approver_id)) {
                        $updateData['approver_id'] = $johny->id;
                    }
                    if ($step3->status !== 'approved') {
                        $updateData['status'] = 'pending';
                    }
                    $step3->update($updateData);
                } else {
                    $exists = \App\Models\ApprovalStep::where('requisition_id', $requisition->id)
                        ->where('step_order', 3)
                        ->where('approver_id', $johny->id)
                        ->first();
                    if (!$exists) {
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
