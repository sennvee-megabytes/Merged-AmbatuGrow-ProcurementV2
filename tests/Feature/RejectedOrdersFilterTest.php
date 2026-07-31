<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RejectedOrdersFilterTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $supplier;
    private $requestor;
    private $approver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        $this->supplier = Supplier::create([
            'name' => 'Acme Supplies',
            'status' => 'active',
            'contact_person' => 'John Doe',
            'email' => 'acme@test.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metropolis',
        ]);
        $this->requestor = User::factory()->create(['name' => 'John Requester']);
        $this->approver = User::factory()->create(['name' => 'Sarah Approver']);
    }

    public function test_rejected_filter_displays_rejected_purchase_orders_and_correct_counter()
    {
        // 1. Create a rejected requisition & approval step
        $req = Requisition::create([
            'code' => 'PR-2026-REJ01',
            'title' => 'Rejected Project Supplies',
            'department' => 'Operations',
            'requestor_id' => $this->requestor->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'rejected',
            'total' => 5000.00,
        ]);

        ApprovalStep::create([
            'requisition_id' => $req->id,
            'step_order' => 1,
            'step_type' => 'manager_approval',
            'label' => 'Manager Step',
            'required' => true,
            'approver_id' => $this->approver->id,
            'status' => 'rejected',
            'comment' => 'Budget exceeds Q3 limit',
            'acted_at' => now()->subDay(),
        ]);

        // 2. Create a rejected PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-999999',
            'supplier_id' => $this->supplier->id,
            'requisition_id' => $req->id,
            'status' => 'rejected',
            'total' => 5000.00,
            'issued_at' => now()->subDays(2),
        ]);

        // Act as admin and visit procurement landing
        $response = $this->actingAs($this->user)
            ->get(route('procurement.home'));

        $response->assertStatus(200);

        // Verify view data
        $stats = $response->viewData('stats');
        $this->assertEquals(1, $stats['rejected']);

        $purchaseOrders = $response->viewData('purchaseOrders');
        $this->assertTrue($purchaseOrders->contains('id', $po->id));

        // Verify HTML content contains required fields
        $response->assertSee('PO-2026-999999');
        $response->assertSee('PR-2026-REJ01');
        $response->assertSee('Acme Supplies');
        $response->assertSee('Sarah Approver');
        $response->assertSee('Budget exceeds Q3 limit');
        $response->assertSee('Rejected');
    }
}
