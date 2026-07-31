<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierProductSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_code_auto_increments_sequence_number(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Kilo Agri',
            'name' => 'Kilo Agri',
            'slug' => 'kilo-agri',
            'supplier_code' => 'AGR-00100',
            'status' => 'Active',
        ]);

        // Add first product with sequence 143
        $this->actingAs($user)->post(route('suppliers.products.store', $supplier->slug), [
            'name' => 'Special Rice',
            'category_name' => 'Rice',
            'uom_code' => 'Sack',
            'unit_price' => 2000.00,
            'min_order' => 10,
            'lead_time_days' => 3,
            'code' => 'AGR-00143-RIC',
        ]);

        $this->assertDatabaseHas('product_suppliers', [
            'supplier_id' => $supplier->id,
            'supplier_sku' => 'AGR-00143-RIC',
        ]);

        // Add second product without specifying code -> should auto-increment to 144
        $this->actingAs($user)->post(route('suppliers.products.store', $supplier->slug), [
            'name' => 'Fresh Apple',
            'category_name' => 'Fruits',
            'uom_code' => 'Box',
            'unit_price' => 1500.00,
            'min_order' => 5,
            'lead_time_days' => 2,
            'code' => '', // empty code -> auto-increment
        ]);

        $this->assertDatabaseHas('product_suppliers', [
            'supplier_id' => $supplier->id,
            'supplier_sku' => 'AGR-00144-FRU',
        ]);
    }
}
