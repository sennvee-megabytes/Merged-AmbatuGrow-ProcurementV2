@extends('layouts.master')

@section('title', 'Supplier Profile')
@section('subtitle', 'Contractual agreements and active duration')

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

    <div class="w-full flex flex-col gap-5">
            <div>
                <h1 style="font-size:24px; font-weight:700; color:#111827; margin-bottom:4px;">Contract Information</h1>
                <p class="text-[13px] text-gray-500">Details of the current contract with {{ $supplier['name'] }}.</p>
            </div>

            {{-- Top Row: Details (Left) + Document/Scope (Right) --}}
            <div class="grid grid-cols-2 gap-5">
                {{-- Contract Details Form --}}
                <div class="card">
                    <h2 class="card-title">Contract Details</h2>

                    @if (session('status'))
                        <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('suppliers.updateContract', $supplier['slug']) }}" class="flex flex-col gap-4">
                        @csrf
                        <div>
                            <label class="form-label">Contract Start <span class="text-red-500">*</span></label>
                            <input type="date" name="contract_start" id="contract_start" 
                                value="{{ old('contract_start', !empty($supplier['contract_start']) ? \Illuminate\Support\Carbon::parse($supplier['contract_start'])->format('Y-m-d') : (!empty($supplier['contract']['start']) ? \Illuminate\Support\Carbon::parse($supplier['contract']['start'])->format('Y-m-d') : '')) }}" 
                                class="form-input @error('contract_start') !border-red-500 @enderror" required>
                            @error('contract_start')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Contract End <span class="text-red-500">*</span></label>
                            <input type="date" name="contract_end" id="contract_end" 
                                value="{{ old('contract_end', !empty($supplier['contract_end']) ? \Illuminate\Support\Carbon::parse($supplier['contract_end'])->format('Y-m-d') : (!empty($supplier['contract']['end']) ? \Illuminate\Support\Carbon::parse($supplier['contract']['end'])->format('Y-m-d') : '')) }}" 
                                class="form-input @error('contract_end') !border-red-500 @enderror" required>
                            @error('contract_end')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Contract Duration</label>
                            <input type="text" name="contract_duration" id="contract_duration" 
                                value="{{ old('contract_duration', $supplier['contract_duration'] ?? $supplier['contract']['duration'] ?? '') }}" 
                                readonly class="form-input bg-slate-100 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="form-label">Days Remaining</label>
                            <input type="text" value="{{ $supplier['contract']['days_remaining'] ?? '—' }}" 
                                readonly class="form-input bg-slate-100 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="form-label">Payment Terms</label>
                            @php
                                $currentTerms = old('payment_terms', $supplier['payment_terms'] ?? $supplier['contract']['payment_terms'] ?? 'Net 30');
                            @endphp
                            <select name="payment_terms" class="form-input @error('payment_terms') !border-red-500 @enderror">
                                @foreach(['COD', 'Net 15', 'Net 30', 'Net 60', 'Net 90', 'Advance Payment', 'Installment'] as $termOption)
                                    <option value="{{ $termOption }}" {{ $currentTerms === $termOption ? 'selected' : '' }}>
                                        {{ $termOption }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_terms')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="btn-primary text-xs py-2 px-4">Update Contract</button>
                        </div>
                    </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startInput = document.getElementById('contract_start');
            const endInput = document.getElementById('contract_end');
            const durationInput = document.getElementById('contract_duration');

            function calculateDuration() {
                if (!startInput || !endInput || !durationInput) return;

                const startVal = startInput.value;
                const endVal = endInput.value;

                if (startVal && endVal) {
                    const startDate = new Date(startVal);
                    const endDate = new Date(endVal);

                    if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                        const diffTime = endDate.getTime() - startDate.getTime();
                        const diffDays = Math.round(diffTime / (1000 * 3600 * 24));

                        if (diffDays >= 0) {
                            durationInput.value = diffDays + ' days';
                        } else {
                            durationInput.value = '0 days';
                        }
                    }
                }
            }

            if (startInput && endInput) {
                startInput.addEventListener('change', calculateDuration);
                startInput.addEventListener('input', calculateDuration);
                endInput.addEventListener('change', calculateDuration);
                endInput.addEventListener('input', calculateDuration);

                calculateDuration();
            }
        });
    </script>
@endsection
