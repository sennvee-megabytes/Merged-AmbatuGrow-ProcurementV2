<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalApprovalTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_user_roles_can_access_purchase_and_requisition(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'department' => 'Marketing',
        ]);

        $response = $this->actingAs($admin)->get(route('approvals.index'));

        $response->assertOk();
    }

    public function test_history_tracking_inspects_full_approval_audit_trail(): void
    {
        $head = User::factory()->create([
            'role' => 'department_head',
            'department' => 'Finance',
        ]);

        $approver = User::factory()->create([
            'name' => 'Sarah Jerkins',
            'role' => 'manager',
        ]);

        $requisition = Requisition::create([
            'code' => 'PR-2026-0099',
            'title' => 'Office Supplies',
            'requestor_id' => $head->id,
            'department' => 'Finance',
            'status' => 'approved',
            'total' => 5000.00,
            'submitted_at' => now(),
        ]);

        ApprovalStep::create([
            'requisition_id' => $requisition->id,
            'step_order' => 1,
            'step_type' => 'manager_approval',
            'label' => 'Manager Approval',
            'approver_id' => $approver->id,
            'status' => 'approved',
            'comment' => 'Approved budget allocation.',
            'acted_at' => now(),
        ]);

        $response = $this->actingAs($head)->get(route('approvals.index', ['requisition' => $requisition->id]));

        $response->assertOk();
        $response->assertViewHas('history');

        $selected = $response->viewData('selected');
        $this->assertNotNull($selected);
        $this->assertEquals($requisition->id, $selected->id);
        $this->assertEquals('Sarah Jerkins', $selected->approvalSteps->first()->approver->name);
        $this->assertEquals('Approved budget allocation.', $selected->approvalSteps->first()->comment);
    }
}
