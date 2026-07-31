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

class UomFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $uomSack;
    private $uomBox;
    private $productSack;
    private $productNoUom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['category_name' => 'Grains']);
        $currency = Currency::create(['currency_code' => 'PHP', 'currency_name' => 'Philippine Peso', 'exchange_rate' => 1.00]);

        $this->uomSack = UnitOfMeasure::create(['uom_code' => 'SACK', 'uom_name' => 'Sack', 'description' => '50kg Sack']);
        $this->uomBox = UnitOfMeasure::create(['uom_code' => 'BOX', 'uom_name' => 'Box', 'description' => '15kg Box']);

        $this->productSack = Product::create([
            'sku' => 'AGRI-RICE-001',
            'name' => 'Premium Rice Seeds',
            'category_id' => $category->id,
            'uom_id' => $this->uomSack->id,
            'currency_id' => $currency->id,
            'base_price' => 2500.00,
            'min_quantity_threshold' => 10.00,
            'lead_time_days' => 3,
        ]);

        $this->productNoUom = Product::create([
            'sku' => 'AGRI-RAW-999',
            'name' => 'Unassigned Raw Material',
            'category_id' => $category->id,
            'uom_id' => $this->uomSack->id,
            'currency_id' => $currency->id,
            'base_price' => 100.00,
            'min_quantity_threshold' => 5.00,
            'lead_time_days' => 2,
        ]);
    }

    public function test_purchase_requisition_saves_exact_uom_from_item_master()
    {
        $payload = [
            'action' => 'continue',
            'title' => 'PR - Premium Rice Seeds',
            'department' => 'Farm Operations',
            'urgency' => 'High',
            'items' => [
                [
                    'sku' => $this->productSack->sku,
                    'name' => $this->productSack->name,
                    'unit' => 'Sack',
                    'qty' => 10,
                    'unit_price' => 2500.00,
                    'justification' => 'Planting season',
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('requisitions.store'), $payload);

        $response->assertRedirect();

        $requisition = Requisition::latest()->first();
        $this->assertNotNull($requisition);
        $this->assertEquals(1, $requisition->items->count());
        $this->assertEquals('Sack', $requisition->items->first()->unit);
    }

    public function test_requisition_submission_fails_when_item_has_no_uom_assigned()
    {
        $payload = [
            'action' => 'continue',
            'title' => 'PR - Unassigned Material',
            'department' => 'Farm Operations',
            'urgency' => 'Medium',
            'items' => [
                [
                    'sku' => $this->productNoUom->sku,
                    'name' => $this->productNoUom->name,
                    'unit' => 'No UOM Assigned',
                    'qty' => 5,
                    'unit_price' => 100.00,
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('requisitions.store'), $payload);

        $response->assertSessionHasErrors(['items.0.unit']);
    }
}
