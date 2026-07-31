<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierProductNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_product_with_custom_dynamic_name(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Orchard Fresh',
            'name' => 'Orchard Fresh',
            'slug' => 'orchard-fresh',
            'supplier_code' => 'AGR-00500',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.products.store', $supplier->slug), [
            'name' => 'Fuji Apple',
            'category_name' => 'Fruits',
            'uom_code' => 'Box',
            'unit_price' => 1800.00,
            'min_order' => 5,
            'lead_time_days' => 2,
            'code' => 'AGR-00500-FRU',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name' => 'Fuji Apple',
        ]);

        $this->assertDatabaseHas('product_suppliers', [
            'supplier_id' => $supplier->id,
            'supplier_sku' => 'AGR-00500-FRU',
        ]);
    }

    public function test_can_update_product_custom_dynamic_name(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Green Valley',
            'name' => 'Green Valley',
            'slug' => 'green-valley',
            'supplier_code' => 'AGR-00600',
            'status' => 'Active',
        ]);

        $category = \App\Models\Category::create(['category_name' => 'Fruits']);
        $uom = \App\Models\UnitOfMeasure::create(['uom_code' => 'Box', 'uom_name' => 'Box']);
        $currency = \App\Models\Currency::create(['currency_code' => 'PHP', 'currency_name' => 'PHP', 'exchange_rate' => 1.0]);

        $product = Product::create([
            'sku' => 'PRD-APPLE-123',
            'name' => 'Fuji Apple',
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'currency_id' => $currency->id,
            'base_price' => 1800.00,
            'min_quantity_threshold' => 5.00,
            'lead_time_days' => 2,
        ]);

        $supplier->productsRelation()->attach($product->id, [
            'supplier_sku' => 'AGR-00600-FRU',
            'unit_price' => 1800.00,
            'lead_time_days' => 2,
        ]);

        $response = $this->actingAs($user)->put(route('suppliers.products.update', [$supplier->slug, $product->id]), [
            'name' => 'Honeycrisp Apple',
            'category_name' => 'Fruits',
            'uom_code' => 'Box',
            'unit_price' => 2100.00,
            'min_order' => 5,
            'lead_time_days' => 2,
            'code' => 'AGR-00600-FRU',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Honeycrisp Apple',
        ]);
    }
}
