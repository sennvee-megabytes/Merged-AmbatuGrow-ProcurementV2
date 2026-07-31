<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_store_new_supplier(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->post(route('suppliers.store'), [
            'company_name' => 'Test Supplier Inc',
            'business_type' => 'Corporation',
            'address' => '123 Test St, Test City',
            'phone' => '1234567890',
            'email' => 'test@supplier.com',
            'contact_person' => 'John Doe',
            'position' => 'Manager',
            'contact_phone' => '0987654321',
            'contact_email' => 'johndoe@supplier.com',
            'lead_time' => '3 Days',
            'delivery_schedule' => 'Weekly',
            'moq' => '10 Units',
            'products' => ['Rice', 'Mango'],
            'payment_terms' => 'Net 30',
            'payment_method' => 'Bank Transfer',
            'description' => 'Test supplier description'
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'supplier_name' => 'Test Supplier Inc',
            'email' => 'test@supplier.com',
        ]);
    }

    public function test_cannot_store_duplicate_supplier_name_or_email(): void
    {
        $user = \App\Models\User::factory()->create();

        Supplier::create([
            'supplier_name' => 'Existing Supplier',
            'name' => 'Existing Supplier',
            'email' => 'existing@supplier.com',
            'slug' => 'existing-supplier',
            'status' => 'Active',
        ]);

        // Duplicate name
        $response1 = $this->actingAs($user)->from(route('suppliers.create'))->post(route('suppliers.store'), [
            'company_name' => 'Existing Supplier',
            'business_type' => 'Corporation',
            'address' => '123 Test St, Test City',
            'phone' => '1234567890',
            'email' => 'newemail@supplier.com',
            'contact_person' => 'John Doe',
            'position' => 'Manager',
            'contact_phone' => '0987654321',
            'contact_email' => 'johndoe@supplier.com',
            'lead_time' => '3 Days',
            'delivery_schedule' => 'Weekly',
            'moq' => '10 Units',
            'products' => ['Rice'],
            'payment_terms' => 'Net 30',
            'payment_method' => 'Bank Transfer',
        ]);

        $response1->assertRedirect(route('suppliers.create'));
        $response1->assertSessionHasErrors(['company_name']);

        // Duplicate email
        $response2 = $this->actingAs($user)->from(route('suppliers.create'))->post(route('suppliers.store'), [
            'company_name' => 'Brand New Supplier',
            'business_type' => 'Corporation',
            'address' => '123 Test St, Test City',
            'phone' => '1234567890',
            'email' => 'existing@supplier.com',
            'contact_person' => 'John Doe',
            'position' => 'Manager',
            'contact_phone' => '0987654321',
            'contact_email' => 'johndoe@supplier.com',
            'lead_time' => '3 Days',
            'delivery_schedule' => 'Weekly',
            'moq' => '10 Units',
            'products' => ['Rice'],
            'payment_terms' => 'Net 30',
            'payment_method' => 'Bank Transfer',
        ]);

        $response2->assertRedirect(route('suppliers.create'));
        $response2->assertSessionHasErrors(['email']);
    }

    public function test_others_without_specified_product_fails_validation(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->from(route('suppliers.create'))->post(route('suppliers.store'), [
            'company_name' => 'Custom Product Supplier',
            'business_type' => 'Corporation',
            'address' => '123 Test St, Test City',
            'phone' => '1234567890',
            'email' => 'custom@supplier.com',
            'contact_person' => 'John Doe',
            'position' => 'Manager',
            'contact_phone' => '0987654321',
            'contact_email' => 'johndoe@supplier.com',
            'lead_time' => '3 Days',
            'delivery_schedule' => 'Weekly',
            'moq' => '10 Units',
            'products' => ['Others'],
            'specified_product' => '', // Empty specified product
            'payment_terms' => 'Net 30',
            'payment_method' => 'Bank Transfer',
        ]);

        $response->assertRedirect(route('suppliers.create'));
        $response->assertSessionHasErrors(['specified_product']);
    }

    public function test_others_with_specified_product_creates_custom_product(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->post(route('suppliers.store'), [
            'company_name' => 'Bio Supplier',
            'business_type' => 'Corporation',
            'address' => '123 Test St, Test City',
            'phone' => '1234567890',
            'email' => 'bio@supplier.com',
            'contact_person' => 'John Doe',
            'position' => 'Manager',
            'contact_phone' => '0987654321',
            'contact_email' => 'johndoe@supplier.com',
            'lead_time' => '3 Days',
            'delivery_schedule' => 'Weekly',
            'moq' => '10 Units',
            'products' => ['Others'],
            'specified_product' => 'Bio Organic Fertilizer',
            'payment_terms' => 'Net 30',
            'payment_method' => 'Bank Transfer',
        ]);

        $response->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Bio Organic Fertilizer',
        ]);
    }
}
