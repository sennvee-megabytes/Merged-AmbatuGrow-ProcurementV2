<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuantityFieldVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['category_name' => 'Grains']);
        $currency = Currency::create(['currency_code' => 'PHP', 'currency_name' => 'Philippine Peso', 'exchange_rate' => 1.00]);
        $uom = UnitOfMeasure::create(['uom_code' => 'SACK', 'uom_name' => 'Sack', 'description' => '50kg Sack']);

        $this->product = Product::create([
            'sku' => 'AGRI-RICE-001',
            'name' => 'Premium Rice Seeds',
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'currency_id' => $currency->id,
            'base_price' => 10.00,
            'min_quantity_threshold' => 10.00,
            'lead_time_days' => 3,
        ]);
    }

    public function test_requisition_saves_large_quantity_and_calculates_total_correctly()
    {
        $largeQty = 999999;
        $unitPrice = 15.00;

        $payload = [
            'action' => 'continue',
            'title' => 'PR - Large Bulk Seeds Order',
            'department' => 'Farm Operations',
            'urgency' => 'High',
            'items' => [
                [
                    'sku' => $this->product->sku,
                    'name' => $this->product->name,
                    'unit' => 'Sack',
                    'qty' => $largeQty,
                    'unit_price' => $unitPrice,
                    'justification' => 'Mass distribution',
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('requisitions.store'), $payload);

        $response->assertRedirect();

        $requisition = Requisition::latest()->first();
        $this->assertNotNull($requisition);
        $this->assertEquals(1, $requisition->items->count());
        $this->assertEquals($largeQty, (int)$requisition->items->first()->qty);
        $this->assertEquals($largeQty * $unitPrice, (float)$requisition->total);
    }
}
