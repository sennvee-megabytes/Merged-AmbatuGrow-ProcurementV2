@extends('layouts.master')

@section('title', 'Supplier Profile')
@section('subtitle', 'Contractual agreements and active duration')

@section('content')

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

    <div class="w-full flex flex-col gap-5">
            <div>
                <h1 style="font-size:24px; font-weight:700; color:#111827; margin-bottom:4px;">Contract Information</h1>
                <p class="text-[13px] text-gray-500">Details of the current contract with {{ $supplier['name'] }}.</p>
            </div>

            {{-- Top Row: Details (Left) + Document/Scope (Right) --}}
            <div class="grid grid-cols-2 gap-5">
                {{-- Contract Details --}}
                <div class="card">
                    <h2 class="card-title">Contract Details</h2>
                    <dl>
                        <div class="info-row">
                            <dt>Contract Start</dt>
                            <dd>{{ $supplier['contract']['start'] }}</dd>
                        </div>
                        <div class="info-row">
                            <dt>Contract End</dt>
                            <dd>{{ $supplier['contract']['end'] }}</dd>
                        </div>
                        <div class="info-row">
                            <dt>Contract Duration</dt>
                            <dd>{{ $supplier['contract']['duration'] }}</dd>
                        </div>
                        <div class="info-row">
                            <dt>Days Remaining</dt>
                            <dd>{{ $supplier['contract']['days_remaining'] }}</dd>
                        </div>
                        <div class="info-row">
                            <dt>Payment Terms</dt>
                            <dd>{{ $supplier['contract']['payment_terms'] }}</dd>
                        </div>
                        <div class="info-row !border-0">
                            <dt>Auto-renewal</dt>
                            <dd>{{ $supplier['contract']['auto_renewal'] === 'Yes' ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Document & Scope --}}
                <div class="flex flex-col gap-5">
                    {{-- Document --}}
                    <div class="card">
                        <h2 class="card-title">Contract Document</h2>
                        <div class="flex items-center gap-3.5 border border-gray-200 rounded-lg p-3.5 bg-gray-50/50">
                            <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-semibold text-gray-800 truncate">{{ $supplier['contract']['document'] }}</div>
                                <div class="text-[11px] text-gray-400 font-medium uppercase mt-0.5">PDF • {{ $supplier['contract']['document_size'] }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Scope --}}
                    <div class="card flex-1">
                        <h2 class="card-title">Scope of Supply</h2>
                        <ul class="text-[13px] text-gray-600 space-y-2 list-disc pl-4">
                            @foreach ($supplier['contract']['scope'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Bottom Row: Contract History --}}
            <div class="card !p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-[15px] font-bold text-gray-900">Contract History</h2>
                </div>
                <table class="fig-table">
                    <thead>
                        <tr>
                            <th style="padding-left:20px">Date</th>
                            <th>Action</th>
                            <th>Performed By</th>
                            <th style="padding-right:20px">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplier['contract']['history'] as $h)
                            <tr>
                                <td style="padding-left:20px; font-size:13px; color:#374151;">
                                    {{ $h['date'] ? \Illuminate\Support\Carbon::parse($h['date'])->format('M d, Y') : '—' }}
                                </td>
                                <td style="font-size:13px; color:#111827; font-weight:500;">
                                    {{ $h['action'] }}
                                </td>
                                <td style="font-size:13px; color:#374151;">
                                    {{ $h['by'] }}
                                </td>
                                <td style="padding-right:20px; font-size:13px; color:#6B7280;">
                                    {{ $h['remarks'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
@endsection
