<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierProductsAddEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_product_with_dynamic_uppercase_code_suffix(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Harvest Co',
            'name' => 'Harvest Co',
            'slug' => 'harvest-co',
            'supplier_code' => 'AGR-00143',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.products.store', $supplier->slug), [
            'name' => 'Special Jasmine Rice',
            'category_name' => 'Rice',
            'uom_code' => 'Sack',
            'unit_price' => 2500.00,
            'min_order' => 10,
            'lead_time_days' => 3,
            'code' => 'agr-00143-ric', // lowercase input
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('product_suppliers', [
            'supplier_id' => $supplier->id,
            'supplier_sku' => 'AGR-00143-RIC', // Strictly uppercase
            'unit_price' => 2500.00,
        ]);
    }

    public function test_custom_others_product_type_generates_three_letter_suffix(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Bio Supplier',
            'name' => 'Bio Supplier',
            'slug' => 'bio-supplier',
            'supplier_code' => 'AGR-00199',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.products.store', $supplier->slug), [
            'name' => 'Fertilizer Pack',
            'category_name' => 'Others',
            'uom_code' => 'Box',
            'unit_price' => 1200.00,
            'min_order' => 5,
            'lead_time_days' => 2,
            'code' => '', // Auto-generate code
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('product_suppliers', [
            'supplier_id' => $supplier->id,
            'supplier_sku' => 'AGR-00101-FER', // 3-letter suffix of Fertilizer with dynamic sequence
        ]);
    }

    public function test_can_edit_existing_supplier_product(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Delta Agri',
            'name' => 'Delta Agri',
            'slug' => 'delta-agri',
            'supplier_code' => 'AGR-00200',
            'status' => 'Active',
        ]);

        $category = \App\Models\Category::create(['category_name' => 'Vegetables']);
        $uom = \App\Models\UnitOfMeasure::create(['uom_code' => 'Crate', 'uom_name' => 'Crate']);
        $currency = \App\Models\Currency::create(['currency_code' => 'PHP', 'currency_name' => 'PHP', 'exchange_rate' => 1.0]);

        $product = Product::create([
            'sku' => 'PRD-CABBAGE',
            'name' => 'Cabbage',
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'currency_id' => $currency->id,
            'base_price' => 900.00,
            'min_quantity_threshold' => 5.00,
            'lead_time_days' => 3,
        ]);

        $supplier->productsRelation()->attach($product->id, [
            'supplier_sku' => 'AGR-00200-VEG',
            'unit_price' => 900.00,
            'lead_time_days' => 3,
        ]);

        $response = $this->actingAs($user)->put(route('suppliers.products.update', [$supplier->slug, $product->id]), [
            'name' => 'Premium Organic Cabbage',
            'category_name' => 'Vegetables',
            'uom_code' => 'Crate',
            'unit_price' => 1100.00,
            'min_order' => 10,
            'lead_time_days' => 4,
            'code' => 'agr-00200-veg-updated', // Lowercase edit input
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Premium Organic Cabbage',
        ]);

        $this->assertDatabaseHas('product_suppliers', [
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
            'supplier_sku' => 'AGR-00200-VEG-UPDATED', // Converted to uppercase
            'unit_price' => 1100.00,
            'lead_time_days' => 4,
        ]);
    }

    public function test_can_delete_supplier_product(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Echo Agri',
            'name' => 'Echo Agri',
            'slug' => 'echo-agri',
            'supplier_code' => 'AGR-00300',
            'status' => 'Active',
        ]);

        $category = \App\Models\Category::create(['category_name' => 'Fruits']);
        $uom = \App\Models\UnitOfMeasure::create(['uom_code' => 'Box', 'uom_name' => 'Box']);
        $currency = \App\Models\Currency::create(['currency_code' => 'PHP', 'currency_name' => 'PHP', 'exchange_rate' => 1.0]);

        $product = Product::create([
            'sku' => 'PRD-MANGO',
            'name' => 'Sweet Mango',
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'currency_id' => $currency->id,
            'base_price' => 1500.00,
            'min_quantity_threshold' => 10.00,
            'lead_time_days' => 2,
        ]);

        $supplier->productsRelation()->attach($product->id, [
            'supplier_sku' => 'AGR-00300-FRU',
            'unit_price' => 1500.00,
            'lead_time_days' => 2,
        ]);

        $response = $this->actingAs($user)->delete(route('suppliers.products.destroy', [$supplier->slug, $product->id]));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Product deleted successfully.');

        $this->assertDatabaseMissing('product_suppliers', [
            'supplier_id' => $supplier->id,
            'product_id' => $product->id,
        ]);
    }
}
