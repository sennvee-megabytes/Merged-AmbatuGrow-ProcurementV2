@extends('layouts.master')

@section('title', 'Supplier Profile')
@section('subtitle', 'Purchase history and transaction summary')

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

    <div class="w-full">
            <h1 style="font-size:24px; font-weight:700; color:#111827; margin-bottom:4px;">Purchase History</h1>
            <p class="text-[13px] text-gray-500 mb-6">Details of your transactions with {{ $supplier['name'] }}.</p>

            @if (empty($supplier['purchase_history']))
                <div class="card">
                    <div class="empty-state">
                        <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-sm font-semibold text-gray-400">No purchase history recorded yet.</p>
                    </div>
                </div>
            @else
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <table class="fig-table">
                        <thead>
                            <tr>
                                <th style="padding-left:20px">Date</th>
                                <th>PO No.</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                                <th style="padding-right:20px">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($supplier['purchase_history'] as $entry)
                                <tr>
                                    <td style="padding-left:20px; font-size:13px; color:#374151;">
                                        {{ $entry['date'] ? \Illuminate\Support\Carbon::parse($entry['date'])->format('M d, Y') : '—' }}
                                    </td>
                                    <td style="font-size:13px; color:#111827; font-weight:500;">
                                        {{ $entry['po_number'] }}
                                    </td>
                                    <td style="font-size:13px; color:#374151; font-weight:500;">
                                        {{ $entry['product'] }}
                                    </td>
                                    <td style="font-size:13px; color:#374151;">
                                        {{ $entry['quantity'] }}
                                    </td>
                                    <td style="font-size:13px; color:#111827; font-weight:600;">
                                        ₱{{ number_format($entry['amount'], 2) }}
                                    </td>
                                    <td style="padding-right:20px">
                                        @if ($entry['status'] === 'Low' || $entry['status'] === 'Paid' || $entry['status'] === 'Delivered')
                                            <span class="badge-active">
                                                {{ $entry['status'] }}
                                            </span>
                                        @elseif ($entry['status'] === 'Completed')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                                Completed
                                            </span>
                                        @elseif ($entry['status'] === 'Pending')
                                            <span class="badge-pending">
                                                Pending
                                            </span>
                                        @else
                                            <span class="badge-blacklisted">
                                                {{ $entry['status'] }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
@endsection
