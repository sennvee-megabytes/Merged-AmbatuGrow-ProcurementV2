<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierNegativeInputRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_submit_negative_unit_price(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Farm Fresh',
            'name' => 'Farm Fresh',
            'slug' => 'farm-fresh',
            'supplier_code' => 'AGR-00100',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.products.store', $supplier->slug), [
            'name' => 'Carrots',
            'category_name' => 'Vegetables',
            'uom_code' => 'Box',
            'unit_price' => -100.00,
            'min_order' => 5,
            'lead_time_days' => 2,
            'code' => 'AGR-00100-VEG',
        ]);

        $response->assertSessionHasErrors(['unit_price']);
    }

    public function test_cannot_submit_negative_moq(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Farm Fresh',
            'name' => 'Farm Fresh',
            'slug' => 'farm-fresh-2',
            'supplier_code' => 'AGR-00101',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.products.store', $supplier->slug), [
            'name' => 'Carrots',
            'category_name' => 'Vegetables',
            'uom_code' => 'Box',
            'unit_price' => 100.00,
            'min_order' => -5,
            'lead_time_days' => 2,
            'code' => 'AGR-00101-VEG',
        ]);

        $response->assertSessionHasErrors(['min_order']);
    }

    public function test_cannot_submit_negative_lead_time(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Farm Fresh',
            'name' => 'Farm Fresh',
            'slug' => 'farm-fresh-3',
            'supplier_code' => 'AGR-00102',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.products.store', $supplier->slug), [
            'name' => 'Carrots',
            'category_name' => 'Vegetables',
            'uom_code' => 'Box',
            'unit_price' => 100.00,
            'min_order' => 5,
            'lead_time_days' => -2,
            'code' => 'AGR-00102-VEG',
        ]);

        $response->assertSessionHasErrors(['lead_time_days']);
    }
}
