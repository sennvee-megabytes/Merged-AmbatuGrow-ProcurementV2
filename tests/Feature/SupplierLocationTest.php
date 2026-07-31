<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierLocationTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_supplier_location_is_saved_and_displayed_exactly_as_entered()
    {
        $exactLocation = 'Indang, Cavite';
        $fullAddress = 'Barangay Kaytambog, Indang, Cavite';

        $supplierData = [
            'company_name' => 'Cavite Organic Growers Co.',
            'business_type' => 'Cooperative',
            'location' => $exactLocation,
            'address' => $fullAddress,
            'phone' => '09179998877',
            'email' => 'info@cavitegrowers.test',
            'contact_person' => 'Juan Dela Cruz',
            'position' => 'Cooperative Head',
            'contact_phone' => '09179998877',
            'contact_email' => 'juan@cavitegrowers.test',
            'lead_time' => '2–3 Business Days',
            'delivery_schedule' => 'Monday – Saturday',
            'moq' => '10 Sacks',
            'products' => ['Vegetables'],
            'payment_terms' => 'Net 30',
            'payment_method' => 'Bank Transfer',
            'description' => 'Supplier located in Cavite',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('suppliers.store'), $supplierData);

        $response->assertRedirect();

        // 1. Database assertion
        $supplier = Supplier::where('supplier_name', 'Cavite Organic Growers Co.')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals($exactLocation, $supplier->location);

        // 2. Supplier Details assertion
        $showRes = $this->actingAs($this->user)
            ->get(route('suppliers.show', $supplier->slug));
        $showRes->assertStatus(200);
        $showRes->assertSee($exactLocation);
        $showRes->assertSee($fullAddress);

        // 3. Supplier Directory assertion
        $indexRes = $this->actingAs($this->user)
            ->get(route('suppliers.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee($exactLocation);

        // 4. Active Supplier List assertion
        $activeRes = $this->actingAs($this->user)
            ->get(route('suppliers.active'));
        $activeRes->assertStatus(200);
        $activeRes->assertSee($exactLocation);
    }
}
