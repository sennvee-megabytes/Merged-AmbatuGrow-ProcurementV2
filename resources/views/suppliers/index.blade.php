@extends('layouts.master')

@section('title', 'Suppliers List')
@section('subtitle', 'All registered agricultural suppliers')

@section('content')

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Supplier Directory</h1>
            <p class="text-xs text-slate-500">Total registered suppliers: <strong class="text-slate-800">{{ $suppliers->total() }}</strong></p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            {{-- Search Form --}}
            <form method="GET" action="{{ route('suppliers.index') }}" class="w-full sm:w-auto">
                <div class="search-box">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                    <input name="q" type="text" value="{{ request('q') }}" placeholder="Search supplier name, ID, or product...">
                    <button type="submit" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                    </button>
                </div>
            </form>

            {{-- Add Supplier --}}
            <a href="{{ route('suppliers.create') }}" class="btn-primary shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Supplier
            </a>
        </div>
    </div>

    @if (session('status'))
    <div class="mb-4 flex items-center gap-3 bg-green-50 text-green-800 border border-green-200 px-4 py-3 rounded-lg text-sm font-medium">
        <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('status') }}
    </div>
    @endif

    {{-- Table Card (White background, Crisp Dark Text) --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <table class="fig-table bg-white">
            <thead>
                <tr class="bg-white border-b border-gray-200">
                    <th style="padding-left:20px; background:#ffffff; color:#6B7280;">Supplier</th>
                    <th style="background:#ffffff; color:#6B7280;">Products</th>
                    <th style="background:#ffffff; color:#6B7280;">Location</th>
                    <th style="background:#ffffff; color:#6B7280;">Rating</th>
                    <th style="background:#ffffff; color:#6B7280;">Status</th>
                    <th style="background:#ffffff; color:#6B7280;">Last Transaction</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @forelse ($suppliers as $s)
                <tr class="cursor-pointer hover:bg-slate-50 transition-colors border-b border-gray-100 bg-white" onclick="window.location='{{ route('suppliers.show', $s['slug']) }}'">
                    <td style="padding-left:20px" class="bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs shrink-0">
                                {{ strtoupper(substr($s['supplier_name'] ?? $s['name'], 0, 1)) }}
                            </div>
                            <div>
                                <div class="supplier-name font-bold text-slate-900">{{ $s['supplier_name'] ?? $s['name'] }}</div>
                                <div class="supplier-id text-xs text-slate-500">{{ $s['supplier_id'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-slate-700 text-[13px] font-medium bg-white">{{ $s['products_list'] }}</td>
                    <td class="bg-white">
                        <span class="flex items-center gap-1.5 text-[13px] text-slate-600">
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            {{ $s['location'] }}
                        </span>
                    </td>
                    <td class="bg-white">
                        <div class="flex items-center gap-2">
                            <span class="stars text-amber-500">
                                @for ($i = 1; $i <= 5; $i++)
                                    {{ $i <= round($s['rating']) ? '★' : '☆' }}
                                @endfor
                            </span>
                            <span class="text-[13px] font-bold text-slate-700">{{ $s['rating'] }}</span>
                        </div>
                    </td>
                    <td class="bg-white">
                        @if ($s['status'] === 'Active')
                            <span class="badge-active"><span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Active</span>
                        @elseif ($s['status'] === 'Blacklisted' || $s['status'] === 'Blocked')
                            <span class="badge-blacklisted"><span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Blacklisted</span>
                        @else
                            <span class="badge-pending"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>Pending</span>
                        @endif
                    </td>
                    <td class="text-[13px] text-slate-500 bg-white">{{ $s['last_transaction'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="bg-white">
                        <div class="empty-state text-center py-12">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p class="text-sm font-semibold text-gray-400">No suppliers found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Dynamic Laravel Pagination --}}
        <div class="px-5 py-4 border-t border-gray-100 bg-white">
            {{ $suppliers->links() }}
        </div>
    </div>
@endsection
