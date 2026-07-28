<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopSuppliersWidgetTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_top_suppliers_widget_displays_empty_message_when_no_orders_exist()
    {
        $response = $this->actingAs($this->user)
            ->get(route('suppliers.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('No supplier order data available.');
    }

    public function test_top_suppliers_widget_ranks_suppliers_dynamically_with_proportional_progress()
    {
        // Create 3 suppliers
        $supplierA = Supplier::create([
            'name' => 'Flora Supplies Co.',
            'status' => 'active',
            'contact_person' => 'Flora',
            'email' => 'flora@test.com',
            'phone' => '11111',
            'address' => 'Flora St',
            'city' => 'Manila',
        ]);

        $supplierB = Supplier::create([
            'name' => 'Green Solutions',
            'status' => 'active',
            'contact_person' => 'Green',
            'email' => 'green@test.com',
            'phone' => '22222',
            'address' => 'Green St',
            'city' => 'Cavite',
        ]);

        $supplierC = Supplier::create([
            'name' => 'Sun Supplies Co.',
            'status' => 'active',
            'contact_person' => 'Sun',
            'email' => 'sun@test.com',
            'phone' => '33333',
            'address' => 'Sun St',
            'city' => 'Cebu',
        ]);

        // Supplier A gets 12 POs
        for ($i = 0; $i < 12; $i++) {
            PurchaseOrder::create([
                'po_number' => 'PO-SUPA-' . $i,
                'supplier_id' => $supplierA->id,
                'status' => 'approved',
                'total' => 1000.00,
            ]);
        }

        // Supplier B gets 6 POs
        for ($i = 0; $i < 6; $i++) {
            PurchaseOrder::create([
                'po_number' => 'PO-SUPB-' . $i,
                'supplier_id' => $supplierB->id,
                'status' => 'approved',
                'total' => 1000.00,
            ]);
        }

        // Supplier C gets 3 POs
        for ($i = 0; $i < 3; $i++) {
            PurchaseOrder::create([
                'po_number' => 'PO-SUPC-' . $i,
                'supplier_id' => $supplierC->id,
                'status' => 'approved',
                'total' => 1000.00,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('suppliers.dashboard'));

        $response->assertStatus(200);

        $topSuppliers = $response->viewData('topSuppliers');
        $this->assertCount(3, $topSuppliers);

        // Rank 1: Flora Supplies Co (12 orders -> 100%)
        $this->assertEquals('Flora Supplies Co.', $topSuppliers[0]['name']);
        $this->assertEquals(12, $topSuppliers[0]['orders_count']);
        $this->assertEquals(100.0, $topSuppliers[0]['progress_percentage']);

        // Rank 2: Green Solutions (6 orders -> 50%)
        $this->assertEquals('Green Solutions', $topSuppliers[1]['name']);
        $this->assertEquals(6, $topSuppliers[1]['orders_count']);
        $this->assertEquals(50.0, $topSuppliers[1]['progress_percentage']);

        // Rank 3: Sun Supplies Co. (3 orders -> 25%)
        $this->assertEquals('Sun Supplies Co.', $topSuppliers[2]['name']);
        $this->assertEquals(3, $topSuppliers[2]['orders_count']);
        $this->assertEquals(25.0, $topSuppliers[2]['progress_percentage']);

        // Verify HTML contents
        $response->assertSee('Flora Supplies Co.');
        $response->assertSee('Green Solutions');
        $response->assertSee('Sun Supplies Co.');
        $response->assertSee('12 orders');
        $response->assertSee('6 orders');
        $response->assertSee('3 orders');
        $response->assertSee('style="width: 100%"', false);
        $response->assertSee('style="width: 50%"', false);
        $response->assertSee('style="width: 25%"', false);
    }
}
