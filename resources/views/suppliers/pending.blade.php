@extends('layouts.master')

@section('title', 'Pending Verification')
@section('subtitle', 'Supplier accounts awaiting verification review')

@section('content')

    <div class="mb-4 flex items-center justify-between">
        <form method="GET" action="{{ route('suppliers.pending') }}" class="search-box max-w-md w-full">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
            <input name="q" type="text" value="{{ request('q') }}" placeholder="Search pending suppliers...">
        </form>
        <span class="text-xs font-bold text-amber-800 bg-amber-50 px-3 py-1.5 rounded-full border border-amber-200">
            Total Pending: {{ $suppliers->total() }}
        </span>
    </div>

    @if ($suppliers->total() > 0)
    <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-[13px] font-medium mb-4">
        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        {{ $suppliers->total() }} supplier(s) are awaiting verification review.
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <table class="fig-table bg-white">
            <thead>
                <tr class="bg-white border-b border-gray-200">
                    <th style="padding-left:20px; background:#ffffff; color:#6B7280;">Supplier</th>
                    <th style="background:#ffffff; color:#6B7280;">Products</th>
                    <th style="background:#ffffff; color:#6B7280;">Location</th>
                    <th style="background:#ffffff; color:#6B7280;">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @forelse ($suppliers as $s)
                <tr class="cursor-pointer hover:bg-slate-50 transition-colors border-b border-gray-100 bg-white" onclick="window.location='{{ route('suppliers.show', $s['slug']) }}'">
                    <td style="padding-left:20px" class="bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 font-bold flex items-center justify-center text-xs shrink-0">
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
                    <td class="bg-white"><span class="badge-pending"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>Pending</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="bg-white">
                        <div class="empty-state text-center py-12">
                            <svg class="w-10 h-10 text-green-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-semibold text-gray-400">No pending suppliers — all clear!</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100 bg-white">
            {{ $suppliers->links() }}
        </div>
    </div>
@endsection
