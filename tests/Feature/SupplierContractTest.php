<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_supplier_contract_with_calculated_duration(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Acme Supplies',
            'name' => 'Acme Supplies',
            'slug' => 'acme-supplies',
            'status' => 'Active',
            'contract_start' => '2026-01-01',
            'contract_end' => '2026-01-31',
            'contract_duration' => '30 days',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.updateContract', $supplier->slug), [
            'contract_start' => '2026-02-01',
            'contract_end' => '2026-03-03', // 30 days
            'payment_terms' => 'Net 30',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'contract_duration' => '30 days',
            'payment_terms' => 'Net 30',
        ]);
    }

    public function test_contract_end_before_start_fails_validation(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Beta Supplies',
            'name' => 'Beta Supplies',
            'slug' => 'beta-supplies',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.updateContract', $supplier->slug), [
            'contract_start' => '2026-05-10',
            'contract_end' => '2026-05-01', // Invalid: end before start
        ]);

        $response->assertSessionHasErrors(['contract_end']);
    }

    public function test_days_remaining_is_formatted_as_integer(): void
    {
        $supplier = Supplier::create([
            'supplier_name' => 'Farming Equip Co',
            'name' => 'Farming Equip Co',
            'slug' => 'farming-equip-co',
            'status' => 'Active',
            'contract_start' => now()->subDays(10)->format('Y-m-d'),
            'contract_end' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $contractData = $supplier->contract;
        $this->assertMatchesRegularExpression('/^\d+ Days$/', $contractData['days_remaining']);
        $this->assertStringNotContainsString('.', $contractData['days_remaining']);
    }

    public function test_invalid_payment_terms_option_fails_validation(): void
    {
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'supplier_name' => 'Gamma Supplies',
            'name' => 'Gamma Supplies',
            'slug' => 'gamma-supplies',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('suppliers.updateContract', $supplier->slug), [
            'contract_start' => '2026-01-01',
            'contract_end' => '2026-01-31',
            'payment_terms' => 'Custom Invalid Term',
        ]);

        $response->assertSessionHasErrors(['payment_terms']);
    }
}
