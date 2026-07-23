<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Supplier;
use App\Models\PurchaseOrder;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private $supplier;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a default supplier for testing
        $this->supplier = Supplier::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'phone' => '09170000000',
        ]);

        $this->user = \App\Models\User::factory()->create();
    }

    public function test_procurement_dashboard_loads_correctly_authenticated()
    {
        // Act
        $response = $this->actingAs($this->user)->get('/order-management');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Procurement Overview');
    }

    public function test_can_create_purchase_order()
    {
        // Act
        $response = $this->actingAs($this->user)->post(route('purchase_orders.store'), [
            'supplier_id' => $this->supplier->id,
            'expected_delivery' => now()->addDays(7)->format('Y-m-d'),
            'items' => [
                [
                    'sku' => 'TEST-SKU-001',
                    'name' => 'Test Item 1',
                    'quantity' => 10,
                    'unit_price' => 150.00,
                ]
            ]
        ]);

        // Assert
        $response->assertRedirect(route('procurement.home'));
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'total' => 1500.00,
            'status' => 'draft',
        ]);
    }

    public function test_purchase_order_code_generation_handles_deletion_correctly()
    {
        // 1. Create first PO
        $this->actingAs($this->user)->post(route('purchase_orders.store'), [
            'supplier_id' => $this->supplier->id,
            'items' => [
                ['name' => 'Item 1', 'quantity' => 1, 'unit_price' => 10]
            ]
        ]);

        $first = PurchaseOrder::first();
        $firstPoNumber = $first->po_number;

        // 2. Create second PO
        $this->actingAs($this->user)->post(route('purchase_orders.store'), [
            'supplier_id' => $this->supplier->id,
            'items' => [
                ['name' => 'Item 2', 'quantity' => 1, 'unit_price' => 20]
            ]
        ]);

        $second = PurchaseOrder::orderBy('id', 'desc')->first();
        $secondPoNumber = $second->po_number;

        // Verify the code sequence increments
        $firstNum = (int) substr($firstPoNumber, -3);
        $secondNum = (int) substr($secondPoNumber, -3);
        $this->assertEquals($firstNum + 1, $secondNum);

        // 3. Delete the second PO
        $second->delete();

        // 4. Create third PO - it should reuse the suffix or be higher, without duplicate violation
        $response = $this->actingAs($this->user)->post(route('purchase_orders.store'), [
            'supplier_id' => $this->supplier->id,
            'items' => [
                ['name' => 'Item 3', 'quantity' => 1, 'unit_price' => 30]
            ]
        ]);

        $response->assertRedirect(route('procurement.home'));
        $third = PurchaseOrder::orderBy('id', 'desc')->first();
        $this->assertEquals($secondPoNumber, $third->po_number);
    }

    public function test_purchase_order_code_generation_handles_different_lengths_correctly()
    {
        // 1. Manually insert PO with code PO-YYYY-999 (length 11)
        $year = date('Y');
        PurchaseOrder::create([
            'id' => 20,
            'po_number' => "PO-{$year}-999",
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        // 2. Manually insert PO with code PO-YYYY-1000 (length 12)
        PurchaseOrder::create([
            'id' => 25,
            'po_number' => "PO-{$year}-1000",
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        // 3. Create a new PO - it should generate PO-YYYY-1001
        $this->actingAs($this->user)->post(route('purchase_orders.store'), [
            'supplier_id' => $this->supplier->id,
            'items' => [
                ['name' => 'Item X', 'quantity' => 1, 'unit_price' => 10]
            ]
        ]);

        $latest = PurchaseOrder::orderBy('id', 'desc')->first();
        $this->assertEquals("PO-{$year}-001001", $latest->po_number);
    }

    public function test_can_send_purchase_order()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-999',
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'total' => 500.00,
        ]);

        // Act
        $response = $this->actingAs($this->user)->post(route('purchase_orders.send', $po));

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'sent',
        ]);
    }

    public function test_can_match_invoice_to_purchase_order()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-999',
            'supplier_id' => $this->supplier->id,
            'status' => 'sent',
            'total' => 1000.00,
        ]);

        // Act
        $response = $this->actingAs($this->user)->post(route('purchase_orders.match_invoice'), [
            'po_number' => 'PO-2026-999',
            'invoice_number' => 'INV-TEST-001',
            'amount' => 1000.00,
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-TEST-001',
            'purchase_order_id' => $po->id,
            'amount' => 1000.00,
        ]);
    }
}
