<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStep;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequisitionController extends Controller
{
    public function create()
    {
        return view('requisitions.create', [
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'title' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'needed_by' => ['nullable', 'date'],
            'purpose' => ['nullable', 'string'],
            'urgency' => ['nullable', 'in:Low,Medium,High'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.justification' => ['nullable', 'string'],
            'action' => ['required', 'in:draft,continue'],
        ]);

        $requisition = DB::transaction(function () use ($data, $request) {
            $subtotal = collect($data['items'])->sum(fn ($i) => $i['qty'] * $i['unit_price']);
            $taxRate = 0; // adjustable
            $taxAmount = round($subtotal * ($taxRate / 100), 2);

            $requisition = Requisition::create([
                'code' => $this->generateCode(),
                'title' => $data['title'],
                'department' => $data['department'] ?? Auth::user()->department,
                'requestor_id' => Auth::id(),
                'supplier_id' => $data['supplier_id'] ?? null,
                'needed_by' => $data['needed_by'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'urgency' => $data['urgency'] ?? 'Medium',
                'status' => 'draft',
            ]);

            foreach ($data['items'] as $item) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'service_id' => $item['service_id'] ?? null,
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                    'unit' => $item['unit'] ?? 'service',
                    'unit_price' => $item['unit_price'],
                    'total' => $item['qty'] * $item['unit_price'],
                    'justification' => $item['justification'] ?? null,
                ]);
            }

            return $requisition;
        });

        if ($data['action'] === 'draft') {
            return redirect()->route('requisitions.tracking')->with('status', 'Requisition saved as draft.');
        }

        self::createDefaultApprovalSteps($requisition);

        return redirect()->route('requisitions.receipt', $requisition)->with('status', 'Purchase Requisition submitted successfully! Receipt generated.');
    }

    public function showReceipt(Requisition $requisition)
    {
        $requisition->load(['requestor', 'recommendedSupplier', 'items', 'approvalSteps.approver']);
        return view('requisitions.receipt', compact('requisition'));
    }

    public function downloadReceiptPdf(Requisition $requisition)
    {
        $requisition->load(['requestor', 'recommendedSupplier', 'items', 'approvalSteps.approver']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('requisitions.receipt_pdf', compact('requisition'));
        return $pdf->download('PR_Receipt_' . $requisition->code . '.pdf');
    }

    public function showRoute(Requisition $requisition)
    {
        $this->authorizeOwner($requisition);

        if ($requisition->approvalSteps()->count() === 0) {
            self::createDefaultApprovalSteps($requisition);
        }

        return redirect()->route('requisitions.receipt', $requisition)->with('status', 'Requisition submitted for 3-Level approval.');
    }

    public function storeRoute(Request $request, Requisition $requisition)
    {
        $this->authorizeOwner($requisition);

        self::createDefaultApprovalSteps($requisition);

        return redirect()->route('requisitions.receipt', $requisition)->with('status', 'Requisition submitted for 3-Level approval.');
    }

    public static function createDefaultApprovalSteps(Requisition $requisition): void
    {
        $sarah = User::where('username', 'sarah.jenkins')->first() ?? User::where('role', 'manager')->first() ?? Auth::user();
        $michael = User::where('username', 'finance.manager')->first() ?? User::where('role', 'finance_manager')->first() ?? $sarah;
        $johny = User::where('username', 'johny.papa')->first() ?? User::where('role', 'department_head')->first() ?? $sarah;

        $steps = [
            [
                'step_order' => 1,
                'step_type' => 'manager_approval',
                'label' => 'Project Manager Approval',
                'description' => 'Level 1: Sarah Jenkins (Project Manager)',
                'required' => true,
                'approver_id' => $sarah->id,
                'status' => 'pending',
            ],
            [
                'step_order' => 2,
                'step_type' => 'finance_approval',
                'label' => 'Finance Manager Approval',
                'description' => 'Level 2: Michael Finn (Finance Manager)',
                'required' => true,
                'approver_id' => $michael->id,
                'status' => 'pending',
            ],
            [
                'step_order' => 3,
                'step_type' => 'department_head_approval',
                'label' => 'Head Approval',
                'description' => 'Level 3: Johny Papa (Head)',
                'required' => true,
                'approver_id' => $johny->id,
                'status' => 'pending',
            ],
        ];

        DB::transaction(function () use ($requisition, $steps) {
            foreach ($steps as $s) {
                $existing = $requisition->approvalSteps()->where('step_order', $s['step_order'])->first();
                if (!$existing) {
                    ApprovalStep::create(array_merge($s, ['requisition_id' => $requisition->id]));
                }
            }

            if ($requisition->status === 'draft' || empty($requisition->status)) {
                $requisition->update([
                    'approval_type' => 'sequential',
                    'status' => 'pending_approval',
                    'submitted_at' => $requisition->submitted_at ?? now(),
                ]);
            }
        });
    }

    public function tracking()
    {
        $requisitions = Requisition::with(['requestor', 'approvalSteps'])
            ->where('requestor_id', Auth::id())
            ->latest()
            ->get();

        return view('requisitions.tracking', compact('requisitions'));
    }

    public function searchServices(Request $request)
    {
        $query = trim((string) $request->get('q'));

        $services = Service::when($query, fn ($q) => $q->where('name', 'like', "%{$query}%"))
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($services);
    }

    private function authorizeOwner(Requisition $requisition): void
    {
        abort_unless($requisition->requestor_id === Auth::id(), 403);
    }

    private function generateCode(): string
    {
        $year = now()->format('Y');
        $latest = Requisition::where('code', 'like', "PR-{$year}-%")
            ->orderBy('code', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('-', $latest->code);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('PR-%s-%05d', $year, $nextNumber);
    }
}
