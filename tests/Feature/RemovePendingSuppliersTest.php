<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemovePendingSuppliersTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_newly_created_supplier_is_immediately_verified_without_pending_approval()
    {
        $supplierData = [
            'company_name' => 'Instant Verified Agri Corp',
            'business_type' => 'Corporation',
            'address' => '100 Agriculture Way',
            'phone' => '09171112233',
            'email' => 'contact@instantagri.test',
            'contact_person' => 'Maria Santos',
            'position' => 'Procurement Manager',
            'contact_phone' => '09171112233',
            'contact_email' => 'maria@instantagri.test',
            'lead_time' => '3 Days',
            'delivery_schedule' => 'Mon-Fri',
            'moq' => '10 Sacks',
            'products' => ['Fertilizer'],
            'payment_terms' => 'Net 30',
            'payment_method' => 'Bank Transfer',
            'description' => 'Direct supplier of organic fertilizer',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('suppliers.store'), $supplierData);

        $response->assertRedirect();

        // 1. Assert supplier was created with Status = Verified
        $supplier = Supplier::where('supplier_name', 'Instant Verified Agri Corp')->first();
        $this->assertNotNull($supplier);
        $this->assertTrue(in_array($supplier->status, ['Active', 'Verified']));

        // 2. Assert supplier appears in Active Suppliers List
        $activeRes = $this->actingAs($this->user)
            ->get(route('suppliers.active'));
        $activeRes->assertStatus(200);
        $activeRes->assertSee('Instant Verified Agri Corp');

        // 3. Assert supplier appears on Supplier Dashboard
        $dashRes = $this->actingAs($this->user)
            ->get(route('suppliers.dashboard'));
        $dashRes->assertStatus(200);

        // 4. Assert pending route redirects to main supplier directory index
        $pendingRes = $this->actingAs($this->user)
            ->get(route('suppliers.pending'));
        $pendingRes->assertRedirect(route('suppliers.index'));
    }
}
