<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierDashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_dynamic_product_supplied_breakdown(): void
    {
        $user = User::factory()->create();

        $category = \App\Models\Category::create(['category_name' => 'Grains']);
        $uom = \App\Models\UnitOfMeasure::create(['uom_code' => 'Sack', 'uom_name' => 'Sack']);
        $currency = \App\Models\Currency::create(['currency_code' => 'PHP', 'currency_name' => 'PHP', 'exchange_rate' => 1.0]);

        $product = Product::create([
            'sku' => 'PRD-GRAIN-01',
            'name' => 'Golden Grain',
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'currency_id' => $currency->id,
            'base_price' => 2000.00,
            'min_quantity_threshold' => 10,
            'lead_time_days' => 3,
        ]);

        $supplier = Supplier::create([
            'supplier_name' => 'Grain Supply Corp',
            'name' => 'Grain Supply Corp',
            'slug' => 'grain-supply-corp',
            'status' => 'Active',
        ]);

        $supplier->productsRelation()->attach($product->id, [
            'supplier_sku' => 'AGR-GRA-01',
            'unit_price' => 2000.00,
            'lead_time_days' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('suppliers.dashboard'));

        $response->assertOk();
        $response->assertViewHas('productSuppliedData');

        $breakdown = $response->viewData('productSuppliedData');
        $this->assertIsArray($breakdown);

        $grainsItem = collect($breakdown)->firstWhere('label', 'Grains');
        $this->assertNotNull($grainsItem);
        $this->assertEquals(1, $grainsItem['count']);
        $this->assertGreaterThan(0, $grainsItem['percentage']);
    }
}
