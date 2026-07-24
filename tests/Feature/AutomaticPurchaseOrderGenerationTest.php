<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomaticPurchaseOrderGenerationTest extends TestCase
{
    use RefreshDatabase;

    private $requestor;
    private $manager;
    private $finance;
    private $head;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'name' => 'Project Manager User',
        ]);

        $this->finance = User::factory()->create([
            'role' => 'finance_manager',
            'name' => 'Finance Manager User',
        ]);

        $this->head = User::factory()->create([
            'role' => 'department_head',
            'name' => 'Head User',
        ]);

        $this->requestor = User::factory()->create([
            'role' => 'admin',
            'department' => 'Operations',
        ]);
    }

    public function test_requisition_submission_automatically_creates_fixed_3_level_approval_steps()
    {
        $response = $this->actingAs($this->requestor)->post(route('requisitions.store'), [
            'title' => 'Agri Supplies Purchase',
            'needed_by' => now()->addDays(5)->format('Y-m-d'),
            'purpose' => 'Farm maintenance',
            'urgency' => 'High',
            'action' => 'continue',
            'items' => [
                [
                    'name' => 'Organic Fertilizer Bales',
                    'qty' => 10,
                    'unit' => 'bag',
                    'unit_price' => 1200,
                ]
            ]
        ]);

        $requisition = Requisition::with('approvalSteps')->first();
        $this->assertNotNull($requisition);
        $response->assertRedirect(route('requisitions.receipt', $requisition));
        $this->assertEquals('pending_approval', $requisition->status);

        $steps = $requisition->approvalSteps;
        $this->assertCount(3, $steps);

        $this->assertEquals(1, $steps[0]->step_order);
        $this->assertEquals('Project Manager Approval', $steps[0]->label);

        $this->assertEquals(2, $steps[1]->step_order);
        $this->assertEquals('Finance Manager Approval', $steps[1]->label);

        $this->assertEquals(3, $steps[2]->step_order);
        $this->assertEquals('Head Approval', $steps[2]->label);
    }

    public function test_po_is_automatically_generated_when_all_three_approvals_are_completed()
    {
        // 1. Submit Requisition
        $this->actingAs($this->requestor)->post(route('requisitions.store'), [
            'title' => 'Equipment Purchase',
            'action' => 'continue',
            'items' => [
                ['name' => 'Water Pump 5HP', 'qty' => 1, 'unit_price' => 25000]
            ]
        ]);

        $requisition = Requisition::first();

        // 2. Step 1: Project Manager Approves
        $step1 = $requisition->approvalSteps()->where('step_order', 1)->first();
        $this->actingAs($this->manager)->post(route('approvals.act', $requisition), [
            'decision' => 'approve',
            'comment' => 'Project Manager approved.',
        ]);

        $requisition->refresh();
        $this->assertEquals('pending_approval', $requisition->status);
        $this->assertDatabaseMissing('purchase_orders', ['requisition_id' => $requisition->id]);

        // 3. Step 2: Finance Manager Approves
        $step2 = $requisition->approvalSteps()->where('step_order', 2)->first();
        $this->actingAs($this->finance)->post(route('approvals.act', $requisition), [
            'decision' => 'approve',
            'comment' => 'Finance approved budget.',
        ]);

        $requisition->refresh();
        $this->assertEquals('pending_approval', $requisition->status);
        $this->assertDatabaseMissing('purchase_orders', ['requisition_id' => $requisition->id]);

        // 4. Step 3: Head Approves (Final 3rd Approval)
        $step3 = $requisition->approvalSteps()->where('step_order', 3)->first();
        $this->actingAs($this->head)->post(route('approvals.act', $requisition), [
            'decision' => 'approve',
            'comment' => 'Head final authorization.',
        ]);

        $requisition->refresh();
        $this->assertEquals('approved', $requisition->status);

        // Verify Purchase Order was automatically generated!
        $po = PurchaseOrder::where('requisition_id', $requisition->id)->first();
        $this->assertNotNull($po);
        $this->assertStringContainsString('PO-' . date('Y') . '-', $po->po_number);
        $this->assertEquals(25000, $po->total);

        // Test PDF route for generated PO
        $pdfResponse = $this->actingAs($this->requestor)->get(route('purchase_orders.pdf', $po));
        $pdfResponse->assertOk();
    }

    public function test_approval_worker_list_contains_only_three_designated_approvers()
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $response = $this->actingAs($this->requestor)->get(route('approvals.index'));
        $response->assertOk();

        $delegates = $response->viewData('delegates');
        $names = $delegates->pluck('name')->toArray();

        $this->assertContains('Sarah Jenkins', $names);
        $this->assertContains('Michael Finn', $names);
        $this->assertContains('Johny Papa', $names);
    }
}
