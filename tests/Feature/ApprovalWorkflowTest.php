<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private $sarah;
    private $michael;
    private $johny;
    private $requestor;
    private $requisition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestor = User::factory()->create([
            'name' => 'Emily Johnson',
            'username' => 'emily.johnson',
            'role' => 'manager',
        ]);

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

        $this->requisition = Requisition::create([
            'code' => 'PR-2026-TEST01',
            'title' => 'Test Office Supplies',
            'department' => 'Marketing',
            'requestor_id' => $this->requestor->id,
            'status' => 'pending_approval',
            'approval_type' => 'sequential',
            'total' => 1500.00,
            'submitted_at' => now(),
        ]);

        RequisitionItem::create([
            'requisition_id' => $this->requisition->id,
            'name' => 'Test Item',
            'qty' => 1,
            'unit' => 'Unit',
            'unit_price' => 1500.00,
            'total' => 1500.00,
        ]);

        ApprovalStep::create([
            'requisition_id' => $this->requisition->id,
            'step_order' => 1,
            'step_type' => 'manager_approval',
            'label' => 'Project Manager Approval',
            'description' => 'Level 1: Sarah Jenkins (Project Manager)',
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

    public function test_sarah_approves_and_assigns_step_2_to_michael_finn_without_duplicates()
    {
        // Sarah approves Step 1
        $response = $this->actingAs($this->sarah)
            ->post(route('approvals.act', $this->requisition), [
                'decision' => 'approve',
                'comment' => 'Approved by Sarah',
            ]);

        $response->assertRedirect(route('approvals.index'));

        // Check Step 1 is approved
        $this->assertDatabaseHas('approval_steps', [
            'requisition_id' => $this->requisition->id,
            'step_order' => 1,
            'status' => 'approved',
        ]);

        // Check Step 2 is pending and assigned ONLY to Michael Finn
        $step2s = ApprovalStep::where('requisition_id', $this->requisition->id)
            ->where('step_order', 2)
            ->get();

        $this->assertCount(1, $step2s);
        $this->assertEquals($this->michael->id, $step2s->first()->approver_id);
        $this->assertEquals('pending', $step2s->first()->status);

        // Verify status label
        $this->requisition->refresh();
        $this->assertEquals('Pending Finance Approval', $this->requisition->statusLabel());
    }

    public function test_michael_finn_sees_requisition_in_queue_once_and_can_approve()
    {
        // Step 1 approved by Sarah
        ApprovalStep::where('requisition_id', $this->requisition->id)
            ->where('step_order', 1)
            ->update(['status' => 'approved', 'acted_at' => now()]);

        // Michael Finn accesses queue
        $response = $this->actingAs($this->michael)
            ->get(route('approvals.index', ['requisition' => $this->requisition->id]));

        $response->assertStatus(200);

        $pendingForMe = $response->viewData('pendingForMe');
        $this->assertCount(1, $pendingForMe);
        $this->assertEquals($this->requisition->id, $pendingForMe->first()->id);

        // Michael Finn approves Step 2
        $actionResponse = $this->actingAs($this->michael)
            ->post(route('approvals.act', $this->requisition), [
                'decision' => 'approve',
                'comment' => 'Approved by Michael',
            ]);

        $actionResponse->assertRedirect(route('approvals.index'));

        // Verify Step 2 is approved and Step 3 is assigned to Johny Papa
        $step2 = ApprovalStep::where('requisition_id', $this->requisition->id)
            ->where('step_order', 2)
            ->first();
        $this->assertEquals('approved', $step2->status);

        $step3s = ApprovalStep::where('requisition_id', $this->requisition->id)
            ->where('step_order', 3)
            ->get();
        $this->assertCount(1, $step3s);
        $this->assertEquals($this->johny->id, $step3s->first()->approver_id);
        $this->assertEquals('pending', $step3s->first()->status);

        // Status should be Pending Head Approval
        $this->requisition->refresh();
        $this->assertEquals('Pending Head Approval', $this->requisition->statusLabel());
    }

    public function test_no_duplicate_steps_are_created_if_already_exists()
    {
        // Act as Sarah and approve
        $this->actingAs($this->sarah)
            ->post(route('approvals.act', $this->requisition), [
                'decision' => 'approve',
            ]);

        $totalSteps = ApprovalStep::where('requisition_id', $this->requisition->id)->count();
        $this->assertEquals(3, $totalSteps);
    }
}
