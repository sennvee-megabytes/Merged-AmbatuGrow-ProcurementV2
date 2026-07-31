@extends('layouts.master')

@section('title', 'Supplier Profile')
@section('subtitle', 'Performance scorecard and KPIs')

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

    {{-- Supplier Header --}}
    <div class="flex items-start gap-5 mb-7 font-sans">
        <div class="w-20 h-20 rounded-full bg-green-900 border-2 border-white flex items-center justify-center text-white text-2xl font-black shrink-0 shadow-md">
            {{ strtoupper(substr($supplier['supplier_name'] ?? $supplier['name'], 0, 1)) }}
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $supplier['supplier_name'] ?? $supplier['name'] }}</h1>
            <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                <span>Supplier ID: <span class="font-semibold">{{ $supplier['supplier_id'] }}</span></span>
                <span class="text-slate-300">•</span>
                <span>Supplier since {{ $supplier['since'] }}</span>
            </div>
            <p class="text-xs text-gray-600 mt-2.5 max-w-xl leading-relaxed">{{ $supplier['description'] }}</p>
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

    <div class="w-full flex flex-col gap-6">
            <div>
                <h1 style="font-size:24px; font-weight:700; color:#111827; margin-bottom:4px;">{{ $supplier['name'] }} Performance</h1>
                <p class="text-[13px] text-gray-500">Performance insights based on total orders, on-time delivery, average rating and product quality.</p>
            </div>

            {{-- 2x2 Grid of Performance Metrics --}}
            <div class="grid grid-cols-2 gap-5">
                @foreach ([
                    [
                        'label' => 'Average Rating',
                        'value' => $supplier['performance']['avg_rating'] . ' / 5',
                        'trend' => $supplier['performance']['avg_rating_delta'] ?? '↑ 0.2 from last month',
                        'bg' => '#FEF3C7',
                        'color' => '#F59E0B'
                    ],
                    [
                        'label' => 'On-time Delivery',
                        'value' => $supplier['performance']['on_time'],
                        'trend' => $supplier['performance']['on_time_delta'] ?? '↑ 5% from last month',
                        'bg' => '#D1FAE5',
                        'color' => '#10B981'
                    ],
                    [
                        'label' => 'Quality Score',
                        'value' => ($supplier['performance']['quality_score'] ?? '4.6') . ' / 5',
                        'trend' => $supplier['performance']['quality_delta'] ?? '↑ 0.3 from last month',
                        'bg' => '#FED7AA',
                        'color' => '#F97316'
                    ],
                    [
                        'label' => 'Total Orders',
                        'value' => $supplier['performance']['total_orders'],
                        'trend' => $supplier['performance']['total_orders_delta'] ?? '↑ 18 this month',
                        'bg' => '#F3F4F6',
                        'color' => '#6B7280'
                    ]
                ] as $card)
                <div class="card !p-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-full shrink-0" style="background-color: {{ $card['bg'] }};"></div>
                    <div>
                        <div class="text-[14px] font-medium text-gray-500">{{ $card['label'] }}</div>
                        <div class="text-[26px] font-black text-gray-900 leading-tight mt-0.5">{{ $card['value'] }}</div>
                        <div class="text-[11px] text-green-600 font-semibold mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            {{ $card['trend'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
@endsection
