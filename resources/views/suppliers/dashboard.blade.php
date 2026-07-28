@extends('layouts.master')

@section('title', 'Supplier Directory')
@section('subtitle', 'Manage and evaluate your agricultural suppliers')

@section('content')

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Total Suppliers --}}
        <a href="{{ route('suppliers.index') }}" class="kpi-card hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500 mb-0.5">Total Supplier</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                <div class="text-xs text-green-600 font-medium mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    6 this month
                </div>
            </div>
        </a>

        {{-- Active Suppliers --}}
        <a href="{{ route('suppliers.active') }}" class="kpi-card hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500 mb-0.5">Active Suppliers</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</div>
                <div class="text-xs text-gray-500 font-medium mt-1">{{ round(($stats['active'] / max($stats['total'], 1)) * 100) }}% of total</div>
            </div>
        </a>

        {{-- Blacklisted --}}
        <a href="{{ route('suppliers.blacklisted') }}" class="kpi-card hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500 mb-0.5">Blacklisted Suppliers</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['blacklisted'] }}</div>
                <div class="text-xs text-red-500 font-medium mt-1">{{ round(($stats['blacklisted'] / max($stats['total'], 1)) * 100) }}% of total</div>
            </div>
        </a>
    </div>

    {{-- Middle Row --}}
    <div class="grid grid-cols-3 gap-5 mb-5">

        {{-- Supplier List Overview --}}
        <div class="col-span-2 card !p-0 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-[15px] font-bold text-gray-900">Supplier List Overview</h2>
                <a href="{{ route('suppliers.create') }}" class="btn-primary text-[13px] py-2 px-4">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Supplier
                </a>
            </div>
            <table class="fig-table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Products</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $s)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('suppliers.show', $s['slug']) }}'">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar"></div>
                                <div>
                                    <div class="supplier-name">{{ $s['name'] }}</div>
                                    <div class="supplier-id">{{ $s['supplier_id'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-gray-600">{{ $s['products_list'] }}</td>
                        <td>
                            <span class="flex items-center gap-1.5 text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                {{ $s['location'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-3 border-t border-gray-100">
                <a href="{{ route('suppliers.index') }}" class="text-[13px] font-semibold text-green-700 hover:text-green-900 flex items-center gap-1">
                    View all suppliers
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Right column --}}
        <div class="flex flex-col gap-5">
            {{-- Donut Chart - Product Supplied --}}
            <div class="card">
                <h2 class="card-title">Product Supplied</h2>
                <div class="flex items-center gap-5">
                    {{-- SVG Donut --}}
                    <svg width="90" height="90" viewBox="0 0 90 90" class="shrink-0">
                        <circle cx="45" cy="45" r="34" fill="none" stroke="#E5E7EB" stroke-width="18"/>
                        {{-- Others 10% --}}
                        <circle cx="45" cy="45" r="34" fill="none" stroke="#D1D5DB" stroke-width="18"
                            stroke-dasharray="{{ 2 * 3.14159 * 34 * 0.10 }} {{ 2 * 3.14159 * 34 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 34 * 0.0 }}"
                            transform="rotate(-90 45 45)"/>
                        {{-- Vegetables 20% --}}
                        <circle cx="45" cy="45" r="34" fill="none" stroke="#6EE7B7" stroke-width="18"
                            stroke-dasharray="{{ 2 * 3.14159 * 34 * 0.20 }} {{ 2 * 3.14159 * 34 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 34 * -0.10 }}"
                            transform="rotate(-90 45 45)"/>
                        {{-- Fruits 30% --}}
                        <circle cx="45" cy="45" r="34" fill="none" stroke="#FCD34D" stroke-width="18"
                            stroke-dasharray="{{ 2 * 3.14159 * 34 * 0.30 }} {{ 2 * 3.14159 * 34 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 34 * -0.30 }}"
                            transform="rotate(-90 45 45)"/>
                        {{-- Rice 40% --}}
                        <circle cx="45" cy="45" r="34" fill="none" stroke="#059669" stroke-width="18"
                            stroke-dasharray="{{ 2 * 3.14159 * 34 * 0.40 }} {{ 2 * 3.14159 * 34 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 34 * -0.60 }}"
                            transform="rotate(-90 45 45)"/>
                        <circle cx="45" cy="45" r="25" fill="white"/>
                    </svg>
                    <ul class="space-y-1.5 flex-1">
                        @foreach([['Rice','#059669',40],['Fruits','#FCD34D',30],['Vegetables','#6EE7B7',20],['Others','#D1D5DB',10]] as [$label,$color,$pct])
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-[13px] text-gray-600">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $color }}"></span>
                                {{ $label }}
                            </span>
                            <span class="text-[13px] font-semibold text-gray-900">{{ $pct }}%</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Top Suppliers --}}
            <div class="card flex-1">
                <h2 class="card-title">Top Suppliers  (by Orders)</h2>
                @if (!empty($topSuppliers) && count($topSuppliers) > 0)
                    <ol class="space-y-3">
                        @foreach ($topSuppliers as $i => $s)
                        <li class="flex items-center gap-3">
                            <span class="text-[13px] font-semibold text-gray-500 w-4 shrink-0">{{ $i+1 }}</span>
                            <div class="avatar-sm shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[13px] font-semibold text-gray-800 truncate">{{ $s['name'] }}</div>
                                <div class="progress-bar mt-1" style="width: 100%">
                                    <div style="width: {{ $s['progress_percentage'] }}%"></div>
                                </div>
                            </div>
                            <span class="text-[12px] font-medium text-gray-500 shrink-0 whitespace-nowrap">{{ number_format($s['orders_count']) }} {{ $s['orders_count'] == 1 ? 'order' : 'orders' }}</span>
                        </li>
                        @endforeach
                    </ol>
                @else
                    <div class="text-[13px] text-gray-500 py-6 text-center">
                        No supplier order data available.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Performance Overview --}}
    <div class="card">
        <h2 class="card-title">Supplier Performance Overview</h2>
        <div class="grid grid-cols-4 gap-6">
            @foreach([
                ['Average Rating','4.6 / 5','↑ 0.2 from last month','#F59E0B','#FEF3C7'],
                ['On-time Delivery','92 %','↑ 5% from last month','#10B981','#D1FAE5'],
                ['Total Orders','245','↑ 18 this month','#6B7280','#F3F4F6'],
                ['Quality Score','4.6 / 5','↑ 0.3 from last month','#F97316','#FED7AA'],
            ] as [$label,$value,$trend,$color,$bg])
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold" style="background:{{ $bg }}; color:{{ $color }}">
                    <span class="text-center leading-tight text-[18px] font-black" style="color:{{ $color }}">{{ explode(' ', $value)[0] }}</span>
                </div>
                <div>
                    <div class="text-[12px] font-medium text-gray-500">{{ $label }}</div>
                    <div class="text-[18px] font-black text-gray-900 leading-tight">{{ $value }}</div>
                    <div class="text-[11px] text-green-600 font-medium mt-0.5 flex items-center gap-1">
                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        {{ $trend }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection
