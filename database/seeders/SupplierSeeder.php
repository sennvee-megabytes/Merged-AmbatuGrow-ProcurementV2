<?php

namespace Database\Seeders;

use App\Models\BlacklistedSupplier;
use App\Models\Supplier;
use App\Models\Address;
use App\Models\UnitOfMeasure;
use App\Models\PaymentTerm;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\POItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default User (for PO references)
        $user = User::first() ?? User::create([
            'name' => 'System Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Lookup Master Data Setup
        $phpCurrency = Currency::updateOrCreate(
            ['currency_code' => 'PHP'],
            ['currency_name' => 'Philippine Peso', 'exchange_rate' => 1.0000]
        );

        $net30 = PaymentTerm::updateOrCreate(
            ['term_code' => 'Net 30'],
            ['description' => 'Payment due 30 days from invoice date', 'net_days' => 30, 'discount_percent' => 0.00]
        );

        $net15 = PaymentTerm::updateOrCreate(
            ['term_code' => 'Net 15'],
            ['description' => 'Payment due 15 days from invoice date', 'net_days' => 15, 'discount_percent' => 0.00]
        );

        // UOMs
        $uoms = [
            'Sack' => '50kg Sack',
            'Box' => '15kg Box',
            'Crate' => '10kg Crate',
            'Pcs' => 'Pieces',
        ];
        $uomModels = [];
        foreach ($uoms as $code => $name) {
            $uomModels[$code] = UnitOfMeasure::updateOrCreate(
                ['uom_code' => $code],
                ['uom_name' => $name, 'description' => $name]
            );
        }

        // Categories
        $categories = ['Grains', 'Vegetables', 'Fruits', 'IT Equipment', 'Office Supplies'];
        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat] = Category::updateOrCreate(
                ['category_name' => $cat],
                ['parent_category_id' => null]
            );
        }

        // Products
        $productsData = [
            // Grains, Vegetables, Fruits
            ['name' => 'Rice', 'sku' => 'PRD-RICE', 'category' => 'Grains', 'uom' => 'Sack', 'price' => 2450.00],
            ['name' => 'Corn', 'sku' => 'PRD-CORN', 'category' => 'Grains', 'uom' => 'Sack', 'price' => 1850.00],
            ['name' => 'Onion', 'sku' => 'PRD-ONION', 'category' => 'Vegetables', 'uom' => 'Sack', 'price' => 1500.00],
            ['name' => 'Lettuce', 'sku' => 'PRD-LETTUCE', 'category' => 'Vegetables', 'uom' => 'Crate', 'price' => 950.00],
            ['name' => 'Cabbage', 'sku' => 'PRD-CABBAGE', 'category' => 'Vegetables', 'uom' => 'Sack', 'price' => 1100.00],
            ['name' => 'Mango', 'sku' => 'PRD-MANGO', 'category' => 'Fruits', 'uom' => 'Box', 'price' => 2100.00],
            ['name' => 'Coconut', 'sku' => 'PRD-COCONUT', 'category' => 'Fruits', 'uom' => 'Sack', 'price' => 1200.00],

            // IT Equipment
            ['name' => 'Developer Workstation Laptop', 'sku' => 'PRD-TEC-LAP-002', 'category' => 'IT Equipment', 'uom' => 'Pcs', 'price' => 58000.00],
            ['name' => 'USB-C Multiport Adapter', 'sku' => 'PRD-TEC-ADPT-004', 'category' => 'IT Equipment', 'uom' => 'Pcs', 'price' => 1800.00],
            ['name' => 'External 1TB SSD Drive', 'sku' => 'PRD-TEC-SSD-005', 'category' => 'IT Equipment', 'uom' => 'Pcs', 'price' => 4500.00],
            ['name' => 'Noise-Cancelling USB Headset', 'sku' => 'PRD-TEC-HDST-010', 'category' => 'IT Equipment', 'uom' => 'Pcs', 'price' => 3200.00],
            ['name' => '24-inch IPS Monitor', 'sku' => 'PRD-TEC-MON-005', 'category' => 'IT Equipment', 'uom' => 'Pcs', 'price' => 6800.00],
            ['name' => 'Wireless Keyboard & Mouse Combo', 'sku' => 'PRD-TEC-KBD-002', 'category' => 'IT Equipment', 'uom' => 'Pcs', 'price' => 1800.00],
            ['name' => 'Privacy Screen Filter 24-inch', 'sku' => 'PRD-TEC-SCRN-009', 'category' => 'IT Equipment', 'uom' => 'Pcs', 'price' => 1250.00],

            // Office Supplies
            ['name' => 'Premium A4 Paper Reams', 'sku' => 'PRD-OFF-PPR-009', 'category' => 'Office Supplies', 'uom' => 'Box', 'price' => 210.00],
            ['name' => 'Ergonomic Office Chair', 'sku' => 'PRD-OFF-CHR-001', 'category' => 'Office Supplies', 'uom' => 'Pcs', 'price' => 6500.00],
            ['name' => 'LED Desk Lamp with USB Charger', 'sku' => 'PRD-OFF-LAMP-003', 'category' => 'Office Supplies', 'uom' => 'Pcs', 'price' => 1450.00],
            ['name' => 'Whiteboard Magnetic 4x3ft', 'sku' => 'PRD-OFF-WBD-006', 'category' => 'Office Supplies', 'uom' => 'Pcs', 'price' => 3200.00],
            ['name' => 'Dry Erase Markers (Pack of 12)', 'sku' => 'PRD-OFF-MKR-007', 'category' => 'Office Supplies', 'uom' => 'Pcs', 'price' => 380.00],

            // Agricultural supplies (Green Harvest Co)
            ['name' => 'Organic Fertilizer Bags', 'sku' => 'PRD-GRN-FERT-001', 'category' => 'Grains', 'uom' => 'Sack', 'price' => 950.00],
            ['name' => 'Organic Potting Soil (20kg Bag)', 'sku' => 'PRD-GRN-SOIL-002', 'category' => 'Grains', 'uom' => 'Sack', 'price' => 250.00],
            ['name' => 'Hybrid Tomato Seeds (Pack of 50)', 'sku' => 'PRD-GRN-SEED-011', 'category' => 'Grains', 'uom' => 'Pcs', 'price' => 120.00],
            ['name' => 'Drip Irrigation Emitter (Pack of 100)', 'sku' => 'PRD-GRN-IRRI-013', 'category' => 'Grains', 'uom' => 'Pcs', 'price' => 600.00],
            ['name' => 'Pruning Shears Heavy Duty', 'sku' => 'PRD-GRN-SHR-014', 'category' => 'Grains', 'uom' => 'Pcs', 'price' => 450.00],
            ['name' => 'Garden Hose Reel Cart', 'sku' => 'PRD-GRN-HOSE-015', 'category' => 'Grains', 'uom' => 'Pcs', 'price' => 2200.00],
            ['name' => 'Biodegradable Seed Starter Trays', 'sku' => 'PRD-GRN-TRAY-016', 'category' => 'Grains', 'uom' => 'Pcs', 'price' => 95.00],
            ['name' => 'Nylon Plant Trellis Netting', 'sku' => 'PRD-GRN-NET-017', 'category' => 'Grains', 'uom' => 'Pcs', 'price' => 180.00],
            ['name' => 'Liquid Seaweed Fertilizer (5L)', 'sku' => 'PRD-GRN-FERT-018', 'category' => 'Grains', 'uom' => 'Sack', 'price' => 850.00],
            ['name' => 'Heavy Duty Gardening Gloves (M)', 'sku' => 'PRD-GRN-GLV-019', 'category' => 'Grains', 'uom' => 'Pcs', 'price' => 140.00],
            ['name' => 'pH Soil Tester Moisture Meter', 'sku' => 'PRD-GRN-TST-020', 'category' => 'Grains', 'uom' => 'Pcs', 'price' => 650.00],
        ];

        $prodModels = [];
        foreach ($productsData as $pData) {
            $prodModels[$pData['name']] = Product::updateOrCreate(
                ['sku' => $pData['sku']],
                [
                    'name' => $pData['name'],
                    'description' => $pData['name'] . ' product',
                    'category_id' => $catModels[$pData['category']]->id,
                    'uom_id' => $uomModels[$pData['uom']]->id,
                    'currency_id' => $phpCurrency->id,
                    'base_price' => $pData['price'],
                    'min_quantity_threshold' => 10.00,
                    'lead_time_days' => 3
                ]
            );
        }

        // Suppliers data
        $suppliers = [
            // Prepend legacy suppliers to align IDs with PurchaseOrderSeeder
            [
                'slug' => 'techvend-solutions',
                'name' => 'TechVend Solutions',
                'supplier_code' => 'AGR-00101',
                'since' => '2025-01-01',
                'description' => 'TechVend Solutions provides IT hardware, software, and networking services.',
                'business_type' => 'Corporation',
                'address' => '123 Tech Tower, Makati City, Philippines',
                'phone' => '09171234567',
                'email' => 'sales@techvend.example',
                'location' => 'Makati City',
                'rating' => 4.8,
                'status' => 'Active',
                'last_transaction' => '2026-06-12',
                'contact_name' => 'Mark Tee',
                'contact_role' => 'Sales Director',
                'total_orders' => 12,
                'total_spent' => 250000.00,
                'avg_order_value' => 20833.00,
                'on_time_rate' => 98,
                'contract_start' => '2025-01-01',
                'contract_end' => '2026-12-31',
                'contract_duration' => '2 Years',
                'payment_terms' => 'Net 30',
                'auto_renewal' => true,
                'contract_document' => 'TechVend_Master_Agreement.pdf',
                'contract_document_size' => '2.5 MB',
                'contract_scope' => [
                    'Provision of computer hardware and IT accessories',
                    'On-site setup and support',
                ],
                'avg_rating_delta' => '↑ 0.1 from last month',
                'on_time_delta' => '↑ 1% from last month',
                'quality_score' => 4.9,
                'quality_delta' => 'No change',
                'total_orders_delta' => '↑ 2 this month',
                'products' => [
                    ['name' => 'Developer Workstation Laptop', 'unit' => 'Pcs', 'price' => 58000.00, 'moq' => '1 Unit', 'lead_time' => 5],
                    ['name' => 'USB-C Multiport Adapter', 'unit' => 'Pcs', 'price' => 1800.00, 'moq' => '5 Units', 'lead_time' => 3],
                    ['name' => 'External 1TB SSD Drive', 'unit' => 'Pcs', 'price' => 4500.00, 'moq' => '2 Units', 'lead_time' => 3],
                    ['name' => 'Noise-Cancelling USB Headset', 'unit' => 'Pcs', 'price' => 3200.00, 'moq' => '5 Units', 'lead_time' => 3],
                    ['name' => '24-inch IPS Monitor', 'unit' => 'Pcs', 'price' => 6800.00, 'moq' => '2 Units', 'lead_time' => 4],
                    ['name' => 'Wireless Keyboard & Mouse Combo', 'unit' => 'Pcs', 'price' => 1800.00, 'moq' => '5 Units', 'lead_time' => 3],
                    ['name' => 'Privacy Screen Filter 24-inch', 'unit' => 'Pcs', 'price' => 1250.00, 'moq' => '5 Units', 'lead_time' => 2],
                ],
                'purchase_history' => [],
                'contract_history' => [
                    ['date' => '2025-01-01', 'action' => 'Contract Signed', 'performed_by' => 'Mark Tee', 'remarks' => 'Hardware supply contract signed'],
                ],
            ],
            [
                'slug' => 'officemax-ph',
                'name' => 'OfficeMax PH',
                'supplier_code' => 'AGR-00102',
                'since' => '2025-01-01',
                'description' => 'OfficeMax PH supplies office stationery, furniture, and workplace accessories.',
                'business_type' => 'Retailer',
                'address' => '456 Office Way, Quezon City, Philippines',
                'phone' => '09179876543',
                'email' => 'orders@officemax.example',
                'location' => 'Quezon City',
                'rating' => 4.2,
                'status' => 'Active',
                'last_transaction' => '2026-06-10',
                'contact_name' => 'Sarah Lim',
                'contact_role' => 'Sales Executive',
                'total_orders' => 45,
                'total_spent' => 180200.00,
                'avg_order_value' => 4004.00,
                'on_time_rate' => 92,
                'contract_start' => '2025-01-01',
                'contract_end' => '2026-12-31',
                'contract_duration' => '2 Years',
                'payment_terms' => 'Net 30',
                'auto_renewal' => true,
                'contract_document' => 'OfficeMax_Stationery_Contract.pdf',
                'contract_document_size' => '1.8 MB',
                'contract_scope' => [
                    'Delivery of standard office supplies and consumables',
                    'Supply and assembly of office furniture',
                ],
                'avg_rating_delta' => 'No change',
                'on_time_delta' => '↓ 1% from last month',
                'quality_score' => 4.3,
                'quality_delta' => '↑ 0.1 from last month',
                'total_orders_delta' => '↑ 5 this month',
                'products' => [
                    ['name' => 'Premium A4 Paper Reams', 'unit' => 'Box', 'price' => 210.00, 'moq' => '10 Boxes', 'lead_time' => 2],
                    ['name' => 'Ergonomic Office Chair', 'unit' => 'Pcs', 'price' => 6500.00, 'moq' => '2 Units', 'lead_time' => 5],
                    ['name' => 'LED Desk Lamp with USB Charger', 'unit' => 'Pcs', 'price' => 1450.00, 'moq' => '5 Units', 'lead_time' => 3],
                    ['name' => 'Whiteboard Magnetic 4x3ft', 'unit' => 'Pcs', 'price' => 3200.00, 'moq' => '1 Unit', 'lead_time' => 4],
                    ['name' => 'Dry Erase Markers (Pack of 12)', 'unit' => 'Pcs', 'price' => 380.00, 'moq' => '5 Units', 'lead_time' => 2],
                ],
                'purchase_history' => [],
                'contract_history' => [
                    ['date' => '2025-01-01', 'action' => 'Contract Signed', 'performed_by' => 'Sarah Lim', 'remarks' => 'Office supplies agreement signed'],
                ],
            ],
            [
                'slug' => 'green-harvest-co',
                'name' => 'Green Harvest Co.',
                'supplier_code' => 'AGR-00103',
                'since' => '2025-01-01',
                'description' => 'Green Harvest Co. provides organic fertilizers, seeds, and agricultural tools.',
                'business_type' => 'Cooperative',
                'address' => '789 Agrarian Rd, La Trinidad, Benguet, Philippines',
                'phone' => '09171112222',
                'email' => 'procure@greenharvest.example',
                'location' => 'Benguet',
                'rating' => 4.5,
                'status' => 'Active',
                'last_transaction' => '2026-06-15',
                'contact_name' => 'Albert Perez',
                'contact_role' => 'Procurement Officer',
                'total_orders' => 28,
                'total_spent' => 142000.00,
                'avg_order_value' => 5071.00,
                'on_time_rate' => 95,
                'contract_start' => '2025-01-01',
                'contract_end' => '2026-12-31',
                'contract_duration' => '2 Years',
                'payment_terms' => 'Net 15',
                'auto_renewal' => true,
                'contract_document' => 'GreenHarvest_Fertilizers_MOU.pdf',
                'contract_document_size' => '3.1 MB',
                'contract_scope' => [
                    'Supply of high-grade organic fertilizer bags',
                    'Supply of potting soil and starter seeds',
                ],
                'avg_rating_delta' => '↑ 0.2 from last month',
                'on_time_delta' => '↑ 3% from last month',
                'quality_score' => 4.6,
                'quality_delta' => '↑ 0.1 from last month',
                'total_orders_delta' => '↑ 3 this month',
                'products' => [
                    ['name' => 'Organic Fertilizer Bags', 'unit' => 'Sack', 'price' => 950.00, 'moq' => '10 Sacks', 'lead_time' => 3],
                    ['name' => 'Organic Potting Soil (20kg Bag)', 'unit' => 'Sack', 'price' => 250.00, 'moq' => '20 Sacks', 'lead_time' => 3],
                    ['name' => 'Hybrid Tomato Seeds (Pack of 50)', 'unit' => 'Pcs', 'price' => 120.00, 'moq' => '50 Pcs', 'lead_time' => 2],
                    ['name' => 'Drip Irrigation Emitter (Pack of 100)', 'unit' => 'Pcs', 'price' => 600.00, 'moq' => '5 Packs', 'lead_time' => 4],
                    ['name' => 'Pruning Shears Heavy Duty', 'unit' => 'Pcs', 'price' => 450.00, 'moq' => '5 Pcs', 'lead_time' => 3],
                    ['name' => 'Garden Hose Reel Cart', 'unit' => 'Pcs', 'price' => 2200.00, 'moq' => '2 Pcs', 'lead_time' => 4],
                    ['name' => 'Biodegradable Seed Starter Trays', 'unit' => 'Pcs', 'price' => 95.00, 'moq' => '20 Pcs', 'lead_time' => 2],
                    ['name' => 'Nylon Plant Trellis Netting', 'unit' => 'Pcs', 'price' => 180.00, 'moq' => '10 Pcs', 'lead_time' => 3],
                    ['name' => 'Liquid Seaweed Fertilizer (5L)', 'unit' => 'Sack', 'price' => 850.00, 'moq' => '10 Sacks', 'lead_time' => 3],
                    ['name' => 'Heavy Duty Gardening Gloves (M)', 'unit' => 'Pcs', 'price' => 140.00, 'moq' => '20 Pcs', 'lead_time' => 2],
                    ['name' => 'pH Soil Tester Moisture Meter', 'unit' => 'Pcs', 'price' => 650.00, 'moq' => '5 Pcs', 'lead_time' => 3],
                ],
                'purchase_history' => [],
                'contract_history' => [
                    ['date' => '2025-01-01', 'action' => 'Contract Signed', 'performed_by' => 'Albert Perez', 'remarks' => 'Fertilizer distribution agreement signed'],
                ],
            ],
            // New agricultural suppliers from the new Supplier Management module
            [
                'slug' => 'abc-farms',
                'name' => 'ABC Farms',
                'supplier_code' => 'AGR-00125',
                'since' => '2025-01-15',
                'description' => 'ABC Farms is a trusted agricultural supplier providing high quality farm products.',
                'business_type' => 'Farm',
                'address' => 'Barangay San Isidro, Cabanatuan City Nueva Ecija, Philippines',
                'phone' => '+63 917 123 4567',
                'email' => 'abcfarms@gmail.com',
                'location' => 'Nueva Ecija',
                'rating' => 4.5,
                'status' => 'Active',
                'last_transaction' => '2026-06-18',
                'contact_name' => 'Juan Dela Cruz',
                'contact_role' => 'Farm Manager',
                'total_orders' => 2245,
                'total_spent' => 523450.00,
                'avg_order_value' => 11632.00,
                'on_time_rate' => 93,
                'contract_start' => '2026-01-15',
                'contract_end' => '2027-01-15',
                'contract_duration' => '1 Year',
                'payment_terms' => 'Net 30',
                'auto_renewal' => true,
                'contract_document' => 'Supply Agreement - ABC Farms.pdf',
                'contract_document_size' => '1.2 MB',
                'contract_scope' => [
                    'Supply of fresh vegetables, rice and agricultural products',
                    'Ensure product quality and timely delivery',
                    'Compliance with company standards and policies',
                    'Maintain proper packaging and documentation',
                ],
                'avg_rating_delta' => '↑ 0.2 from last month',
                'on_time_delta' => '↑ 5% from last month',
                'quality_score' => 4.6,
                'quality_delta' => '↑ 5% from last month',
                'total_orders_delta' => '↑ 79 this month',
                'products' => [
                    ['name' => 'Rice', 'unit' => 'Sack', 'price' => 2450.00, 'moq' => '10 Sacks', 'lead_time' => 3],
                    ['name' => 'Corn', 'unit' => 'Sack', 'price' => 1850.00, 'moq' => '10 Sacks', 'lead_time' => 3],
                    ['name' => 'Onion', 'unit' => 'Sack', 'price' => 1500.00, 'moq' => '5 Sacks', 'lead_time' => 2],
                ],
                'purchase_history' => [
                    ['date' => '2026-06-18', 'po_number' => 'PO-1024', 'product' => 'Rice', 'quantity' => 120, 'amount' => 245000.00, 'status' => 'Delivered'],
                    ['date' => '2026-05-22', 'po_number' => 'PO-0998', 'product' => 'Corn', 'quantity' => 80, 'amount' => 148000.00, 'status' => 'Completed'],
                ],
                'contract_history' => [
                    ['date' => '2025-01-01', 'action' => 'Contract Created', 'performed_by' => 'Juan Dela Cruz', 'remarks' => 'Initial Contract creation'],
                    ['date' => '2025-01-15', 'action' => 'Contract Signed', 'performed_by' => 'ABC Farms', 'remarks' => 'Contract signed by company'],
                    ['date' => '2026-01-15', 'action' => 'Contract Updated', 'performed_by' => 'Juan Dela Cruz', 'remarks' => 'Renewal of Contract'],
                ],
            ],
            [
                'slug' => 'green-harvest',
                'name' => 'Green Harvest',
                'supplier_code' => 'AGR-00117',
                'since' => '2025-02-03',
                'description' => 'Green Harvest supplies fresh vegetables sourced from Benguet highland farms.',
                'business_type' => 'Cooperative',
                'address' => 'La Trinidad, Benguet, Philippines',
                'phone' => '+63 918 222 3344',
                'email' => 'greenharvest@gmail.com',
                'location' => 'Benguet',
                'rating' => 3.5,
                'status' => 'Blacklisted',
                'last_transaction' => '2026-06-17',
                'contact_name' => 'Maria Santos',
                'contact_role' => 'Cooperative Head',
                'total_orders' => 309,
                'total_spent' => 210300.00,
                'avg_order_value' => 6805.00,
                'on_time_rate' => 78,
                'contract_start' => '2025-02-03',
                'contract_end' => '2026-02-03',
                'contract_duration' => '1 Year',
                'payment_terms' => 'Net 15',
                'auto_renewal' => false,
                'contract_document' => 'Supply Agreement - Green Harvest.pdf',
                'contract_document_size' => '980 KB',
                'contract_scope' => [
                    'Supply of fresh vegetables',
                    'Compliance with quality standards',
                ],
                'avg_rating_delta' => '↓ 0.3 from last month',
                'on_time_delta' => '↓ 4% from last month',
                'quality_score' => 3.0,
                'quality_delta' => '↓ 1.1 from last month',
                'total_orders_delta' => '↑ 5 this month',
                'blacklist_reason' => 'Failed Quality Inspection',
                'blacklisted_since' => '2026-01-12',
                'risk_level' => 'High',
                'products' => [
                    ['name' => 'Lettuce', 'unit' => 'Crate', 'price' => 950.00, 'moq' => '5 Crates', 'lead_time' => 3],
                    ['name' => 'Cabbage', 'unit' => 'Sack', 'price' => 1100.00, 'moq' => '5 Sacks', 'lead_time' => 3],
                ],
                'purchase_history' => [
                    ['date' => '2026-06-17', 'po_number' => 'PO-1017', 'product' => 'Lettuce', 'quantity' => 60, 'amount' => 57000.00, 'status' => 'Pending'],
                    ['date' => '2026-04-08', 'po_number' => 'PO-0921', 'product' => 'Cabbage', 'quantity' => 45, 'amount' => 49500.00, 'status' => 'Completed'],
                ],
                'contract_history' => [
                    ['date' => '2025-02-03', 'action' => 'Contract Created', 'performed_by' => 'Maria Santos', 'remarks' => 'Initial Contract creation'],
                    ['date' => '2026-01-12', 'action' => 'Supplier Blacklisted', 'performed_by' => 'Admin', 'remarks' => 'Failed Quality Inspection'],
                ],
            ],
            [
                'slug' => 'fresh-mango-co',
                'name' => 'Fresh Mango Co.',
                'supplier_code' => 'AGR-00133',
                'since' => '2025-03-20',
                'description' => 'Fresh Mango Co. supplies premium mangoes sourced from Guimaras orchards.',
                'business_type' => 'Farm',
                'address' => 'Buenavista, Guimaras, Philippines',
                'phone' => '+63 919 555 7788',
                'email' => 'freshmango@gmail.com',
                'location' => 'Guimaras',
                'rating' => 4.0,
                'status' => 'Active',
                'last_transaction' => '2026-06-14',
                'contact_name' => 'Ana Reyes',
                'contact_role' => 'Farm Owner',
                'total_orders' => 578,
                'total_spent' => 780900.00,
                'avg_order_value' => 13510.00,
                'on_time_rate' => 95,
                'contract_start' => '2025-03-20',
                'contract_end' => '2027-03-20',
                'contract_duration' => '2 Years',
                'payment_terms' => 'Net 30',
                'auto_renewal' => true,
                'contract_document' => 'Supply Agreement - Fresh Mango Co.pdf',
                'contract_document_size' => '1.4 MB',
                'contract_scope' => [
                    'Supply of fresh mangoes',
                    'Ensure ripeness grading and timely delivery',
                ],
                'avg_rating_delta' => '↑ 0.1 from last month',
                'on_time_delta' => '↑ 2% from last month',
                'quality_score' => 4.2,
                'quality_delta' => '↑ 0.2 from last month',
                'total_orders_delta' => '↑ 12 this month',
                'products' => [
                    ['name' => 'Mango', 'unit' => 'Box', 'price' => 2100.00, 'moq' => '5 Boxes', 'lead_time' => 2],
                ],
                'purchase_history' => [
                    ['date' => '2026-06-14', 'po_number' => 'PO-1004', 'product' => 'Mango', 'quantity' => 80, 'amount' => 168000.00, 'status' => 'Delivered'],
                    ['date' => '2026-05-30', 'po_number' => 'PO-0978', 'product' => 'Mango', 'quantity' => 40, 'amount' => 84000.00, 'status' => 'Completed'],
                ],
                'contract_history' => [
                    ['date' => '2025-03-20', 'action' => 'Contract Created', 'performed_by' => 'Ana Reyes', 'remarks' => 'Initial Contract creation'],
                ],
            ],
            [
                'slug' => 'coconut-valley',
                'name' => 'Coconut Valley',
                'supplier_code' => 'AGR-00109',
                'since' => '2025-04-05',
                'description' => 'Coconut Valley is a leading coconut supplier from Quezon province.',
                'business_type' => 'Farm',
                'address' => 'Lucena City, Quezon, Philippines',
                'phone' => '+63 920 111 9900',
                'email' => 'coconutvalley@gmail.com',
                'location' => 'Quezon',
                'rating' => 4.5,
                'status' => 'Active',
                'last_transaction' => '2026-06-10',
                'contact_name' => 'Pedro Ramos',
                'contact_role' => 'Operations Manager',
                'total_orders' => 1899,
                'total_spent' => 1230600.00,
                'avg_order_value' => 9750.00,
                'on_time_rate' => 90,
                'contract_start' => '2025-04-05',
                'contract_end' => '2027-04-05',
                'contract_duration' => '2 Years',
                'payment_terms' => 'Net 30',
                'auto_renewal' => true,
                'contract_document' => 'Supply Agreement - Coconut Valley.pdf',
                'contract_document_size' => '1.1 MB',
                'contract_scope' => [
                    'Supply of coconuts',
                    'Maintain proper packaging and documentation',
                ],
                'avg_rating_delta' => '↑ 0.3 from last month',
                'on_time_delta' => '↑ 1% from last month',
                'quality_score' => 4.4,
                'quality_delta' => '↑ 0.1 from last month',
                'total_orders_delta' => '↑ 40 this month',
                'products' => [
                    ['name' => 'Coconut', 'unit' => 'Sack', 'price' => 1200.00, 'moq' => '10 Sacks', 'lead_time' => 3],
                ],
                'purchase_history' => [
                    ['date' => '2026-06-10', 'po_number' => 'PO-0987', 'product' => 'Coconut', 'quantity' => 100, 'amount' => 120000.00, 'status' => 'Delivered'],
                    ['date' => '2026-05-14', 'po_number' => 'PO-0945', 'product' => 'Coconut', 'quantity' => 70, 'amount' => 84000.00, 'status' => 'Completed'],
                ],
                'contract_history' => [
                    ['date' => '2025-04-05', 'action' => 'Contract Created', 'performed_by' => 'Pedro Ramos', 'remarks' => 'Initial Contract creation'],
                ],
            ],
        ];

        // Dynamically add sample suppliers up to 125 records (Requirement 6)
        $initialCount = count($suppliers);
        $locations = ['Manila', 'Quezon City', 'Makati City', 'Davao City', 'Cebu City', 'Taguig City', 'Pasig City', 'Iloilo City', 'Cagayan de Oro', 'Baguio City'];
        $businessTypes = ['Corporation', 'Cooperative', 'Partnership', 'Sole Proprietorship'];
        $companyPrefixes = ['Agri', 'Green', 'Harvest', 'Bio', 'Eco', 'Terra', 'Farm', 'Crop', 'Flora', 'Verdant', 'Golden', 'Pacific', 'Apex', 'Global', 'Sun', 'Valley'];
        $companySuffixes = ['Supplies Co.', 'Agri-Ventures', 'Farming Systems', 'Trading Inc.', 'Products Ltd.', 'Solutions', 'Holdings', 'Enterprises', 'Produce Corp.', 'Groceries'];
        $reasons = [
            'Fraudulent transactions',
            'Repeated late deliveries',
            'Poor product quality',
            'Contract violation',
            'Expired Certification',
            'Non-compliance with safety standards',
            'Substandard crop yields'
        ];
        $risks = ['Critical', 'High', 'Medium', 'Low'];

        for ($i = $initialCount + 1; $i <= 125; $i++) {
            $pFix = $companyPrefixes[($i * 3) % count($companyPrefixes)];
            $sFix = $companySuffixes[($i * 5) % count($companySuffixes)];
            $name = "{$pFix} {$sFix} {$i}";
            $code = 'AGR-' . str_pad((string) ($i + 200), 5, '0', STR_PAD_LEFT);
            $slug = Str::slug($name);

            if ($i > 115) {
                $status = 'Blacklisted';
                $reason = $reasons[$i % count($reasons)];
                $risk = $risks[$i % count($risks)];
            } else {
                $status = 'Active';
                $reason = null;
                $risk = 'Low';
            }

            $suppliers[] = [
                'slug' => $slug,
                'name' => $name,
                'supplier_code' => $code,
                'since' => now()->subDays($i * 3)->format('Y-m-d'),
                'description' => $name . ' is an authorized supplier of agricultural produce and goods.',
                'business_type' => $businessTypes[$i % count($businessTypes)],
                'address' => ($i * 12) . ' Agri Way, ' . $locations[$i % count($locations)] . ', Philippines',
                'phone' => '0917' . str_pad((string)$i, 7, '0', STR_PAD_LEFT),
                'email' => 'contact@' . Str::slug($name) . '.com',
                'location' => $locations[$i % count($locations)],
                'rating' => round(3.5 + (($i % 15) / 10), 1),
                'status' => $status,
                'blacklist_reason' => $reason,
                'blacklisted_since' => $status === 'Blacklisted' ? now()->subDays($i)->format('Y-m-d') : null,
                'risk_level' => $risk,
                'last_transaction' => now()->subDays($i)->format('Y-m-d'),
                'contact_name' => 'Representative ' . $i,
                'contact_role' => 'Account Manager',
                'total_orders' => ($i % 20) + 1,
                'total_spent' => ($i * 1500) + 5000.00,
                'avg_order_value' => (($i * 1500) + 5000.00) / max(($i % 20) + 1, 1),
                'on_time_rate' => 85 + ($i % 15),
                'contract_start' => '2025-01-01',
                'contract_end' => '2026-12-31',
                'contract_duration' => '1 Year',
                'payment_terms' => 'Net 30',
                'auto_renewal' => true,
                'contract_document' => $slug . '_Agreement.pdf',
                'contract_document_size' => '1.2 MB',
                'contract_scope' => ['Supply of agricultural products'],
                'avg_rating_delta' => 'No change',
                'on_time_delta' => 'No change',
                'quality_score' => 4.5,
                'quality_delta' => 'No change',
                'total_orders_delta' => 'No change',
                'products' => [
                    ['name' => 'Rice', 'unit' => 'Sack', 'price' => 2450.00, 'moq' => '10 Sacks', 'lead_time' => 3],
                ],
                'purchase_history' => [],
                'contract_history' => [],
            ];
        }

        foreach ($suppliers as $sData) {
            $products = $sData['products'] ?? [];
            $purchaseHistory = $sData['purchase_history'] ?? [];
            $contractHistory = $sData['contract_history'] ?? [];
            unset($sData['products'], $sData['purchase_history'], $sData['contract_history']);

            // 1. Create Address
            $address = Address::create([
                'street' => $sData['address'],
                'city' => $sData['location'],
                'province' => $sData['location'],
                'zipcode' => '0000',
                'country' => 'Philippines'
            ]);

            // 2. Map Supplier
            $sData['supplier_name'] = $sData['name'];
            $sData['supplier_id'] = $sData['supplier_code'];
            $sData['address_id'] = $address->id;
            // Retain name for database column mapping
            $sData['name'] = $sData['name'];
            unset($sData['address']);

            $supplier = Supplier::updateOrCreate(['slug' => $sData['slug']], $sData);

            // 3. Link Products
            $supplier->productsRelation()->detach();
            foreach ($products as $prod) {
                $pModel = $prodModels[$prod['name']];
                $supplier->productsRelation()->attach($pModel->id, [
                    'supplier_sku' => $supplier->supplier_code . '-' . strtoupper(substr($prod['name'], 0, 3)),
                    'unit_price' => $prod['price'],
                    'lead_time_days' => $prod['lead_time'],
                    'is_preferred' => true,
                ]);
            }

            // 4. Create Purchase Orders & PO Items
            $supplier->purchaseOrders()->delete();
            foreach ($purchaseHistory as $ph) {
                $pModel = $prodModels[$ph['product']];
                
                $term = ($supplier->payment_terms === 'Net 15') ? $net15 : $net30;

                $po = PurchaseOrder::create([
                    'po_number' => $ph['po_number'],
                    'supplier_id' => $supplier->id,
                    'requisition_id' => null,
                    'payment_term_id' => $term->id,
                    'currency_id' => $phpCurrency->id,
                    'status' => $ph['status'],
                    'order_date' => \Illuminate\Support\Carbon::parse($ph['date'] ?? now()), // parser fallback
                    'created_by' => $user->id,
                ]);

                POItem::create([
                    'po_id' => $po->id,
                    'product_id' => $pModel->id,
                    'quantity' => $ph['quantity'],
                    'uom_id' => $pModel->uom_id,
                    'unit_price' => $pModel->base_price,
                ]);
            }

            // 5. Create Contract History
            $supplier->contractHistoryEntries()->delete();
            foreach ($contractHistory as $entry) {
                $supplier->contractHistoryEntries()->create([
                    'date' => \Illuminate\Support\Carbon::parse($entry['date']),
                    'action' => $entry['action'],
                    'performed_by' => $entry['performed_by'],
                    'remarks' => $entry['remarks'],
                ]);
            }
        }

        // Standalone Blacklist entries
        $standaloneBlacklist = [
            ['name' => 'Sunrise Corp.', 'supplier_code' => 'ARG-05843', 'reason' => 'Expired Certification', 'blacklisted_since' => '2026-01-12', 'risk_level' => 'Low'],
            ['name' => 'XYZ', 'supplier_code' => 'AGR-02457', 'reason' => 'Fraudulent Contract', 'blacklisted_since' => '2026-01-12', 'risk_level' => 'Critical'],
            ['name' => 'Sunrise Corp.', 'supplier_code' => 'AGR-05487', 'reason' => 'Non-compliance', 'blacklisted_since' => '2026-01-12', 'risk_level' => 'Medium'],
        ];

        foreach ($standaloneBlacklist as $entry) {
            BlacklistedSupplier::updateOrCreate(
                ['supplier_code' => $entry['supplier_code']],
                $entry
            );
        }
    }
}
