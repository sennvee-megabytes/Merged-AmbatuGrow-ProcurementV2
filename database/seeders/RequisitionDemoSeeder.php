<?php

namespace Database\Seeders;

use App\Models\ApprovalStep;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class RequisitionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $requestor = User::where('username', 'emily.johnson')->first();
        $sarah = User::where('username', 'sarah.jenkins')->first() ?? User::where('role', 'manager')->first();
        $michael = User::where('username', 'finance.manager')->first() ?? User::where('role', 'finance_manager')->first();
        $johny = User::where('username', 'johny.papa')->first() ?? User::where('role', 'department_head')->first();

        if (! $requestor || Requisition::where('code', 'PR-2026-00125')->exists()) {
            return;
        }

        $requisition = Requisition::create([
            'code' => 'PR-2026-00125',
            'title' => 'Q3 Marketing Software Renewal',
            'department' => 'Marketing',
            'requestor_id' => $requestor->id,
            'needed_by' => now()->addWeeks(2),
            'purpose' => 'Renewal of premium SaaS licenses and onboarding for the marketing team.',
            'subtotal' => 12500,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => 12500,
            'approval_type' => 'sequential',
            'status' => 'pending_approval',
            'urgency' => 'High',
            'submitted_at' => now(),
        ]);

        RequisitionItem::create([
            'requisition_id' => $requisition->id,
            'name' => 'Premium SaaS License',
            'qty' => 10,
            'unit' => 'service',
            'unit_price' => 1200,
            'total' => 12000,
        ]);

        RequisitionItem::create([
            'requisition_id' => $requisition->id,
            'name' => 'Onboarding Licenses',
            'qty' => 5,
            'unit' => 'service',
            'unit_price' => 100,
            'total' => 500,
        ]);

        ApprovalStep::create([
            'requisition_id' => $requisition->id,
            'step_order' => 1,
            'step_type' => 'manager_approval',
            'label' => 'Project Manager Approval',
            'description' => 'Level 1: Sarah Jenkins (Project Manager)',
            'required' => true,
            'approver_id' => $sarah?->id,
            'status' => 'pending',
        ]);

        ApprovalStep::create([
            'requisition_id' => $requisition->id,
            'step_order' => 2,
            'step_type' => 'finance_approval',
            'label' => 'Finance Manager Approval',
            'description' => 'Level 2: Michael Finn (Finance Manager)',
            'required' => true,
            'approver_id' => $michael?->id,
            'status' => 'pending',
        ]);

        ApprovalStep::create([
            'requisition_id' => $requisition->id,
            'step_order' => 3,
            'step_type' => 'department_head_approval',
            'label' => 'Head Approval',
            'description' => 'Level 3: Johny Papa (Head)',
            'required' => true,
            'approver_id' => $johny?->id,
            'status' => 'pending',
        ]);
    }
}
