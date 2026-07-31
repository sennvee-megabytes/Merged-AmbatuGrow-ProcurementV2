<?php

namespace App\Http\Controllers;

use App\Models\BlacklistedSupplier;
use App\Models\Supplier;
use App\Models\Address;
use App\Models\Product;
use App\Models\Category;
use App\Models\UnitOfMeasure;
use App\Models\Currency;
use App\Http\Requests\StoreSupplierRequest;
use Illuminate\Database\QueryException;
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
            ->has('purchaseOrders')
            ->withCount('purchaseOrders')
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

        // Calculate Product Supplied category breakdown dynamically
        $allSuppliers = Supplier::with(['productsRelation.category'])->get();

        $grainsCount = 0;
        $fruitsCount = 0;
        $vegCount = 0;
        $othersCount = 0;

        foreach ($allSuppliers as $s) {
            $categories = $s->productsRelation->map(function ($p) {
                return strtolower($p->category->category_name ?? '');
            })->toArray();

            $hasGrains = false;
            $hasFruits = false;
            $hasVeg = false;
            $hasOthers = false;

            foreach ($categories as $cat) {
                if (str_contains($cat, 'grain') || str_contains($cat, 'rice')) {
                    $hasGrains = true;
                } elseif (str_contains($cat, 'fruit')) {
                    $hasFruits = true;
                } elseif (str_contains($cat, 'veg')) {
                    $hasVeg = true;
                } else {
                    $hasOthers = true;
                }
            }

            if (empty($categories)) {
                $pList = strtolower($s->products_list ?? '');
                if (str_contains($pList, 'grain') || str_contains($pList, 'rice')) $hasGrains = true;
                if (str_contains($pList, 'fruit')) $hasFruits = true;
                if (str_contains($pList, 'veg')) $hasVeg = true;
                if (str_contains($pList, 'other') || (! $hasGrains && ! $hasFruits && ! $hasVeg && ! empty($pList))) $hasOthers = true;
            }

            if ($hasGrains) $grainsCount++;
            if ($hasFruits) $fruitsCount++;
            if ($hasVeg) $vegCount++;
            if ($hasOthers) $othersCount++;
        }

        $sumCounts = $grainsCount + $fruitsCount + $vegCount + $othersCount;

        if ($sumCounts > 0) {
            $grainsPct = (int) round(($grainsCount / $sumCounts) * 100);
            $fruitsPct = (int) round(($fruitsCount / $sumCounts) * 100);
            $vegPct = (int) round(($vegCount / $sumCounts) * 100);
            $othersPct = max(0, 100 - ($grainsPct + $fruitsPct + $vegPct));
        } else {
            $grainsPct = 40;
            $fruitsPct = 30;
            $vegPct = 20;
            $othersPct = 10;
        }

        $productSuppliedData = [
            ['label' => 'Grains',     'color' => '#059669', 'count' => $grainsCount, 'percentage' => $grainsPct],
            ['label' => 'Fruits',     'color' => '#FCD34D', 'count' => $fruitsCount, 'percentage' => $fruitsPct],
            ['label' => 'Vegetables', 'color' => '#6EE7B7', 'count' => $vegCount,    'percentage' => $vegPct],
            ['label' => 'Others',     'color' => '#D1D5DB', 'count' => $othersCount, 'percentage' => $othersPct],
        ];

        return view('suppliers.dashboard', [
            'suppliers' => $suppliers,
            'topSuppliers' => $topSuppliers,
            'stats' => $this->stats(),
            'productSuppliedData' => $productSuppliedData,
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

    public function store(StoreSupplierRequest $request)
    {
        $data = $request->validated();

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
                if ($prodName === 'Others' && !empty($request->input('specified_product'))) {
                    $prodName = trim($request->input('specified_product'));
                }

                $catName = in_array($prodName, ['Rice', 'Vegetables', 'Fruits', 'Grains']) ? $prodName : 'Others';
                $categoryObj = Category::firstOrCreate(['category_name' => $catName]);
                $uom = UnitOfMeasure::firstOrCreate(['uom_code' => 'Sack'], ['uom_name' => '50kg Sack']);
                $currency = Currency::firstOrCreate(['currency_code' => 'PHP'], ['currency_name' => 'Philippine Peso', 'exchange_rate' => 1.0]);

                $product = Product::create([
                    'sku' => 'PRD-' . strtoupper(Str::slug($prodName)) . '-' . Str::random(5),
                    'name' => $prodName,
                    'description' => $prodName,
                    'category_id' => $categoryObj->id,
                    'uom_id' => $uom->id,
                    'currency_id' => $currency->id,
                    'base_price' => 1000.00,
                    'min_quantity_threshold' => 10.00,
                    'lead_time_days' => 3,
                ]);

                $catSuffix = self::generateCategorySuffix($catName, $prodName);

                $supplier->productsRelation()->attach($product->id, [
                    'supplier_sku' => $supplier->supplier_id . '-' . $catSuffix,
                    'unit_price' => $product->base_price,
                    'lead_time_days' => $product->lead_time_days,
                    'is_preferred' => true,
                ]);
            }

        } catch (QueryException $e) {
            return redirect()->back()->withInput()->withErrors(['company_name' => 'A supplier with this company name or email address already exists.']);
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

    public static function getNextProductSequenceNumber(): int
    {
        $allSkus = \Illuminate\Support\Facades\DB::table('product_suppliers')
            ->pluck('supplier_sku')
            ->concat(\Illuminate\Support\Facades\DB::table('products')->pluck('sku'))
            ->filter();

        $maxNumber = 100;

        foreach ($allSkus as $sku) {
            if (preg_match('/(?:AGR|PRD)[\-_](\d{3,5})/i', $sku, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            } elseif (preg_match('/[\-_](\d{3,5})[\-_]/', $sku, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        return $maxNumber + 1;
    }

    // Product details
    public function products(string $supplier)
    {
        $s = Supplier::with('productsRelation')->where('slug', $supplier)->firstOrFail();

        $data = $s->toArray();
        $data['products'] = $s->products;

        $nextSeq = self::getNextProductSequenceNumber();
        $nextProductCodeBase = 'AGR-' . str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);

        return view('suppliers.products', [
            'supplier' => $data,
            'nextProductCodeBase' => $nextProductCodeBase,
        ]);
    }

    public static function generateCategorySuffix(string $categoryOrName, string $productName = ''): string
    {
        $cleanCat = trim($categoryOrName);
        $lowerCat = strtolower($cleanCat);

        if (str_contains($lowerCat, 'rice')) return 'RIC';
        if (str_contains($lowerCat, 'veg')) return 'VEG';
        if (str_contains($lowerCat, 'fruit')) return 'FRU';
        if (str_contains($lowerCat, 'grain')) return 'GRA';

        $source = ($cleanCat === 'Others' || empty($cleanCat)) ? $productName : $cleanCat;
        $alpha = preg_replace('/[^A-Za-z0-9]/', '', $source);
        $suffix = strtoupper(substr($alpha, 0, 3));
        return $suffix ?: 'PRD';
    }

    public function storeProduct(Request $request, string $supplier)
    {
        $s = Supplier::where('slug', $supplier)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_name' => ['required', 'string', 'max:255'],
            'uom_code' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'min_order' => ['required', 'numeric', 'min:0'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $category = Category::firstOrCreate(['category_name' => $data['category_name']]);
        $uom = UnitOfMeasure::firstOrCreate(
            ['uom_code' => $data['uom_code']],
            ['uom_name' => $data['uom_code'] . ' Unit']
        );
        $currency = Currency::firstOrCreate(
            ['currency_code' => 'PHP'],
            ['currency_name' => 'Philippine Peso', 'exchange_rate' => 1.0]
        );

        $suffix = self::generateCategorySuffix($data['category_name'], $data['name']);
        $nextSeq = self::getNextProductSequenceNumber();

        if (!empty($data['code'])) {
            $formattedCode = strtoupper(trim($data['code']));
            $exists = \Illuminate\Support\Facades\DB::table('product_suppliers')->where('supplier_sku', $formattedCode)->exists();
            if ($exists) {
                $formattedCode = strtoupper('AGR-' . str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT) . '-' . $suffix);
            }
        } else {
            $formattedCode = strtoupper('AGR-' . str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT) . '-' . $suffix);
        }

        $product = Product::create([
            'supplier_id' => $s->id,
            'sku' => 'PRD-' . strtoupper(Str::slug($data['name'])) . '-' . Str::random(5),
            'name' => $data['name'],
            'description' => $data['name'] . ' product',
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'currency_id' => $currency->id,
            'base_price' => $data['unit_price'],
            'min_quantity_threshold' => $data['min_order'],
            'lead_time_days' => $data['lead_time_days'],
        ]);

        $s->productsRelation()->syncWithoutDetaching([
            $product->id => [
                'supplier_sku' => $formattedCode,
                'unit_price' => $data['unit_price'],
                'lead_time_days' => $data['lead_time_days'],
                'is_preferred' => true,
            ]
        ]);

        return redirect()->back()->with('status', 'Product added successfully.');
    }

    public function updateProduct(Request $request, string $supplier, int $product)
    {
        $s = Supplier::where('slug', $supplier)->firstOrFail();
        $pModel = Product::findOrFail($product);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_name' => ['required', 'string', 'max:255'],
            'uom_code' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'min_order' => ['required', 'numeric', 'min:0'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $category = Category::firstOrCreate(['category_name' => $data['category_name']]);
        $uom = UnitOfMeasure::firstOrCreate(
            ['uom_code' => $data['uom_code']],
            ['uom_name' => $data['uom_code'] . ' Unit']
        );

        $suffix = self::generateCategorySuffix($data['category_name'], $data['name']);
        $baseSupplierCode = $s->supplier_id ?: $s->supplier_code ?: 'AGR-00100';

        if (!empty($data['code'])) {
            $formattedCode = strtoupper(trim($data['code']));
        } else {
            $formattedCode = strtoupper($baseSupplierCode . '-' . $suffix);
        }

        $isShared = $pModel->suppliers()->count() > 1;

        if ($isShared) {
            $s->productsRelation()->detach($pModel->id);

            $currency = Currency::firstOrCreate(
                ['currency_code' => 'PHP'],
                ['currency_name' => 'Philippine Peso', 'exchange_rate' => 1.0]
            );

            $newProduct = Product::create([
                'supplier_id' => $s->id,
                'sku' => 'PRD-' . strtoupper(Str::slug($data['name'])) . '-' . Str::random(5),
                'name' => $data['name'],
                'description' => $data['name'] . ' product',
                'category_id' => $category->id,
                'uom_id' => $uom->id,
                'currency_id' => $currency->id,
                'base_price' => $data['unit_price'],
                'min_quantity_threshold' => $data['min_order'],
                'lead_time_days' => $data['lead_time_days'],
            ]);

            $s->productsRelation()->attach($newProduct->id, [
                'supplier_sku' => $formattedCode,
                'unit_price' => $data['unit_price'],
                'lead_time_days' => $data['lead_time_days'],
                'is_preferred' => true,
            ]);
        } else {
            $pModel->update([
                'supplier_id' => $s->id,
                'name' => $data['name'],
                'category_id' => $category->id,
                'uom_id' => $uom->id,
                'base_price' => $data['unit_price'],
                'min_quantity_threshold' => $data['min_order'],
                'lead_time_days' => $data['lead_time_days'],
            ]);

            $s->productsRelation()->updateExistingPivot($pModel->id, [
                'supplier_sku' => $formattedCode,
                'unit_price' => $data['unit_price'],
                'lead_time_days' => $data['lead_time_days'],
            ]);
        }

        return redirect()->back()->with('status', 'Product updated successfully.');
    }

    public function destroyProduct(Request $request, string $supplier, int $product)
    {
        $s = Supplier::where('slug', $supplier)->firstOrFail();
        $pModel = Product::find($product);

        $s->productsRelation()->detach($product);

        if ($pModel) {
            if ($pModel->suppliers()->count() === 0) {
                $pModel->delete();
            }
        }

        return redirect()->back()->with('status', 'Product deleted successfully.');
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

    public function updateContract(Request $request, string $supplier)
    {
        $s = Supplier::where('slug', $supplier)->firstOrFail();

        $data = $request->validate([
            'contract_start' => ['required', 'date'],
            'contract_end' => ['required', 'date', 'after_or_equal:contract_start'],
            'payment_terms' => ['nullable', 'string', 'in:COD,Net 15,Net 30,Net 60,Net 90,Advance Payment,Installment'],
        ], [
            'contract_end.after_or_equal' => 'The Contract End date must be a date after or equal to Contract Start.',
            'payment_terms.in' => 'The selected payment terms option is invalid.',
        ]);

        $start = \Illuminate\Support\Carbon::parse($data['contract_start']);
        $end = \Illuminate\Support\Carbon::parse($data['contract_end']);
        $durationDays = (int) $start->diffInDays($end);
        $durationString = $durationDays . ' days';

        $s->update([
            'contract_start' => $start,
            'contract_end' => $end,
            'contract_duration' => $durationString,
            'payment_terms' => $data['payment_terms'] ?? $s->payment_terms,
        ]);

        $s->contractHistoryEntries()->create([
            'date' => now(),
            'action' => 'Contract Updated',
            'performed_by' => auth()->user()->name ?? 'System Admin',
            'remarks' => 'Contract start and end dates updated with calculated duration of ' . $durationString . '.',
        ]);

        return redirect()->back()->with('status', 'Contract details updated successfully.');
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
