@extends('layouts.master')

@section('title', 'Active Suppliers')
@section('subtitle', 'Verified active vendor accounts')

@section('topbar-actions')
    <a href="{{ route('suppliers.create') }}" class="top-bar-btn bg-emerald-600/80 hover:bg-emerald-700/90 border-emerald-500/30 flex items-center gap-1">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Supplier</span>
    </a>
@endsection

@section('content')

    <div class="mb-4 flex items-center justify-between">
        <form method="GET" action="{{ route('suppliers.active') }}" class="search-box max-w-md w-full">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
            <input name="q" type="text" value="{{ request('q') }}" placeholder="Search active suppliers...">
        </form>
        <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-200">
            Total Active: {{ $suppliers->total() }}
        </span>
    </div>

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
                        <span class="stars text-amber-500">
                            @for ($i = 1; $i <= 5; $i++) {{ $i <= round($s['rating']) ? '★' : '☆' }} @endfor
                        </span>
                        <span class="text-[13px] font-bold text-slate-700 ml-1">{{ $s['rating'] }}</span>
                    </td>
                    <td class="bg-white"><span class="badge-active"><span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Active</span></td>
                    <td class="text-[13px] text-slate-500 bg-white">{{ $s['last_transaction'] }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="bg-white"><div class="empty-state text-center py-12"><p class="text-sm font-semibold text-gray-400">No active suppliers found</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100 bg-white">
            {{ $suppliers->links() }}
        </div>
    </div>
@endsection
