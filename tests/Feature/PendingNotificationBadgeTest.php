<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingNotificationBadgeTest extends TestCase
{
    use RefreshDatabase;

    private $sarah;
    private $michael;
    private $johny;
    private $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sarah = User::factory()->create([
            'name' => 'Sarah Jerkins',
            'email' => 'sarah.jerkins@ambatugrow.test',
            'role' => 'manager',
        ]);

        $this->michael = User::factory()->create([
            'name' => 'Michael Finn',
            'email' => 'michael.finn@ambatugrow.test',
            'role' => 'finance_manager',
        ]);

        $this->johny = User::factory()->create([
            'name' => 'Johny Papa',
            'email' => 'johny.papa@ambatugrow.test',
            'role' => 'department_head',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'status' => 'active',
            'contact_person' => 'Jane',
            'email' => 'supplier@test.com',
            'phone' => '12345',
            'address' => 'Test Addr',
            'city' => 'Cavite',
        ]);
    }

    public function test_pending_notification_badge_updates_dynamically_across_approvers()
    {
        // 1. Initial state: Sarah, Michael, Johny all have 0 pending approvals
        $this->actingAs($this->sarah)
            ->get(route('approvals.pending_count'))
            ->assertStatus(200)
            ->assertJson(['count' => 0]);

        $this->actingAs($this->michael)
            ->get(route('approvals.pending_count'))
            ->assertStatus(200)
            ->assertJson(['count' => 0]);

        // 2. Create Requisition assigned to Sarah (Step 1)
        $req = Requisition::create([
            'code' => 'PR-2026-BADGE01',
            'title' => 'Badge Test Requisition',
            'department' => 'Operations',
            'requestor_id' => $this->sarah->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'pending_approval',
            'total' => 12000.00,
        ]);

        ApprovalStep::create([
            'requisition_id' => $req->id,
            'step_order' => 1,
            'step_type' => 'manager_approval',
            'label' => 'Manager Approval',
            'required' => true,
            'approver_id' => $this->sarah->id,
            'status' => 'pending',
        ]);

        ApprovalStep::create([
            'requisition_id' => $req->id,
            'step_order' => 2,
            'step_type' => 'finance_approval',
            'label' => 'Finance Approval',
            'required' => true,
            'approver_id' => $this->michael->id,
            'status' => 'pending',
        ]);

        ApprovalStep::create([
            'requisition_id' => $req->id,
            'step_order' => 3,
            'step_type' => 'department_head_approval',
            'label' => 'Head Approval',
            'required' => true,
            'approver_id' => $this->johny->id,
            'status' => 'pending',
        ]);

        // Sarah now has 1 pending approval; Michael & Johny still 0
        $this->actingAs($this->sarah)
            ->get(route('approvals.pending_count'))
            ->assertJson(['count' => 1]);

        $this->actingAs($this->michael)
            ->get(route('approvals.pending_count'))
            ->assertJson(['count' => 0]);

        // 3. Sarah approves (Step 1 -> Step 2)
        $this->actingAs($this->sarah)
            ->post(route('approvals.act', $req), [
                'decision' => 'approve',
                'comment' => 'Approved step 1',
            ])
            ->assertRedirect();

        // Sarah badge -> 0, Michael badge -> 1
        $this->actingAs($this->sarah)
            ->get(route('approvals.pending_count'))
            ->assertJson(['count' => 0]);

        $this->actingAs($this->michael)
            ->get(route('approvals.pending_count'))
            ->assertJson(['count' => 1]);

        $this->actingAs($this->johny)
            ->get(route('approvals.pending_count'))
            ->assertJson(['count' => 0]);

        // 4. Michael approves (Step 2 -> Step 3)
        $this->actingAs($this->michael)
            ->post(route('approvals.act', $req), [
                'decision' => 'approve',
                'comment' => 'Approved step 2',
            ])
            ->assertRedirect();

        // Michael badge -> 0, Johny badge -> 1
        $this->actingAs($this->michael)
            ->get(route('approvals.pending_count'))
            ->assertJson(['count' => 0]);

        $this->actingAs($this->johny)
            ->get(route('approvals.pending_count'))
            ->assertJson(['count' => 1]);

        // 5. Johny approves (Step 3 -> Completed)
        $this->actingAs($this->johny)
            ->post(route('approvals.act', $req), [
                'decision' => 'approve',
                'comment' => 'Approved step 3',
            ])
            ->assertRedirect();

        // Johny badge -> 0
        $this->actingAs($this->johny)
            ->get(route('approvals.pending_count'))
            ->assertJson(['count' => 0]);
    }
}
