<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierProductIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_shared_product_isolates_changes_to_current_supplier(): void
    {
        $user = User::factory()->create();

        $supplierA = Supplier::create([
            'supplier_name' => 'Supplier Alpha',
            'name' => 'Supplier Alpha',
            'slug' => 'supplier-alpha',
            'supplier_code' => 'AGR-00701',
            'status' => 'Active',
        ]);

        $supplierB = Supplier::create([
            'supplier_name' => 'Supplier Beta',
            'name' => 'Supplier Beta',
            'slug' => 'supplier-beta',
            'supplier_code' => 'AGR-00702',
            'status' => 'Active',
        ]);

        $category = \App\Models\Category::create(['category_name' => 'Rice']);
        $uom = \App\Models\UnitOfMeasure::create(['uom_code' => 'Sack', 'uom_name' => 'Sack']);
        $currency = \App\Models\Currency::create(['currency_code' => 'PHP', 'currency_name' => 'PHP', 'exchange_rate' => 1.0]);

        $sharedProduct = Product::create([
            'sku' => 'PRD-SHARED-RICE',
            'name' => 'Jasmine Rice',
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'currency_id' => $currency->id,
            'base_price' => 1000.00,
            'min_quantity_threshold' => 10.00,
            'lead_time_days' => 3,
        ]);

        // Attach shared product to both suppliers
        $supplierA->productsRelation()->attach($sharedProduct->id, [
            'supplier_sku' => 'AGR-00701-RIC',
            'unit_price' => 1000.00,
            'lead_time_days' => 3,
        ]);

        $supplierB->productsRelation()->attach($sharedProduct->id, [
            'supplier_sku' => 'AGR-00702-RIC',
            'unit_price' => 1000.00,
            'lead_time_days' => 3,
        ]);

        // Supplier A updates the shared product to "Super Jasmine Rice"
        $response = $this->actingAs($user)->put(route('suppliers.products.update', [$supplierA->slug, $sharedProduct->id]), [
            'name' => 'Super Jasmine Rice',
            'category_name' => 'Rice',
            'uom_code' => 'Sack',
            'unit_price' => 1200.00,
            'min_order' => 10,
            'lead_time_days' => 3,
            'code' => 'AGR-00701-RIC-SUPER',
        ]);

        $response->assertRedirect();

        // Supplier A's product list must show "Super Jasmine Rice"
        $supplierAProducts = $supplierA->fresh()->products;
        $this->assertEquals('Super Jasmine Rice', $supplierAProducts[0]['name']);

        // Supplier B's product list MUST remain untouched as "Jasmine Rice"
        $supplierBProducts = $supplierB->fresh()->products;
        $this->assertEquals('Jasmine Rice', $supplierBProducts[0]['name']);
    }
}
