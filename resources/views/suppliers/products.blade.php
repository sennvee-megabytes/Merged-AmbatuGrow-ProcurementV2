@extends('layouts.master')

@section('title', 'Supplier Profile')
@section('subtitle', 'Supplier catalog and custom pricing')

@section('content')

    {{-- Top-Left Back Button --}}
    <div class="mb-4">
        <button type="button" 
                onclick="if (window.history.length > 1 && document.referrer && document.referrer.indexOf('/supplier-management') !== -1) { window.history.back(); } else { window.location.href='{{ route('suppliers.index') }}'; }"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-slate-900 text-xs font-bold shadow-sm transition-all cursor-pointer">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Supplier List</span>
        </button>
    </div>

@php
    $isBlocked = in_array(strtolower($supplier['status'] ?? ''), ['blacklisted', 'blocked'], true);
@endphp

    {{-- Supplier Header Bar --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-5 font-sans">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-[#1f5c3d] border-2 border-emerald-400/30 flex items-center justify-center text-white text-3xl font-black shrink-0 shadow-md">
                {{ strtoupper(substr($supplier['supplier_name'] ?? $supplier['name'], 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $supplier['supplier_name'] ?? $supplier['name'] }}</h1>
                    @if ($isBlocked)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-red-100 text-red-700 border border-red-200">
                            <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                            Blocked / Blacklisted
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-700 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            Active Supplier
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-600 mt-1.5 flex items-center gap-2 font-medium">
                    <span>Supplier ID: <strong class="text-slate-800 font-bold" id="supplier-id-val">{{ $supplier['supplier_id'] }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span>Supplier Since: <strong class="text-slate-800 font-bold">{{ $supplier['since'] }}</strong></span>
                </div>
                <p class="text-xs text-slate-600 mt-2 max-w-xl leading-relaxed">{{ $supplier['description'] }}</p>
            </div>
        </div>
    </div>

    {{-- Horizontal Sub-tabs Navigation --}}
    <div class="border-b border-slate-200/60 mb-6 w-full flex items-center gap-6 text-sm font-medium font-sans">
        <a href="{{ route('suppliers.show', $supplier['slug']) }}" class="pb-3 border-b-2 transition-colors {{ request()->routeIs('suppliers.show') ? 'border-green-800 text-green-800 font-bold' : 'border-transparent text-gray-500 hover:text-gray-900' }}">Overview</a>
        <a href="{{ route('suppliers.products', $supplier['slug']) }}" class="pb-3 border-b-2 transition-colors {{ request()->routeIs('suppliers.products') ? 'border-green-800 text-green-800 font-bold' : 'border-transparent text-gray-500 hover:text-gray-900' }}">Products</a>
        <a href="{{ route('suppliers.purchase-history', $supplier['slug']) }}" class="pb-3 border-b-2 transition-colors {{ request()->routeIs('suppliers.purchase-history') ? 'border-green-800 text-green-800 font-bold' : 'border-transparent text-gray-500 hover:text-gray-900' }}">Purchase History</a>
        <a href="{{ route('suppliers.contract', $supplier['slug']) }}" class="pb-3 border-b-2 transition-colors {{ request()->routeIs('suppliers.contract') ? 'border-green-800 text-green-800 font-bold' : 'border-transparent text-gray-500 hover:text-gray-900' }}">Contract</a>
        <a href="{{ route('suppliers.performance', $supplier['slug']) }}" class="pb-3 border-b-2 transition-colors {{ request()->routeIs('suppliers.performance') ? 'border-green-800 text-green-800 font-bold' : 'border-transparent text-gray-500 hover:text-gray-900' }}">Performance</a>
    </div>

    <div class="w-full">
        @if (session('status'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center justify-between mb-5">
            <h1 class="text-xl font-bold text-slate-900">Product Details</h1>
            @if (!$isBlocked)
                <button type="button" onclick="openAddProductModal()" class="btn-primary text-xs py-2.5 px-4 flex items-center gap-1.5 font-bold rounded-xl shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Product</span>
                </button>
            @endif
        </div>

        @if ($isBlocked)
            <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0"></i>
                <span>Unit price and Minimum Order Quantity (MOQ) are hidden for blocked suppliers.</span>
            </div>
        @endif

        <div class="flex flex-col gap-5">
            @foreach ($supplier['products'] as $p)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900">{{ $p['name'] }}</h2>
                    @if (!$isBlocked)
                        <div class="flex items-center gap-2">
                            <button type="button" onclick='openEditProductModal(@json($p))' class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-sm">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Edit Product</span>
                            </button>
                            <form method="POST" action="{{ route('suppliers.products.destroy', [$supplier['slug'], $p['id']]) }}" onsubmit="return confirm('Are you sure you want to delete this product?');" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition-all shadow-sm">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-600"></i>
                                    <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col md:flex-row gap-6">
                    {{-- Image Placeholder --}}
                    <div class="w-full md:w-52 h-44 bg-slate-100 border border-slate-200 rounded-xl flex flex-col items-center justify-center shrink-0 text-slate-400">
                        <i data-lucide="package" class="w-8 h-8 mb-1"></i>
                        <span class="font-semibold text-xs">Product Image</span>
                    </div>

                    {{-- Product Details Table --}}
                    <table class="flex-1 border-collapse bg-white">
                        <thead>
                            <tr class="bg-white border-b border-slate-200">
                                <th class="text-left py-2 pr-4 text-xs font-extrabold uppercase tracking-wider text-slate-500 border-b border-slate-200 w-1/2">Field</th>
                                <th class="text-left py-2 pr-4 text-xs font-extrabold uppercase tracking-wider text-slate-500 border-b border-slate-200">Details</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach([
                                ['Product Code', $p['code'] ?? '—'],
                                ['Category',     $p['category'] ?? '—'],
                                ['Unit',         $p['unit'] ?? '—'],
                                ['Unit Price',   $isBlocked ? '—' : ($p['unit_price'] ?? $p['price'] ?? '—')],
                                ['Stock Status', $p['stock_status'] ?? $p['stock'] ?? 'In Stock'],
                                ['Minimum Order Quantity (MOQ)', $isBlocked ? '—' : ($p['min_order'] ?? $p['moq'] ?? '—')],
                                ['Lead time',    $p['lead_time'] ?? '—'],
                            ] as [$field, $detail])
                            <tr class="bg-white">
                                <td class="py-2.5 pr-4 text-xs font-semibold text-slate-600 border-b border-slate-100">{{ $field }}</td>
                                <td class="py-2.5 pr-4 text-xs font-bold text-slate-900 border-b border-slate-100">
                                    @if ($isBlocked && in_array($field, ['Unit Price', 'Minimum Order Quantity (MOQ)'], true))
                                        <span class="text-slate-400 italic font-normal">— (Hidden for blocked supplier)</span>
                                    @else
                                        {{ $detail }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Add Product Modal --}}
    <div id="addProductModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="card w-full max-w-lg shadow-xl relative bg-white rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900">Add New Product</h3>
                <button type="button" onclick="closeAddProductModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('suppliers.products.store', $supplier['slug']) }}" class="flex flex-col gap-4">
                @csrf
                <div>
                    <label class="form-label">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="add_name" placeholder="e.g. Premium Jasmine Rice" class="form-input" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Category <span class="text-red-500">*</span></label>
                        <select name="category_name" id="add_category_name" class="form-input" required>
                            <option value="Rice">Rice</option>
                            <option value="Vegetables">Vegetables</option>
                            <option value="Fruits">Fruits</option>
                            <option value="Grains">Grains</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Unit / UOM <span class="text-red-500">*</span></label>
                        <select name="uom_code" id="add_uom_code" class="form-input" required>
                            <option value="Sack">Sack</option>
                            <option value="Box">Box</option>
                            <option value="Crate">Crate</option>
                            <option value="Pcs">Pcs</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Product Code (Auto-generated/Uppercase) <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="add_code" style="text-transform: uppercase;" placeholder="e.g. {{ $supplier['supplier_id'] }}-RIC" class="form-input font-mono uppercase" required>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="form-label">Unit Price (₱) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" onkeydown="if(event.key==='-'||event.key==='e') event.preventDefault();" name="unit_price" id="add_unit_price" placeholder="1000.00" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">MOQ <span class="text-red-500">*</span></label>
                        <input type="number" step="1" min="0" onkeydown="if(event.key==='-'||event.key==='e') event.preventDefault();" name="min_order" id="add_min_order" placeholder="10" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Lead Time (Days) <span class="text-red-500">*</span></label>
                        <input type="number" step="1" min="0" onkeydown="if(event.key==='-'||event.key==='e') event.preventDefault();" name="lead_time_days" id="add_lead_time_days" placeholder="3" class="form-input" required>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeAddProductModal()" class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Product Modal --}}
    <div id="editProductModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="card w-full max-w-lg shadow-xl relative bg-white rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900">Edit Product</h3>
                <button type="button" onclick="closeEditProductModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="editProductForm" method="POST" action="" class="flex flex-col gap-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Category <span class="text-red-500">*</span></label>
                        <select name="category_name" id="edit_category_name" class="form-input" required>
                            <option value="Rice">Rice</option>
                            <option value="Vegetables">Vegetables</option>
                            <option value="Fruits">Fruits</option>
                            <option value="Grains">Grains</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Unit / UOM <span class="text-red-500">*</span></label>
                        <select name="uom_code" id="edit_uom_code" class="form-input" required>
                            <option value="Sack">Sack</option>
                            <option value="Box">Box</option>
                            <option value="Crate">Crate</option>
                            <option value="Pcs">Pcs</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Product Code (Uppercase) <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="edit_code" style="text-transform: uppercase;" class="form-input font-mono uppercase" required>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="form-label">Unit Price (₱) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" onkeydown="if(event.key==='-'||event.key==='e') event.preventDefault();" name="unit_price" id="edit_unit_price" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">MOQ <span class="text-red-500">*</span></label>
                        <input type="number" step="1" min="0" onkeydown="if(event.key==='-'||event.key==='e') event.preventDefault();" name="min_order" id="edit_min_order" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Lead Time (Days) <span class="text-red-500">*</span></label>
                        <input type="number" step="1" min="0" onkeydown="if(event.key==='-'||event.key==='e') event.preventDefault();" name="lead_time_days" id="edit_lead_time_days" class="form-input" required>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditProductModal()" class="btn-outline">Cancel</button>
                    <button type="submit" class="btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const nextCodeBase = "{{ $nextProductCodeBase ?? ($supplier['supplier_id'] ?? 'AGR-00100') }}";
        const updateRouteTemplate = "{{ route('suppliers.products.update', [$supplier['slug'], ':id']) }}";

        function openAddProductModal() {
            document.getElementById('addProductModal').classList.remove('hidden');
            updateAddProductCode();
        }

        function closeAddProductModal() {
            document.getElementById('addProductModal').classList.add('hidden');
        }

        function openEditProductModal(product) {
            const form = document.getElementById('editProductForm');
            form.action = updateRouteTemplate.replace(':id', product.id);

            document.getElementById('edit_name').value = product.name || '';
            document.getElementById('edit_category_name').value = product.category || 'Grains';
            document.getElementById('edit_uom_code').value = product.unit || 'Sack';
            document.getElementById('edit_code').value = (product.code || '').toUpperCase();
            document.getElementById('edit_unit_price').value = product.raw_unit_price || parseFloat((product.price || '0').replace(/[^0-9.]/g, '')) || 0;
            document.getElementById('edit_min_order').value = product.raw_moq || parseInt((product.moq || '10').replace(/[^0-9]/g, '')) || 10;
            document.getElementById('edit_lead_time_days').value = product.raw_lead_time || parseInt((product.lead_time || '3').replace(/[^0-9]/g, '')) || 3;

            document.getElementById('editProductModal').classList.remove('hidden');
        }

        function closeEditProductModal() {
            document.getElementById('editProductModal').classList.add('hidden');
        }

        function getCategorySuffix(categoryVal, nameVal) {
            const cat = (categoryVal || '').toLowerCase().trim();
            if (cat.includes('rice')) return 'RIC';
            if (cat.includes('veg')) return 'VEG';
            if (cat.includes('fruit')) return 'FRU';
            if (cat.includes('grain')) return 'GRA';

            const source = (categoryVal === 'Others' || !categoryVal) ? nameVal : categoryVal;
            const alpha = (source || '').replace(/[^A-Za-z0-9]/g, '');
            const suffix = alpha.substring(0, 3).toUpperCase();
            return suffix.length > 0 ? suffix : 'PRD';
        }

        function updateAddProductCode() {
            const catVal = document.getElementById('add_category_name').value;
            const nameVal = document.getElementById('add_name').value;
            const codeInput = document.getElementById('add_code');

            if (!codeInput.dataset.userEdited) {
                const suffix = getCategorySuffix(catVal, nameVal);
                codeInput.value = (nextCodeBase + '-' + suffix).toUpperCase();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const addCode = document.getElementById('add_code');
            const editCode = document.getElementById('edit_code');
            const addCat = document.getElementById('add_category_name');
            const addName = document.getElementById('add_name');

            if (addCode) {
                addCode.addEventListener('input', function () {
                    this.value = this.value.toUpperCase();
                    this.dataset.userEdited = "true";
                });
                addCode.addEventListener('blur', function () {
                    this.value = this.value.toUpperCase();
                });
            }

            if (editCode) {
                editCode.addEventListener('input', function () {
                    this.value = this.value.toUpperCase();
                });
                editCode.addEventListener('blur', function () {
                    this.value = this.value.toUpperCase();
                });
            }

            if (addCat) addCat.addEventListener('change', updateAddProductCode);
            if (addName) addName.addEventListener('input', updateAddProductCode);

            ['add_unit_price', 'add_min_order', 'add_lead_time_days', 'edit_unit_price', 'edit_min_order', 'edit_lead_time_days'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        if (this.value !== '' && parseFloat(this.value) < 0) {
                            this.value = Math.abs(parseFloat(this.value)) || 0;
                        }
                    });
                }
            });
        });
    </script>
@endsection
