<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Category;
use App\Models\UnitOfMeasure;
use App\Models\Currency;
use App\Models\PaymentTerm;
use App\Models\PurchaseOrder;
use App\Models\POItem;
use App\Models\PurchaseOrderItem;
use App\Models\SupplierInvoice;
use App\Models\Invoice;
use App\Models\DeliveryReceipt;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\ApprovalStep;
use App\Models\User;
use App\Models\ContractHistory;
use App\Models\BlacklistedSupplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MassProcurementSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure core lookup values exist
        $currencies = [
            ['currency_code' => 'PHP', 'currency_name' => 'Philippine Peso', 'exchange_rate' => 1.0000],
            ['currency_code' => 'USD', 'currency_name' => 'US Dollar', 'exchange_rate' => 58.5000],
            ['currency_code' => 'EUR', 'currency_name' => 'Euro', 'exchange_rate' => 63.2000],
        ];
        foreach ($currencies as $c) {
            Currency::firstOrCreate(['currency_code' => $c['currency_code']], $c);
        }
        $php = Currency::where('currency_code', 'PHP')->first();

        $uoms = [
            ['uom_code' => 'KG', 'uom_name' => 'Kilogram', 'description' => 'Weight unit'],
            ['uom_code' => 'LTR', 'uom_name' => 'Liter', 'description' => 'Volume unit'],
            ['uom_code' => 'SACK', 'uom_name' => 'Sack', 'description' => '50kg agricultural packaging'],
            ['uom_code' => 'UNIT', 'uom_name' => 'Unit', 'description' => 'Single item counting'],
            ['uom_code' => 'BOX', 'uom_name' => 'Box', 'description' => 'Bulk packaging box'],
        ];
        foreach ($uoms as $u) {
            UnitOfMeasure::firstOrCreate(['uom_code' => $u['uom_code']], $u);
        }
        $sack = UnitOfMeasure::where('uom_code', 'SACK')->first();
        $unit = UnitOfMeasure::where('uom_code', 'UNIT')->first();

        $paymentTerms = [
            ['term_code' => 'COD', 'description' => 'Cash On Delivery', 'net_days' => 0, 'discount_percent' => 0.00],
            ['term_code' => 'NET15', 'description' => 'Net 15 Days', 'net_days' => 15, 'discount_percent' => 0.00],
            ['term_code' => 'NET30', 'description' => 'Net 30 Days', 'net_days' => 30, 'discount_percent' => 0.00],
            ['term_code' => 'NET45', 'description' => 'Net 45 Days', 'net_days' => 45, 'discount_percent' => 0.00],
            ['term_code' => '2/10NET30', 'description' => '2% discount if paid within 10 days, Net 30', 'net_days' => 30, 'discount_percent' => 2.00],
        ];
        foreach ($paymentTerms as $pt) {
            PaymentTerm::firstOrCreate(['term_code' => $pt['term_code']], $pt);
        }
        $net30 = PaymentTerm::where('term_code', 'NET30')->first();

        // 2. Categories
        $categoriesList = [
            'Seedlings & Crops',
            'Fertilizers & Nutrients',
            'Irrigation Equipment',
            'Heavy Machinery',
            'Logistics & Transport',
            'Harvest Tools',
        ];
        $categories = [];
        foreach ($categoriesList as $catName) {
            $categories[] = Category::firstOrCreate(['category_name' => $catName]);
        }

        // 3. Create Addresses
        $cities = ['Davao', 'Cebu', 'Bacolod', 'Cagayan de Oro', 'Zamboanga', 'Tarlac', 'Pampanga', 'Laguna', 'Batangas', 'Iloilo'];
        $addresses = [];
        for ($i = 1; $i <= 15; $i++) {
            $city = $cities[array_rand($cities)];
            $addresses[] = Address::create([
                'street' => "{$i}42 Agricultural Zone Road",
                'city' => $city,
                'province' => "{$city} Province",
                'zipcode' => (string)rand(1000, 9900),
                'country' => 'Philippines',
            ]);
        }

        // 4. Create Suppliers
        $supplierNames = [
            'AgriSeed Systems Co.',
            'Davao Fertilizer Supply',
            'HydroTech Irrigation Co.',
            'Northern Harvest Logistics',
            'Mindanao Agro Equipment',
            'Greenfields Seedlings Inc.',
            'EcoGrow Organic Fertilizer',
            'Batangas Tractor Services',
            'Bacolod Crop Protection',
            'Isabela Agri-Supplies',
            'Cebu Irrigation Systems',
            'Valley Farm Logistics',
        ];

        $suppliers = [];
        foreach ($supplierNames as $index => $name) {
            $status = $index % 4 === 0 ? 'Verified' : ($index % 6 === 0 ? 'Blacklisted' : 'Active');
            $slug = Str::slug($name);
            
            $suppliers[] = Supplier::create([
                'slug' => $slug,
                'supplier_name' => $name,
                'name' => $name,
                'category' => $categories[array_rand($categories)]->category_name,
                'email' => strtolower(Str::slug($name)) . '@agripartner.test',
                'phone' => '+63 9' . rand(100000000, 999999999),
                'address_id' => $addresses[$index % count($addresses)]->id,
                'status' => $status,
                'supplier_id' => 'AGR-' . str_pad((string)($index + 15), 5, '0', STR_PAD_LEFT),
                'supplier_code' => 'CODE-' . strtoupper(substr(Str::slug($name), 0, 4)) . rand(10, 99),
                'business_type' => $index % 3 === 0 ? 'Corporation' : 'Partnership',
                'description' => "Premium distributor providing high-quality solutions for commercial agricultural operations: {$name}.",
                'rating' => number_format(3.0 + (rand(0, 20) / 10), 1),
                'since' => now()->subYears(rand(1, 5)),
                'location' => $addresses[$index % count($addresses)]->city . ', ' . $addresses[$index % count($addresses)]->province,
                'contact_name' => 'Representative ' . ($index + 1),
                'contact_role' => 'Sales Director',
                'contact_phone' => '+63 9' . rand(100000000, 999999999),
                'contact_email' => 'sales.' . strtolower(Str::slug($name)) . '@agripartner.test',
                'total_orders' => rand(15, 60),
                'total_spent' => rand(150000, 850000),
                'avg_order_value' => rand(10000, 25000),
                'on_time_rate' => rand(85, 99),
            ]);
        }

        // 5. Create Standalone Blacklisted
        $blacklistReasons = [
            'Delivered substandard products and failed quality audit twice.',
            'Unreasonable delays and consistent breach of contract terms.',
            'Involved in non-compliant regulatory transport violations.',
        ];
        foreach ($blacklistReasons as $index => $reason) {
            BlacklistedSupplier::create([
                'name' => 'Restricted Vendor ' . ($index + 1),
                'supplier_code' => 'BL-' . rand(100, 999),
                'reason' => $reason,
                'blacklisted_since' => now()->subMonths(rand(1, 6)),
                'risk_level' => $index % 2 === 0 ? 'High' : 'Critical',
            ]);
        }

        // 6. Create Products
        $productNames = [
            'Premium Hybrid Rice Seeds',
            'Nitrogen NPK Fertilizer',
            'Drip Irrigation Pipes 100m',
            'Submersible Water Pump',
            'Heavy Duty Harvest Knives',
            'Organic Compost Soil',
            'Agri-Tractor Engine Oil',
            'Crop Spraying Nozzles',
            'Greenhouse Film Roll',
            'High-Yield Corn Seedlings',
            'Potassium Rich Soil Additive',
            'Solar Irrigation Controller',
            'Pest Control Spray 5L',
            'Vegetable Seed Mix',
            'Silage Wrap Roll',
            'Farming Hand Tools Set',
        ];

        $products = [];
        foreach ($productNames as $index => $prodName) {
            $cat = $categories[$index % count($categories)];
            $products[] = Product::create([
                'sku' => 'PRD-' . strtoupper(substr(Str::slug($prodName), 0, 5)) . '-' . str_pad((string)$index, 3, '0', STR_PAD_LEFT),
                'name' => $prodName,
                'description' => "Professional grade agricultural resource: {$prodName}.",
                'category_id' => $cat->id,
                'uom_id' => ($index % 3 === 0 ? $sack->id : $unit->id),
                'currency_id' => $php->id,
                'base_price' => rand(850, 4500),
                'min_quantity_threshold' => 15.00,
                'lead_time_days' => rand(2, 5),
            ]);
        }

        // 7. Attach Products to Suppliers (Junction)
        foreach ($suppliers as $sup) {
            if ($sup->status === 'Blacklisted') continue;
            
            // Link 4 random products to each active/pending supplier
            $linkedProds = array_rand($products, 4);
            foreach ($linkedProds as $pIdx) {
                $prod = $products[$pIdx];
                $sup->productsRelation()->attach($prod->id, [
                    'supplier_sku' => 'SUP-SKU-' . rand(1000, 9999),
                    'unit_price' => $prod->base_price * (0.9 + (rand(0, 20) / 100)), // ±10% base price
                    'lead_time_days' => $prod->lead_time_days + rand(-1, 1),
                    'is_preferred' => rand(0, 1) === 1,
                ]);
            }
        }

        // 8. Create Requisitions, Requisition Items, and Approval Steps
        $users = User::all();
        $managers = User::where('role', 'manager')->get();
        $deptHeads = User::where('role', 'department_head')->get();
        $financeManagers = User::where('role', 'finance_manager')->get();

        $requisitionTitles = [
            'Emergency Seed Supply for Q3 planting',
            'Fertilizer supply for North Field',
            'Replacement irrigation controllers',
            'Harvest tools for seasonal crew',
            'Bulk soil compost procurement',
            'Greenhouse setup components',
        ];

        foreach ($requisitionTitles as $index => $title) {
            $requestor = $users->random();
            $subtotal = rand(25000, 125000);
            $status = $index % 3 === 0 ? 'approved' : ($index % 2 === 0 ? 'rejected' : 'pending_approval');
            
            $req = Requisition::create([
                'code' => 'PR-' . now()->format('Y') . '-' . str_pad((string)($index + 10), 5, '0', STR_PAD_LEFT),
                'title' => $title,
                'department' => $requestor->department ?: 'Operations',
                'requestor_id' => $requestor->id,
                'needed_by' => now()->addDays(rand(10, 20)),
                'purpose' => "Standard inventory procurement for seasonal operations: {$title}.",
                'subtotal' => $subtotal,
                'tax_rate' => 12.00,
                'tax_amount' => $subtotal * 0.12,
                'total' => $subtotal * 1.12,
                'approval_type' => $index % 2 === 0 ? 'sequential' : 'parallel',
                'status' => $status,
                'urgency' => $index % 3 === 0 ? 'High' : 'Medium',
                'submitted_at' => now()->subDays(rand(1, 5)),
            ]);

            // Add Items
            RequisitionItem::create([
                'requisition_id' => $req->id,
                'name' => $products[array_rand($products)]->name,
                'qty' => rand(10, 50),
                'unit' => 'Unit',
                'unit_price' => rand(1000, 2500),
                'total' => $subtotal,
            ]);

            // Add Approval Steps
            $sarah = User::where('username', 'sarah.jenkins')->first() ?? $managers->first();
            $michael = User::where('username', 'finance.manager')->first() ?? $financeManagers->first();
            $johny = User::where('username', 'johny.papa')->first() ?? $deptHeads->first();

            ApprovalStep::create([
                'requisition_id' => $req->id,
                'step_order' => 1,
                'step_type' => 'manager_approval',
                'label' => 'Project Manager Approval',
                'description' => 'Level 1: Sarah Jenkins (Project Manager)',
                'required' => true,
                'approver_id' => $sarah?->id,
                'status' => $status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending'),
                'comment' => $status === 'approved' ? 'Looks good.' : ($status === 'rejected' ? 'budget exceeded' : null),
                'acted_at' => $status !== 'pending_approval' ? now()->subDays(1) : null,
            ]);

            ApprovalStep::create([
                'requisition_id' => $req->id,
                'step_order' => 2,
                'step_type' => 'finance_approval',
                'label' => 'Finance Manager Approval',
                'description' => 'Level 2: Michael Finn (Finance Manager)',
                'required' => true,
                'approver_id' => $michael?->id,
                'status' => $status === 'approved' ? 'approved' : 'pending',
                'acted_at' => $status === 'approved' ? now()->subDays(1) : null,
            ]);

            ApprovalStep::create([
                'requisition_id' => $req->id,
                'step_order' => 3,
                'step_type' => 'department_head_approval',
                'label' => 'Head Approval',
                'description' => 'Level 3: Johny Papa (Head)',
                'required' => true,
                'approver_id' => $johny?->id,
                'status' => $status === 'approved' ? 'approved' : 'pending',
                'acted_at' => $status === 'approved' ? now() : null,
            ]);
        }

        // 9. Create Purchase Orders (and sync both PO Line Item tables!)
        $activeSuppliers = Supplier::where('status', 'Active')->get();
        
        for ($i = 1; $i <= 15; $i++) {
            $sup = $activeSuppliers->random();
            $status = $i % 4 === 0 ? 'sent' : ($i % 5 === 0 ? 'delivered' : 'draft');
            $poNumber = 'PO-' . now()->format('Y') . '-' . str_pad((string)($i + 12), 3, '0', STR_PAD_LEFT);
            
            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $sup->id,
                'status' => $status,
                'total' => 0.00,
                'expected_delivery' => now()->addDays(rand(5, 12)),
                'issued_at' => $status !== 'draft' ? now()->subDays(rand(1, 3)) : null,
                'payment_term_id' => $net30->id,
                'currency_id' => $php->id,
                'order_date' => now()->subDays(rand(2, 6)),
                'created_by' => $users->random()->id,
            ]);

            // Add Items to both PO line item tables
            $itemCount = rand(1, 3);
            $totalAmount = 0;
            
            for ($k = 0; $k < $itemCount; $k++) {
                $prod = $products[($i + $k) % count($products)];
                $qty = rand(10, 100);
                $price = $prod->base_price;
                $lineTotal = $qty * $price;
                $totalAmount += $lineTotal;

                // Populating table 1: purchase_order_items (Order Management)
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'sku' => $prod->sku,
                    'name' => $prod->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'line_total' => $lineTotal,
                ]);

                // Populating table 2: po_items (Supplier Management & Matching)
                POItem::create([
                    'po_id' => $po->id,
                    'product_id' => $prod->id,
                    'quantity' => $qty,
                    'uom_id' => $prod->uom_id,
                    'unit_price' => $price,
                ]);
            }

            $po->update(['total' => $totalAmount]);

            // 10. Generate matching receipts & invoices for Matching module
            if ($status === 'delivered' || $i % 3 === 0) {
                // Create Delivery Receipt (GRN)
                $drItems = [];
                foreach ($po->items as $item) {
                    $drItems[] = [
                        'name' => $item->name,
                        'sku' => $item->sku,
                        'qty_ordered' => $item->quantity,
                        'qty_received' => $item->quantity, // perfect match
                    ];
                }
                DeliveryReceipt::create([
                    'dr_number' => 'GRN-2026-' . str_pad((string)($i + 1500), 5, '0', STR_PAD_LEFT),
                    'purchase_order_id' => $po->id,
                    'received_at' => now()->subDays(2),
                    'items' => $drItems,
                ]);

                // Create Supplier Invoice (Supplier Management)
                SupplierInvoice::create([
                    'supplier_id' => $sup->id,
                    'po_id' => $po->id,
                    'invoice_number' => 'INV-SUP-' . rand(10000, 99999),
                    'invoice_date' => now()->subDays(1),
                    'due_date' => now()->addDays(29),
                ]);

                // Create Standard Invoice (Order Management)
                Invoice::create([
                    'invoice_number' => 'INV-ORD-' . rand(10000, 99999),
                    'supplier_id' => $sup->id,
                    'purchase_order_id' => $po->id,
                    'amount' => $po->total,
                    'received_at' => now()->subDays(1),
                ]);
            }
        }
    }
}
