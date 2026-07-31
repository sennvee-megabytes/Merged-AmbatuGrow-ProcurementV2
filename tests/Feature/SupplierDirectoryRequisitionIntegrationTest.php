<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierDirectoryRequisitionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_requisition_with_supplier_connects_to_supplier_directory(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $supplier = Supplier::create([
            'supplier_name' => 'BioGrow Organics',
            'name' => 'BioGrow Organics',
            'slug' => 'biogrow-organics',
            'supplier_code' => 'AGR-00901',
            'status' => 'Active',
        ]);

        $reqData = [
            'supplier_id' => $supplier->id,
            'title' => 'Bulk Soil Fertilizer',
            'department' => 'Farm Operations',
            'needed_by' => now()->addDays(5)->format('Y-m-d'),
            'purpose' => 'Soil treatment',
            'urgency' => 'High',
            'action' => 'continue',
            'items' => [
                [
                    'sku' => 'FERT-001',
                    'name' => 'Organic Compost',
                    'qty' => 10,
                    'unit' => 'Sack',
                    'unit_price' => 450.00,
                    'justification' => 'Field A requirement',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('requisitions.store'), $reqData);

        $response->assertRedirect();

        $requisition = Requisition::where('title', 'Bulk Soil Fertilizer')->first();
        $this->assertNotNull($requisition);
        $this->assertEquals($supplier->id, $requisition->supplier_id);

        // Approve requisition steps
        $steps = ApprovalStep::where('requisition_id', $requisition->id)->get();
        foreach ($steps as $step) {
            $this->actingAs($user)->post(route('approvals.act', $requisition), [
                'decision' => 'approve',
                'comment' => 'Approved in test',
            ]);
        }

        $requisition->refresh();
        $this->assertEquals('approved', $requisition->status);

        // Verify PurchaseOrder created and linked to supplier
        $po = \App\Models\PurchaseOrder::where('requisition_id', $requisition->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals($supplier->id, $po->supplier_id);

        // Verify purchase history accessor on supplier
        $supplier->refresh();
        $history = $supplier->purchase_history;
        $this->assertNotEmpty($history);
        $this->assertEquals($po->po_number, $history[0]['po_number']);

        // Verify purchase history view route
        $historyResponse = $this->actingAs($user)->get(route('suppliers.purchase-history', $supplier->slug));
        $historyResponse->assertOk();
        $historyResponse->assertSee($po->po_number);
    }
}
