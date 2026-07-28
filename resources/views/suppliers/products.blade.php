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
                    <span>Supplier ID: <strong class="text-slate-800 font-bold">{{ $supplier['supplier_id'] }}</strong></span>
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
        <h1 class="text-xl font-bold text-slate-900 mb-5">Product Details</h1>

        @if ($isBlocked)
            <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0"></i>
                <span>Unit price and Minimum Order Quantity (MOQ) are hidden for blocked suppliers.</span>
            </div>
        @endif

        <div class="flex flex-col gap-5">
            @foreach ($supplier['products'] as $p)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4">{{ $p['name'] }}</h2>
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
                                ['Stock Status', $p['stock_status'] ?? 'In Stock'],
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

@endsection
