@php
    $catalogProducts = \App\Models\Product::with('uom')->orderBy('name')->get();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AMBATUGROW ERP') · Purchase Requisition & Approval</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('appLayout', () => ({
                showRequisitionModal: false,
                showPoModal: false,
                catalogProducts: @json($catalogProducts ?? []),
                reqItems: [{ sku: '', name: '', unit: 'Unit', qty: 1, cost: 0, justification: '' }],
                poItems: [{ sku: '', name: '', unit: 'Unit', qty: 1, cost: 0 }],
                selectCatalogItem(itemIndex, productId) {
                    if (!productId) return;
                    const prod = this.catalogProducts.find(p => p.id == productId);
                    if (prod && this.reqItems[itemIndex]) {
                        this.reqItems[itemIndex].sku = prod.sku || '';
                        this.reqItems[itemIndex].name = prod.name || '';
                        this.reqItems[itemIndex].unit = prod.uom ? (prod.uom.uom_code || prod.uom.uom_name) : 'Unit';
                        this.reqItems[itemIndex].cost = Number(prod.base_price || 0);
                    }
                },
                addReqItem() {
                    this.reqItems.push({ sku: '', name: '', unit: 'Unit', qty: 1, cost: 0, justification: '' });
                },
                removeReqItem(i) {
                    this.reqItems.splice(i, 1);
                },
                addPoItem() {
                    this.poItems.push({ sku: '', name: '', unit: 'Unit', qty: 1, cost: 0 });
                },
                removePoItem(i) {
                    this.poItems.splice(i, 1);
                },
                reqTotal() {
                    return this.reqItems.reduce((sum, item) => sum + (Number(item.qty || 0) * Number(item.cost || 0)), 0);
                },
                poSubtotal() {
                    return this.poItems.reduce((sum, item) => sum + (Number(item.qty || 0) * Number(item.cost || 0)), 0);
                },
                poVat() {
                    return this.poSubtotal() * 0.12;
                },
                poTotal() {
                    return this.poSubtotal() + this.poVat();
                }
            }));
        });
    </script>
    <style>
        :root{
            --green-900:#1c3820;
            --green-800:#1f4223;
            --green-700:#25502a;
            --green-600:#2f6435;
            --green-active:#3d7f43;
            --blue-600:#2563eb;
            --blue-700:#1d4ed8;
            --bg-page:#f4f5f7;
        }
        body{ font-family:'Inter',sans-serif; background:var(--bg-page); }
        .sidebar{ background:linear-gradient(180deg,var(--green-900),var(--green-700)); }
        .nav-item{ color:#d7e6d9; transition:.15s; }
        .nav-item:hover{ background:rgba(255,255,255,.08); color:#fff; }
        .nav-item.active{ background:var(--green-active); color:#fff; box-shadow: inset 3px 0 0 #ffffff; }
        .btn-primary{ background:var(--blue-600); }
        .btn-primary:hover{ background:var(--blue-700); }
        .card{ background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; }
        ::-webkit-scrollbar{ width:8px; height:8px; }
        ::-webkit-scrollbar-thumb{ background:#c9ccd1; border-radius:8px; }
        [x-cloak]{ display:none !important; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex text-slate-800" x-data="appLayout"
      @open-requisition-modal.window="showRequisitionModal = true"
      @open-po-modal.window="showPoModal = true">

    {{-- Sidebar --}}
    <aside class="sidebar w-60 shrink-0 min-h-screen flex flex-col py-5 px-4 text-sm">
        <div class="flex items-center gap-3 px-2 mb-8">
            <div class="w-9 h-9 rounded-md bg-white shrink-0"></div>
            <div class="leading-tight">
                <p class="text-white font-bold tracking-wide text-[13px]">AMBATUGROW</p>
                <p class="text-[10px] text-green-200 tracking-widest">ERP SYSTEM</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto no-scrollbar">
            <a href="{{ route('requisitions.create') }}" class="nav-item {{ request()->routeIs('requisitions.create') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-md">
                <i class="fa-solid fa-plus w-4 text-center"></i> Create Requisition
            </a>
            <a href="{{ route('requisitions.tracking') }}" class="nav-item {{ request()->routeIs('requisitions.tracking') ? 'active' : '' }} flex items-center justify-between px-3 py-2.5 rounded-md">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-diagram-project w-4 text-center"></i> Tracking
                </div>
                @if(($pendingApprovalCount ?? 0) > 0)
                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-red-600 text-white">
                        {{ $pendingApprovalCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('procurement.home') }}" class="nav-item {{ request()->routeIs('procurement.*') ? 'active' : '' }} flex items-center justify-between px-3 py-2.5 rounded-md">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-cart-shopping w-4 text-center"></i> Order Management
                </div>
                @if(($pendingApprovalCount ?? 0) > 0)
                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-red-600 text-white">
                        {{ $pendingApprovalCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('approvals.index') }}" class="nav-item {{ request()->routeIs('approvals.*') ? 'active' : '' }} flex items-center justify-between px-3 py-2.5 rounded-md">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-square-check w-4 text-center"></i> Approvals
                </div>
                @if(($pendingApprovalCount ?? 0) > 0)
                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-red-600 text-white">
                        {{ $pendingApprovalCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('suppliers.index') }}" class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-md">
                <i class="fa-solid fa-handshake w-4 text-center"></i> Suppliers
            </a>
        </nav>

        <div class="space-y-1 pt-4 border-t border-white/10 shrink-0">
            <form method="POST" action="{{ route('logout') }}" class="m-0 w-full">
                @csrf
                <button type="submit" class="nav-item text-red-200 hover:text-white hover:bg-red-900/50 flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-left font-bold transition">
                    <i class="fa-solid fa-right-from-bracket w-4 text-center text-red-300"></i> Log Out
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 min-h-screen flex flex-col">
        @include('partials.requisitions-topbar')

        <main class="flex-1 p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 text-sm">
                    <i class="fa-solid fa-circle-check mr-1.5"></i>{{ session('status') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    @php
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
    @endphp

    <!-- Requisition Modal Overlay -->
    <div x-show="showRequisitionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showRequisitionModal = false"></div>
        
        <form action="{{ route('requisitions.store') }}" method="POST" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col z-50 animate-in fade-in zoom-in duration-200">
            @csrf
            <input type="hidden" name="action" value="continue">
            <input type="hidden" name="title" x-bind:value="'PR - ' + (reqItems[0] && reqItems[0].name ? reqItems[0].name : 'Office Supplies') + ' (' + new Date().toLocaleDateString() + ')'">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Raise Purchase Requisition</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Total: <span class="font-bold text-[#1f5c3d]" x-text="'₱' + Number(reqTotal()).toLocaleString('en-US', {minimumFractionDigits: 2})">₱0.00</span> · Approval: <span class="text-emerald-700 font-semibold">Level 1</span>
                    </p>
                </div>
                <button type="button" @click="showRequisitionModal = false" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 overflow-y-auto space-y-5 flex-1 text-sm text-left">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">REQUESTED BY *</label>
                        <input type="text" name="requestor_name" required value="{{ auth()->user()->name }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DEPARTMENT</label>
                        <select name="department" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="Farm Operations">Farm Operations</option>
                            <option value="Logistics">Logistics</option>
                            <option value="Procurement">Procurement</option>
                            <option value="Finance">Finance</option>
                            <option value="Marketing">Marketing</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DATE NEEDED *</label>
                        <input type="date" name="needed_by" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">PRIORITY</label>
                        <select name="urgency" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="Medium">Normal</option>
                            <option value="High">High</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase">Line Items *</span>
                        <button type="button" @click="addReqItem()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-plus"></i> Add Line
                        </button>
                    </div>
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50 p-4 space-y-4 max-h-[300px] overflow-y-auto">
                        <template x-for="(item, index) in reqItems" :key="index">
                            <div class="grid grid-cols-12 gap-3 items-end bg-white p-3 rounded-lg border border-slate-100 shadow-sm relative pt-6 md:pt-3">
                                <button type="button" @click="if(reqItems.length > 1) removeReqItem(index)" class="absolute top-1 right-2 text-slate-400 hover:text-red-500 md:hidden">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                <!-- Database Order Catalog Dropdown -->
                                <div class="col-span-12 pb-1 border-b border-slate-100">
                                    <label class="block text-[10px] font-extrabold text-emerald-800 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-cart-shopping text-emerald-600"></i> Select Order Item (Database Catalog)
                                    </label>
                                    <select @change="selectCatalogItem(index, $event.target.value)" class="w-full px-2.5 py-1.5 border border-emerald-300 rounded-lg text-xs bg-emerald-50/50 text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                        <option value="">-- Select Order Item from Database --</option>
                                        <template x-for="prod in catalogProducts" :key="prod.id">
                                            <option :value="prod.id" x-text="prod.sku + ' - ' + prod.name + ' (₱' + Number(prod.base_price).toLocaleString('en-US', {minimumFractionDigits: 2}) + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-12 md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">SKU</label>
                                    <input type="text" x-model="item.sku" :name="`items[${index}][sku]`" placeholder="e.g. AGRI-SEED-042" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-12 md:col-span-4">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Item Name</label>
                                    <input type="text" x-model="item.name" :name="`items[${index}][name]`" required placeholder="e.g. Hybrid Rice Seeds" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-4 md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">UOM</label>
                                    <input type="text" x-model="item.unit" :name="`items[${index}][unit]`" required placeholder="Unit" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-4 md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Qty</label>
                                    <input type="number" x-model.number="item.qty" :name="`items[${index}][qty]`" required min="1" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-4 md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Est. Unit Cost (₱)</label>
                                    <input type="number" x-model.number="item.cost" :name="`items[${index}][unit_price]`" required min="0" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-11 md:col-span-11">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Justification</label>
                                    <input type="text" x-model="item.justification" placeholder="Reason for request..." class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-1 md:col-span-1 flex items-center justify-between pt-1">
                                    <span class="text-xs font-bold text-slate-600 pr-2" x-text="'₱' + Number(item.qty * item.cost).toLocaleString('en-US')">₱0</span>
                                    <button type="button" @click="if(reqItems.length > 1) removeReqItem(index)" class="text-slate-400 hover:text-red-500 hidden md:block">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">REMARKS</label>
                    <textarea name="purpose" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3 bg-slate-50">
                <button type="button" @click="showRequisitionModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-100 text-xs font-bold transition">CANCEL</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">SUBMIT PR</button>
            </div>
        </form>
    </div>

    <!-- PO Modal Overlay -->
    <div x-show="showPoModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showPoModal = false"></div>
        
        <form action="{{ route('purchase_orders.store') }}" method="POST" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col z-50 animate-in fade-in zoom-in duration-200">
            @csrf
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Create Purchase Order</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Subtotal: <span class="font-bold text-slate-700" x-text="'₱' + Number(poSubtotal()).toLocaleString('en-US')">₱0</span> · VAT (12%): <span class="font-bold text-slate-700" x-text="'₱' + Number(poVat()).toLocaleString('en-US')">₱0</span> · Total: <span class="font-bold text-[#1f5c3d]" x-text="'₱' + Number(poTotal()).toLocaleString('en-US')">₱0</span>
                    </p>
                </div>
                <button type="button" @click="showPoModal = false" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 overflow-y-auto space-y-5 flex-1 text-sm text-left">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">SUPPLIER *</label>
                        <select name="supplier_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#1f5c3d]/20 focus:border-[#1f5c3d]">
                            <option value="">-- Choose Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">EXPECTED DELIVERY *</label>
                        <input type="date" name="expected_delivery" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1f5c3d]/20 focus:border-[#1f5c3d]">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">PAYMENT TERMS</label>
                        <input type="text" name="payment_terms" value="Net 30" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1f5c3d]/20 focus:border-[#1f5c3d]">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase">Line Items</span>
                        <button type="button" @click="addPoItem()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-plus"></i> Add Line
                        </button>
                    </div>
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50 p-4 space-y-4 max-h-[300px] overflow-y-auto">
                        <template x-for="(item, index) in poItems" :key="index">
                            <div class="grid grid-cols-12 gap-3 items-end bg-white p-3 rounded-lg border border-slate-100 shadow-sm relative pt-6 md:pt-3">
                                <button type="button" @click="if(poItems.length > 1) removePoItem(index)" class="absolute top-1 right-2 text-slate-400 hover:text-red-500 md:hidden">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                <div class="col-span-12 md:col-span-3">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">SKU</label>
                                    <input type="text" x-model="item.sku" :name="`items[${index}][sku]`" placeholder="e.g. AGRI-SEED-042" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-12 md:col-span-4">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Item Name</label>
                                    <input type="text" x-model="item.name" :name="`items[${index}][name]`" required placeholder="e.g. Hybrid Rice Seeds" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-4 md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">UOM</label>
                                    <input type="text" x-model="item.unit" placeholder="Unit" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-4 md:col-span-1">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Ordered Qty</label>
                                    <input type="number" x-model.number="item.qty" :name="`items[${index}][quantity]`" required min="1" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-4 md:col-span-1">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Unit Price (₱)</label>
                                    <input type="number" x-model.number="item.cost" :name="`items[${index}][unit_price]`" required min="0" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <div class="col-span-12 md:col-span-1 flex items-center justify-between pt-1">
                                    <span class="text-xs font-bold text-slate-600 pr-2" x-text="'₱' + Number(item.qty * item.cost).toLocaleString('en-US')">₱0</span>
                                    <button type="button" @click="if(poItems.length > 1) removePoItem(index)" class="text-slate-400 hover:text-red-500 hidden md:block">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">NOTES / INSTRUCTIONS</label>
                    <textarea name="notes" rows="2" placeholder="Any specific delivery instructions or warehouse directions..." class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1f5c3d]/20 focus:border-[#1f5c3d]"></textarea>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3 bg-slate-50">
                <button type="button" @click="showPoModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-100 text-xs font-bold transition">CANCEL</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-sm">CREATE PO</button>
            </div>
        </form>
    </div>

    @stack('scripts')
</body>
</html>
