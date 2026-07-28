<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierDetailsBackButtonTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->supplier = Supplier::create([
            'slug' => 'test-supplier-corp',
            'supplier_name' => 'Test Supplier Corp',
            'name' => 'Test Supplier Corp',
            'status' => 'Verified',
            'email' => 'test@supplier.com',
            'phone' => '123456789',
        ]);
    }

    public function test_supplier_details_page_contains_top_left_back_button()
    {
        $routes = [
            route('suppliers.show', $this->supplier->slug),
            route('suppliers.products', $this->supplier->slug),
            route('suppliers.purchase-history', $this->supplier->slug),
            route('suppliers.contract', $this->supplier->slug),
            route('suppliers.performance', $this->supplier->slug),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($this->user)->get($url);
            $response->assertStatus(200);
            $response->assertSee('Back to Supplier List');
            $response->assertSee('window.history.back()', false);
        }
    }
}
