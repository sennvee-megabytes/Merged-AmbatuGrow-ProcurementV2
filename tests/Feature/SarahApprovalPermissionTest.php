<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SarahApprovalPermissionTest extends TestCase
{
    use RefreshDatabase;

    private $sarah;
    private $michael;
    private $johny;
    private $requisition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sarah = User::factory()->create([
            'name' => 'Sarah Jerkins',
            'username' => 'sarah.jerkins',
            'role' => 'manager',
        ]);

        $this->michael = User::factory()->create([
            'name' => 'Michael Finn',
            'username' => 'finance.manager',
            'role' => 'finance_manager',
        ]);

        $this->johny = User::factory()->create([
            'name' => 'Johny Papa',
            'username' => 'johny.papa',
            'role' => 'department_head',
        ]);

        $requestor = User::factory()->create(['role' => 'manager']);

        $this->requisition = Requisition::create([
            'code' => 'PR-2026-00013',
            'title' => 'Office Supplies for Q3',
            'department' => 'Marketing',
            'requestor_id' => $requestor->id,
            'status' => 'pending_approval',
            'approval_type' => 'sequential',
            'total' => 2500.00,
            'submitted_at' => now(),
        ]);

        RequisitionItem::create([
            'requisition_id' => $this->requisition->id,
            'name' => 'Printer Paper',
            'qty' => 10,
            'unit' => 'Box',
            'unit_price' => 250.00,
            'total' => 2500.00,
        ]);

        ApprovalStep::create([
            'requisition_id' => $this->requisition->id,
            'step_order' => 1,
            'step_type' => 'manager_approval',
            'label' => 'Manager Approval',
            'description' => 'Level 1: Sarah Jerkins (Manager)',
            'required' => true,
            'approver_id' => $this->sarah->id,
            'status' => 'pending',
        ]);

        ApprovalStep::create([
            'requisition_id' => $this->requisition->id,
            'step_order' => 2,
            'step_type' => 'finance_approval',
            'label' => 'Finance Manager Approval',
            'description' => 'Level 2: Michael Finn (Finance Manager)',
            'required' => true,
            'approver_id' => $this->michael->id,
            'status' => 'pending',
        ]);

        ApprovalStep::create([
            'requisition_id' => $this->requisition->id,
            'step_order' => 3,
            'step_type' => 'department_head_approval',
            'label' => 'Head Approval',
            'description' => 'Level 3: Johny Papa (Head)',
            'required' => true,
            'approver_id' => $this->johny->id,
            'status' => 'pending',
        ]);
    }

    public function test_sarah_jerkins_can_view_and_approve_requisition()
    {
        $response = $this->actingAs($this->sarah)
            ->get(route('approvals.index', ['requisition' => $this->requisition->id]));

        $response->assertStatus(200);
        $response->assertSee('Approve');
        $response->assertSee('Reject');
        $response->assertDontSee("You cannot act on this requisition right now");

        // Submit approval
        $actResponse = $this->actingAs($this->sarah)
            ->post(route('approvals.act', $this->requisition), [
                'decision' => 'approve',
                'comment' => 'Approved by Sarah',
            ]);

        $actResponse->assertRedirect(route('approvals.index'));

        // Step 1 approved
        $this->assertDatabaseHas('approval_steps', [
            'requisition_id' => $this->requisition->id,
            'step_order' => 1,
            'status' => 'approved',
        ]);

        // Step 2 is now pending for Michael Finn
        $step2 = ApprovalStep::where('requisition_id', $this->requisition->id)->where('step_order', 2)->first();
        $this->assertEquals('pending', $step2->status);
        $this->assertEquals($this->michael->id, $step2->approver_id);
    }

    public function test_sarah_cannot_act_when_it_is_not_her_turn()
    {
        // Advance to Step 2
        ApprovalStep::where('requisition_id', $this->requisition->id)->where('step_order', 1)->update(['status' => 'approved']);

        $response = $this->actingAs($this->sarah)
            ->get(route('approvals.index', ['requisition' => $this->requisition->id]));

        $response->assertStatus(200);
        $response->assertSee("You cannot act on this requisition right now");
    }
}
