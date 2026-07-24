<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ErDiagramController extends Controller
{
    public function index()
    {
        $entities = [
            'addresses' => [
                'name' => 'Addresses',
                'model' => \App\Models\Address::class,
                'description' => 'Physical mailing and billing addresses for suppliers.',
            ],
            'suppliers' => [
                'name' => 'Suppliers',
                'model' => \App\Models\Supplier::class,
                'description' => 'Supplier vendor accounts, contact details, and contract status.',
            ],
            'products' => [
                'name' => 'Products',
                'model' => \App\Models\Product::class,
                'description' => 'Purchasable inventory items, stock levels, and base prices.',
            ],
            'product_suppliers' => [
                'name' => 'Product Suppliers (Junction)',
                'table' => 'product_suppliers',
                'description' => 'Junction table connecting products to their respective suppliers with custom prices.',
            ],
            'purchase_orders' => [
                'name' => 'Purchase Orders',
                'model' => \App\Models\PurchaseOrder::class,
                'description' => 'Purchase orders raised to vendor suppliers containing tracking status.',
            ],
            'po_items' => [
                'name' => 'PO Line Items',
                'model' => \App\Models\POItem::class,
                'description' => 'Individual line items ordered within a specific purchase order.',
            ],
            'supplier_invoices' => [
                'name' => 'Supplier Invoices',
                'model' => \App\Models\SupplierInvoice::class,
                'description' => 'Ledger invoices received from suppliers to reconcile against POs.',
            ],
            'payment_terms' => [
                'name' => 'Payment Terms',
                'model' => \App\Models\PaymentTerm::class,
                'description' => 'Agreed payment schedules, net days, and discount percentages.',
            ],
            'currencies' => [
                'name' => 'Currencies',
                'model' => \App\Models\Currency::class,
                'description' => 'System-supported transaction currencies with exchange rates.',
            ],
            'units_of_measure' => [
                'name' => 'Units of Measure',
                'model' => \App\Models\UnitOfMeasure::class,
                'description' => 'Packaging units (e.g. Piece, Box, Kilogram) used for products.',
            ],
        ];

        $schemaData = [];

        foreach ($entities as $key => $config) {
            $tableName = isset($config['model']) ? (new $config['model'])->getTable() : $config['table'];
            
            // Get row count
            $count = DB::table($tableName)->count();
            
            // Get columns and types
            $columns = [];
            foreach (Schema::getColumnListing($tableName) as $col) {
                $columns[] = [
                    'name' => $col,
                    'type' => Schema::getColumnType($tableName, $col),
                ];
            }

            // Get sample data (5 rows)
            $samples = DB::table($tableName)->limit(5)->get()->map(function($row) {
                return (array)$row;
            })->toArray();

            $schemaData[$key] = [
                'name' => $config['name'],
                'table' => $tableName,
                'description' => $config['description'],
                'count' => $count,
                'columns' => $columns,
                'samples' => $samples,
            ];
        }

        return view('schema.erd-schema', compact('schemaData'));
    }
}
