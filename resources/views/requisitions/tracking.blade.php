@extends('layouts.master')

@section('title', 'Tracking')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-lg font-bold text-slate-800">My Requisitions</h1>
        <p class="text-xs text-slate-400">Track the status of requisitions you've submitted</p>
    </div>
    <div class="flex items-center gap-2">
        <button @click="$dispatch('open-requisition-modal')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-md transition flex items-center gap-1.5">
            <i class="fa-solid fa-file-invoice"></i>
            <span>Create Requisition</span>
        </button>
    </div>
</div>

<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs text-slate-400">
            <tr>
                <th class="text-left font-medium px-5 py-3">Request ID</th>
                <th class="text-left font-medium px-5 py-3">Title</th>
                <th class="text-left font-medium px-5 py-3">Total Amount</th>
                <th class="text-left font-medium px-5 py-3">Status</th>
                <th class="text-left font-medium px-5 py-3">Current Step</th>
                <th class="text-left font-medium px-5 py-3">Submitted</th>
                <th class="text-left font-medium px-5 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($requisitions as $req)
                @php $current = $req->approvalSteps->firstWhere('status', 'pending'); @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-700">{{ $req->code }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $req->title }}</td>
                    <td class="px-5 py-3 text-slate-600">₱{{ number_format($req->total, 2) }}</td>
                    <td class="px-5 py-3">
                        @php
                            $badge = match($req->status) {
                                'approved' => 'bg-emerald-50 text-emerald-600',
                                'rejected' => 'bg-red-50 text-red-600',
                                'pending_approval' => 'bg-amber-50 text-amber-600',
                                default => 'bg-slate-100 text-slate-500',
                            };
                        @endphp
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $badge }}">{{ $req->statusLabel() }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-500">{{ $current?->label ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $req->submitted_at?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if ($req->status === 'draft')
                            <a href="{{ route('requisitions.route', $req) }}" class="text-blue-600 text-xs font-medium hover:underline">Route for approval</a>
                        @else
                            <a href="{{ route('approvals.index', ['requisition' => $req->id]) }}" class="text-blue-600 text-xs font-medium hover:underline">View Details</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate-400">No requisitions yet. Create your first one!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
