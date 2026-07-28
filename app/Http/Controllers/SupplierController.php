<?php

namespace App\Http\Controllers;

use App\Models\BlacklistedSupplier;
use App\Models\Supplier;
use App\Models\Address;
use App\Models\Product;
use App\Models\Category;
use App\Models\UnitOfMeasure;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    protected function stats(): array
    {
        Supplier::whereIn('status', ['Pending', 'Pending Verification', 'pending', 'pending_verification', 'Awaiting Review'])->update(['status' => 'Active']);

        $total = Supplier::count();
        $active = Supplier::whereIn('status', ['Active', 'Verified', 'active', 'verified'])->count();

        return [
            'total' => $total,
            'active' => $active,
            'pending' => 0,
            'blacklisted' => Supplier::where('status', 'Blacklisted')->count()
                + BlacklistedSupplier::count(),
        ];
    }

    /**
     * Combines suppliers with status=Blacklisted with standalone blacklist-only entries.
     */
    protected function blacklisted($riskFilter = null)
    {
        $fromSuppliersQuery = Supplier::where('status', 'Blacklisted');
        if ($riskFilter && $riskFilter !== 'All Risk Levels') {
            $fromSuppliersQuery->where('risk_level', $riskFilter);
        }
        $fromSuppliers = $fromSuppliersQuery->get()->map(fn ($s) => [
            'supplier' => $s->supplier_name,
            'slug' => $s->slug,
            'supplier_id' => $s->supplier_id,
            'reason' => $s->blacklist_reason,
            'since' => $s->blacklisted_since ? $s->blacklisted_since->format('M. d, Y') : null,
            'risk' => $s->risk_level ?? 'High',
        ]);

        $standaloneQuery = BlacklistedSupplier::query();
        if ($riskFilter && $riskFilter !== 'All Risk Levels') {
            $standaloneQuery->where('risk_level', $riskFilter);
        }
        $standalone = $standaloneQuery->get()->map(fn ($b) => [
            'supplier' => $b->name,
            'slug' => null,
            'supplier_id' => $b->supplier_code,
            'reason' => $b->reason,
            'since' => $b->since,
            'risk' => $b->risk_level ?? 'Critical',
        ]);

        $combined = $fromSuppliers->concat($standalone)->values();

        if ($riskFilter && $riskFilter !== 'All Risk Levels') {
            $combined = $combined->filter(fn ($item) => strtolower($item['risk'] ?? '') === strtolower($riskFilter))->values();
        }

        return $combined;
    }

    // Supplier Management dashboard
    public function dashboard()
    {
        $topSuppliersData = Supplier::whereIn('status', ['Active', 'Verified', 'active', 'verified'])
            ->withCount('purchaseOrders')
            ->having('purchase_orders_count', '>', 0)
            ->orderByDesc('purchase_orders_count')
            ->take(10)
            ->get();

        $maxOrders = $topSuppliersData->max('purchase_orders_count') ?? 0;

        $topSuppliers = $topSuppliersData->map(function ($supplier) use ($maxOrders) {
            $poCount = (int) $supplier->purchase_orders_count;
            $percentage = $maxOrders > 0 ? round(($poCount / $maxOrders) * 100, 1) : 0;
            return [
                'id' => $supplier->id,
                'name' => $supplier->supplier_name ?: $supplier->name,
                'orders_count' => $poCount,
                'progress_percentage' => $percentage,
            ];
        })->toArray();

        $suppliers = Supplier::latest()->take(10)->get()->toArray();

        return view('suppliers.dashboard', [
            'suppliers' => $suppliers,
            'topSuppliers' => $topSuppliers,
            'stats' => $this->stats(),
        ]);
    }

    // Suppliers list with pagination and search
    public function index(Request $request)
    {
        $keyword = (string) $request->query('q', '');
        $keyword = trim($keyword);

        $query = Supplier::query();

        if ($keyword !== '') {
            $pattern = "%{$keyword}%";

            $query->where(function ($q) use ($pattern) {
                $q->where('supplier_name', 'like', $pattern)
                  ->orWhere('location', 'like', $pattern)
                  ->orWhere('supplier_code', 'like', $pattern)
                  ->orWhere('supplier_id', 'like', $pattern)
                  ->orWhereHas('productsRelation', function ($p) use ($pattern) {
                      $p->where('name', 'like', $pattern);
                  });
            });
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        return view('suppliers.index', [
            'suppliers' => $suppliers,
        ]);
    }

    public function activeIndex(Request $request)
    {
        Supplier::whereIn('status', ['Pending', 'Pending Verification', 'pending', 'pending_verification', 'Awaiting Review'])->update(['status' => 'Active']);

        $keyword = (string) $request->query('q', '');
        $keyword = trim($keyword);

        $query = Supplier::whereIn('status', ['Active', 'Verified', 'active', 'verified']);

        if ($keyword !== '') {
            $pattern = "%{$keyword}%";
            $query->where(function ($q) use ($pattern) {
                $q->where('supplier_name', 'like', $pattern)
                  ->orWhere('location', 'like', $pattern)
                  ->orWhere('supplier_code', 'like', $pattern)
                  ->orWhere('supplier_id', 'like', $pattern);
            });
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        return view('suppliers.active', [
            'suppliers' => $suppliers,
        ]);
    }

    public function pendingIndex(Request $request)
    {
        return redirect()->route('suppliers.index');
    }

    // Add new supplier form
    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'contact_email' => ['required', 'email', 'max:255'],
            'lead_time' => ['required', 'string'],
            'delivery_schedule' => ['required', 'string'],
            'moq' => ['required', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'payment_terms' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $locationValue = trim($data['location'] ?? $data['address']);

        $baseSlug = Str::slug($data['company_name']);
        $slug = $baseSlug;
        $i = 1;
        while (Supplier::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        try {
            $supplierAddress = Address::create([
                'street' => $data['address'],
                'city' => $locationValue,
                'province' => $locationValue,
                'zipcode' => '1000',
                'country' => 'Philippines',
            ]);

            $supplier = Supplier::create([
                'slug' => $slug,
                'supplier_name' => $data['company_name'],
                'name' => $data['company_name'],
                'supplier_id' => 'AGR-' . str_pad((string) (Supplier::count() + 1), 5, '0', STR_PAD_LEFT),
                'business_type' => $data['business_type'],
                'address_id' => $supplierAddress->id,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'contact_name' => $data['contact_person'],
                'contact_role' => $data['position'],
                'contact_phone' => $data['contact_phone'],
                'contact_email' => $data['contact_email'],
                'lead_time' => $data['lead_time'],
                'delivery_schedule' => $data['delivery_schedule'],
                'moq' => $data['moq'],
                'payment_terms' => $data['payment_terms'],
                'payment_method' => $data['payment_method'],
                'description' => $data['description'] ?? null,
                'status' => 'Active',
                'since' => now(),
                'location' => $locationValue,
            ]);

            foreach ($data['products'] as $prodName) {
                $product = Product::where('name', $prodName)->first();
                if (!$product) {
                    $grains = Category::firstOrCreate(['category_name' => 'Grains']);
                    $uom = UnitOfMeasure::firstOrCreate(['uom_code' => 'Sack'], ['uom_name' => '50kg Sack']);
                    $currency = Currency::firstOrCreate(['currency_code' => 'PHP'], ['currency_name' => 'Philippine Peso', 'exchange_rate' => 1.0]);
                    
                    $product = Product::create([
                        'sku' => 'PRD-' . strtoupper(Str::slug($prodName)),
                        'name' => $prodName,
                        'description' => $prodName,
                        'category_id' => $grains->id,
                        'uom_id' => $uom->id,
                        'currency_id' => $currency->id,
                        'base_price' => 1000.00,
                        'min_quantity_threshold' => 10.00,
                        'lead_time_days' => 3,
                    ]);
                }

                $supplier->productsRelation()->attach($product->id, [
                    'supplier_sku' => $supplier->supplier_id . '-' . strtoupper(substr($prodName, 0, 3)),
                    'unit_price' => $product->base_price,
                    'lead_time_days' => $product->lead_time_days,
                    'is_preferred' => true,
                ]);
            }

        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->withErrors(['db' => 'Unable to save supplier: ' . $e->getMessage()]);
        }

        if ($supplier && $supplier->id) {
            return redirect()->route('suppliers.index')->with('status', 'Supplier saved successfully.');
        }

        return redirect()->back()->withInput()->withErrors(['db' => 'Unknown error creating supplier.']);
    }

    // Supplier overview
    public function show(string $supplier)
    {
        $s = Supplier::with(['productsRelation', 'purchaseOrders', 'contractHistoryEntries', 'addressRelation'])
            ->where('slug', $supplier)->firstOrFail();

        $data = $s->toArray();
        $data['products'] = $s->products;
        $data['purchase_history'] = $s->purchase_history;
        $data['status'] = $s->status;
        $data['blacklist_reason'] = $s->blacklist_reason;
        $data['blacklisted_since'] = $s->blacklisted_since ? $s->blacklisted_since->format('M. d, Y') : null;

        return view('suppliers.show', [
            'supplier' => $data,
        ]);
    }

    // Product details
    public function products(string $supplier)
    {
        $s = Supplier::with('productsRelation')->where('slug', $supplier)->firstOrFail();

        $data = $s->toArray();
        $data['products'] = $s->products;

        return view('suppliers.products', [
            'supplier' => $data,
        ]);
    }

    // Purchase history
    public function purchaseHistory(string $supplier)
    {
        $s = Supplier::with('purchaseOrders')->where('slug', $supplier)->firstOrFail();

        $data = $s->toArray();
        $data['purchase_history'] = $s->purchase_history;

        return view('suppliers.purchase-history', [
            'supplier' => $data,
        ]);
    }

    // Contract information
    public function contract(string $supplier)
    {
        $s = Supplier::with('contractHistoryEntries')->where('slug', $supplier)->firstOrFail();

        return view('suppliers.contract', [
            'supplier' => $s->toArray(),
        ]);
    }

    // Performance
    public function performance(string $supplier)
    {
        $s = Supplier::with(['productsRelation', 'purchaseOrders'])->where('slug', $supplier)->firstOrFail();

        $data = $s->toArray();
        $data['products'] = $s->products;
        $data['purchase_history'] = $s->purchase_history;

        return view('suppliers.performance', [
            'supplier' => $data,
        ]);
    }

    // Blacklisted suppliers with Risk Level filter and search
    public function blacklistedIndex(Request $request)
    {
        $risk = $request->query('risk', 'All Risk Levels');
        $keyword = trim((string) $request->query('q', ''));

        $items = $this->blacklisted($risk);

        if ($keyword !== '') {
            $pattern = strtolower($keyword);
            $items = $items->filter(function ($b) use ($pattern) {
                return str_contains(strtolower($b['supplier'] ?? ''), $pattern) ||
                       str_contains(strtolower($b['supplier_id'] ?? ''), $pattern) ||
                       str_contains(strtolower($b['reason'] ?? ''), $pattern);
            })->values();
        }

        return view('suppliers.blacklisted', [
            'blacklisted' => $items,
            'currentRisk' => $risk,
            'stats' => $this->stats(),
        ]);
    }

    // Requirement 3: Block Supplier action requiring reason
    public function block(Request $request, string $supplier)
    {
        $request->validate([
            'blacklist_reason' => ['required', 'string', 'min:3'],
        ], [
            'blacklist_reason.required' => 'A reason for blocking is required before blocking a supplier.',
        ]);

        $s = Supplier::where('slug', $supplier)->firstOrFail();

        $s->update([
            'status' => 'Blacklisted',
            'blacklist_reason' => $request->input('blacklist_reason'),
            'blacklisted_since' => now(),
            'risk_level' => $request->input('risk_level', $s->risk_level ?? 'High'),
        ]);

        // Sync or create standalone BlacklistedSupplier entry
        BlacklistedSupplier::updateOrCreate(
            ['supplier_code' => $s->supplier_id ?: $s->supplier_code],
            [
                'name' => $s->supplier_name ?: $s->name,
                'reason' => $request->input('blacklist_reason'),
                'blacklisted_since' => now()->format('Y-m-d'),
                'risk_level' => $request->input('risk_level', $s->risk_level ?? 'High'),
            ]
        );

        return redirect()->back()->with('success', 'Supplier has been blocked and moved to blacklisted suppliers.');
    }

    // Requirement 2: Unblock Supplier action
    public function unblock(Request $request, string $supplier)
    {
        $s = Supplier::where('slug', $supplier)->firstOrFail();

        $s->update([
            'status' => 'Active',
            'blacklist_reason' => null,
            'blacklisted_since' => null,
        ]);

        BlacklistedSupplier::where('supplier_code', $s->supplier_id ?: $s->supplier_code)->delete();

        return redirect()->back()->with('success', 'Supplier has been unblocked and restored to Active status.');
    }
}
