<?php

namespace Tests\Feature;

use App\Models\DeliveryReceipt;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptInvoiceMatchingTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $supplier;
    private $po;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->supplier = Supplier::create([
            'slug' => 'savanna-grain-co',
            'supplier_name' => 'Savanna Grain Co.',
            'name' => 'Savanna Grain Co.',
            'supplier_id' => 'SUP-001',
            'status' => 'Active',
            'location' => 'Harare Central Depot',
        ]);

        $this->po = PurchaseOrder::create([
            'po_number' => 'PO-2026-99001',
            'supplier_id' => $this->supplier->id,
            'status' => 'approved',
            'total' => 204500.00,
            'issued_at' => now(),
        ]);
    }

    public function test_matching_dashboard_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('matching.index'));

        $response->assertStatus(200);
        $response->assertSee('Order Management');
    }

    public function test_can_record_goods_receipt()
    {
        $response = $this->actingAs($this->user)->postJson(route('matching.store_grn'), [
            'po_number' => 'PO-2026-99001',
            'grn_number' => 'GRN-2026-TEST01',
            'received_at' => now()->format('Y-m-d H:i:s'),
            'receiving_location' => 'Harare Central Depot',
            'invoice_number' => 'INV-SG-8821',
            'invoice_amount' => 204500.00,
            'lines' => [
                ['name' => 'White Maize', 'qty_received' => 100, 'qty_accepted' => 100, 'unit_price' => 2045.00]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('status', 'Matched');

        $this->assertDatabaseHas('delivery_receipts', [
            'dr_number' => 'GRN-2026-TEST01',
            'purchase_order_id' => $this->po->id,
        ]);
    }

    public function test_prevents_duplicate_goods_receipt_recording()
    {
        DeliveryReceipt::create([
            'dr_number' => 'GRN-2026-DUP01',
            'purchase_order_id' => $this->po->id,
            'received_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('matching.store_grn'), [
            'po_number' => 'PO-2026-99001',
            'grn_number' => 'GRN-2026-DUP01',
            'received_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertSee('Duplicate Receipt Record');
    }

    public function test_run_3way_matching_detects_missing_documents_and_mismatches()
    {
        // PO exists without GRN or Invoice
        $response = $this->actingAs($this->user)->postJson(route('matching.run_matching'), [
            'po_number' => 'PO-2026-99001',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('payment_approvable', false);
        $response->assertSee('Matching cannot be completed until all required documents are available.');
    }

    public function test_cannot_approve_payment_when_mismatches_or_missing_documents_exist()
    {
        // Create un-matched DR with missing invoice
        $dr = DeliveryReceipt::create([
            'dr_number' => 'GRN-UNMATCHED',
            'purchase_order_id' => $this->po->id,
            'received_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('matching.approve', $dr->id));

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertSee('Payment cannot be approved');
    }

    public function test_can_approve_payment_when_transaction_is_fully_matched()
    {
        $dr = DeliveryReceipt::create([
            'dr_number' => 'GRN-MATCHED-OK',
            'purchase_order_id' => $this->po->id,
            'received_at' => now(),
            'items' => ['accepted_total' => 204500.00],
        ]);

        Invoice::create([
            'invoice_number' => 'INV-MATCHED-OK',
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $this->po->id,
            'amount' => 204500.00,
            'received_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('matching.approve', $dr->id));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertSee('Payment validated and approved successfully!');
    }

    public function test_get_matching_details_endpoint_returns_json()
    {
        $response = $this->actingAs($this->user)->getJson(route('matching.details', 'PO-2026-99001'));

        $response->assertStatus(200);
        $response->assertJsonPath('po_number', 'PO-2026-99001');
        $response->assertJsonStructure([
            'po_number',
            'supplier',
            'po_amount',
            'status',
            'payment_approvable',
        ]);
    }
}
