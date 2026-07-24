@extends('layouts.master')

@section('title', 'Approvals')
@section('subtitle', 'PR & PO Approval Queue')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Override body styling imported from app.css to restore master layout compatibility */
        body {
            display: block !important;
            height: auto !important;
            overflow: auto !important;
        }

        /* Flat CSS for Figma Tables to prevent nesting compilation issues */
        .fig-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 14px !important;
        }
        .fig-table th {
            text-align: left !important;
            padding: 12px 16px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #4b5563 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            border-bottom: 1px solid #e5e7eb !important;
            background: #ffffff !important;
            white-space: nowrap !important;
        }
        .fig-table td {
            padding: 14px 16px !important;
            border-bottom: 1px solid #f3f4f6 !important;
            color: #111827 !important;
            vertical-align: middle !important;
        }
        .fig-table tr:hover td {
            background-color: #fafafa !important;
        }
    </style>
@endpush

@section('topbar-actions')
    <div class="flex items-center gap-2">
        <button @click="$dispatch('open-requisition-modal')" class="top-bar-btn bg-emerald-600/80 hover:bg-emerald-700/90 border-emerald-500/30">
            <i data-lucide="file-text" class="w-4 h-4 mr-1"></i>
            <span>Create Requisition</span>
        </button>
    </div>
@endsection

@section('content')
<div x-data="{ tab: 'pr' }">

    {{-- Stat cards --}}
    <div class="kpi-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr)) !important; margin-bottom: 24px; width: 100%; gap: 16px;">
        <!-- Card 1 -->
        <div class="kpi-card border-l-4 border-blue-500 bg-white" style="min-height: 130px; display: flex; flex-direction: column; justify-content: space-between; padding: 18px 20px;">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: auto;">
                <div class="kpi-icon-wrapper" style="width: 40px; height: 40px; border-radius: 8px; background-color: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="clock" style="width:20px;height:20px;"></i>
                </div>
            </div>
            <div class="kpi-body" style="display: flex; flex-direction: column; gap: 2px; margin-top: 12px;">
                <div class="kpi-value" style="font-size: 2.25rem; font-weight: 800; line-height: 1; color: #111827;">{{ $stats['pending_count'] }}</div>
                <div class="kpi-title" style="font-size: 0.75rem; font-weight: 500; color: #6b7280; margin: 0; line-height: 1.2;">Pending Approvals</div>
                <div class="kpi-subtext" style="font-size: 0.65rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; tracking-wider; margin: 0; line-height: 1.2;">Awaiting your action</div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="kpi-card border-l-4 border-emerald-500 bg-white" style="min-height: 130px; display: flex; flex-direction: column; justify-content: space-between; padding: 18px 20px;">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: auto;">
                <div class="kpi-icon-wrapper" style="width: 40px; height: 40px; border-radius: 8px; background-color: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="dollar-sign" style="width:20px;height:20px;"></i>
                </div>
            </div>
            <div class="kpi-body" style="display: flex; flex-direction: column; gap: 2px; margin-top: 12px;">
                <div class="kpi-value" style="font-size: 2.25rem; font-weight: 800; line-height: 1; color: #111827;">₱{{ number_format($stats['value_awaiting'], 0) }}</div>
                <div class="kpi-title" style="font-size: 0.75rem; font-weight: 500; color: #6b7280; margin: 0; line-height: 1.2;">Value Awaiting Approval</div>
                <div class="kpi-subtext" style="font-size: 0.65rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; tracking-wider; margin: 0; line-height: 1.2;">Across your queue</div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="kpi-card border-l-4 border-amber-500 bg-white" style="min-height: 130px; display: flex; flex-direction: column; justify-content: space-between; padding: 18px 20px;">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: auto;">
                <div class="kpi-icon-wrapper" style="width: 40px; height: 40px; border-radius: 8px; background-color: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="shield-check" style="width:20px;height:20px;"></i>
                </div>
            </div>
            <div class="kpi-body" style="display: flex; flex-direction: column; gap: 2px; margin-top: 12px;">
                <div class="kpi-value" style="font-size: 1.25rem; font-weight: 800; line-height: 1.2; color: #111827; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">{{ auth()->user()->roleLabel() }}</div>
                <div class="kpi-title" style="font-size: 0.75rem; font-weight: 500; color: #6b7280; margin: 0; line-height: 1.2;">Your Role</div>
                <div class="kpi-subtext" style="font-size: 0.65rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; tracking-wider; margin: 0; line-height: 1.2;">{{ auth()->user()->department }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-5 items-start">

        {{-- Left: queue table --}}
        <div class="unified-card !p-0 overflow-hidden font-sans bg-white">
            <div class="flex items-center gap-6 px-6 pt-5 border-b border-slate-100 text-sm">
                <button @click="tab = 'pr'" :class="tab === 'pr' ? 'text-green-800 border-green-800 font-bold' : 'text-slate-400 border-transparent'"
                        class="pb-3.5 border-b-2 transition-all">Purchase Requisitions ({{ $pendingForMe->count() }})</button>
                <button @click="tab = 'history'" :class="tab === 'history' ? 'text-green-800 border-green-800 font-bold' : 'text-slate-400 border-transparent'"
                        class="pb-3.5 border-b-2 transition-all">History ({{ $history->count() }})</button>
            </div>

            <div x-show="tab === 'pr'">
                <table class="fig-table">
                    <thead>
                        <tr>
                            <th style="padding-left:24px;">Request ID</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th>Total Amount</th>
                            <th>Urgency</th>
                            <th style="padding-right:24px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingForMe as $req)
                            @php
                                $urgencyBadge = match($req->urgency) {
                                    'High' => 'bg-red-50 text-red-700 border-red-100',
                                    'Low' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    default => 'bg-amber-50 text-amber-700 border-amber-100',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $selected && $selected->id === $req->id ? 'bg-green-50/20' : '' }}">
                                <td style="padding-left:24px;" class="py-3.5 font-bold text-slate-800">{{ $req->code }}</td>
                                <td class="py-3.5 text-slate-655 font-medium">{{ $req->requestor->name }}</td>
                                <td class="py-3.5 text-slate-600">{{ $req->department }}</td>
                                <td class="py-3.5 text-slate-800 font-bold">₱{{ number_format($req->total, 2) }}</td>
                                <td class="py-3.5">
                                    <span class="text-[10px] font-black uppercase tracking-wide px-2.5 py-1 rounded-full border {{ $urgencyBadge }}">{{ $req->urgency }}</span>
                                </td>
                                <td style="padding-right:24px; text-align:right;" class="py-3.5">
                                    <a href="{{ route('approvals.index', ['requisition' => $req->id]) }}"
                                       class="text-[11px] font-bold px-4 py-2 rounded-full bg-green-800 text-white hover:bg-green-900 transition-colors shadow-sm">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    <span class="text-xs font-semibold">Nothing waiting on your approval right now.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div x-show="tab === 'history'" x-cloak>
                <table class="fig-table">
                    <thead>
                        <tr>
                            <th style="padding-left:24px;">Request ID</th>
                            <th>Requester</th>
                            <th>Total Amount</th>
                            <th style="padding-right:24px; text-align:right;">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $req)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td style="padding-left:24px;" class="py-3.5 font-bold text-slate-800">{{ $req->code }}</td>
                                <td class="py-3.5 text-slate-655 font-medium">{{ $req->requestor->name }}</td>
                                <td class="py-3.5 text-slate-800 font-bold">₱{{ number_format($req->total, 2) }}</td>
                                <td style="padding-right:24px; text-align:right;" class="py-3.5">
                                    <span class="text-[10px] font-black uppercase tracking-wide px-2.5 py-1 rounded-full border {{ $req->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100' }}">
                                        {{ $req->statusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    <span class="text-xs font-semibold">No decisions recorded yet.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right: detail panel --}}
        <div class="unified-card !p-6 font-sans bg-white">
            @if ($selected)
                @php 
                    $currentStep = $selected->currentStep();
                    $userStep = $selected->approvalSteps->where('approver_id', auth()->id())->where('status', 'pending')->first();
                    $canAct = $userStep && $userStep->canBeActedOnBy(auth()->user(), $selected); 
                @endphp

                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full border {{ $selected->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : ($selected->status === 'rejected' ? 'bg-red-50 text-red-700 border-red-100' : 'bg-amber-50 text-amber-700 border-amber-100') }}">{{ $selected->statusLabel() }}</span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">{{ $selected->submitted_at?->format('M d, Y') }}</span>
                </div>
                
                <h3 class="text-sm font-black text-slate-800">{{ $selected->code }}</h3>
                <p class="text-xs text-slate-550 mt-1 leading-relaxed">{{ $selected->title }}</p>
                <div class="mt-2.5 bg-slate-50/50 border border-slate-100 rounded-xl p-3">
                    <p class="text-xs font-bold text-slate-800">{{ $selected->requestor->name }}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">Department: {{ $selected->department }}</p>
                </div>

                <div class="mt-5">
                    <p class="text-xs font-black text-slate-700 uppercase tracking-wide mb-3">Line items</p>
                    <div class="bg-slate-50/50 border border-slate-100 rounded-xl overflow-hidden divide-y divide-slate-100/60">
                        @foreach ($selected->items as $item)
                            <div class="p-3 flex justify-between items-start text-xs">
                                <div>
                                    <div class="font-bold text-slate-800">{{ $item->name }}</div>
                                    <div class="text-slate-400 mt-0.5">Qty: {{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}</div>
                                </div>
                                <span class="font-black text-slate-700">₱{{ number_format($item->total, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-green-800/5 border border-green-800/10 rounded-xl p-4 mt-4 flex justify-between items-center text-sm font-sans">
                    <span class="font-bold text-green-900">Grand Total</span>
                    <span class="font-black text-green-950 text-lg">₱{{ number_format($selected->total, 2) }}</span>
                </div>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    <p class="text-xs font-black text-slate-700 uppercase tracking-wide mb-3">Approval Workflow</p>
                    <div class="space-y-3 relative before:absolute before:inset-y-1 before:left-3 before:w-[2px] before:bg-slate-100">
                        @foreach ($selected->approvalSteps as $s)
                            @php
                                $isCurrent = $currentStep && $currentStep->id === $s->id;
                                $stepBg = match($s->status) {
                                    'approved' => 'bg-emerald-500 ring-4 ring-emerald-50',
                                    'rejected' => 'bg-red-500 ring-4 ring-red-50',
                                    default => ($isCurrent ? 'bg-amber-500 ring-4 ring-amber-50' : 'bg-slate-200'),
                                };
                                $stColor = match($s->status) {
                                    'approved' => 'text-emerald-700 font-bold',
                                    'rejected' => 'text-red-700 font-bold',
                                    default => 'text-amber-700 font-medium',
                                };
                            @endphp
                            <div class="flex items-center gap-3 relative z-10 text-xs">
                                <div class="w-6 h-6 rounded-full {{ $stepBg }} flex items-center justify-center shrink-0">
                                    @if ($s->status === 'approved')
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-white"></i>
                                    @elseif ($s->status === 'rejected')
                                        <i data-lucide="x" class="w-3.5 h-3.5 text-white"></i>
                                    @else
                                        <span class="text-[9px] font-bold text-slate-600 {{ $isCurrent ? 'text-white' : '' }}">{{ $s->step_order }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-800 {{ $isCurrent ? 'text-[#235c2b]' : '' }}">{{ $s->approver?->name ?? 'Unassigned' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Step {{ $s->step_order }} &middot; <span class="{{ $stColor }}">{{ ucfirst($s->status) }}</span></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    @if ($canAct)
                        <form method="POST" action="{{ route('approvals.act', $selected) }}" class="space-y-3.5" x-data="{ decision: '', submitted: false }" @submit="submitted = true">
                            @csrf
                            <input type="hidden" name="decision" x-model="decision">
                            
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wide mb-1.5">Remarks / Comments</label>
                                <textarea name="comment" rows="2" placeholder="Provide any comments or instructions..."
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 bg-white text-slate-800 placeholder-slate-400"></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wide mb-1.5">Delegate Authority (Optional)</label>
                                <select name="delegate_to" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600">
                                    <option value="">-- Select Delegate --</option>
                                    @foreach ($delegates as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->roleLabel() }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex gap-2.5 pt-1.5">
                                <button type="submit" @click="decision = 'reject'" :disabled="submitted" class="flex-1 px-4 py-2.5 text-xs font-bold rounded-xl bg-red-50 text-red-700 border border-red-100 hover:bg-red-100 transition-colors disabled:opacity-50 uppercase tracking-wide">Reject</button>
                                <button type="submit" @click="decision = 'delegate'" :disabled="submitted" class="flex-1 px-4 py-2.5 text-xs font-bold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors disabled:opacity-50 uppercase tracking-wide">Delegate</button>
                                <button type="submit" @click="decision = 'approve'" :disabled="submitted" class="flex-1 px-4 py-2.5 text-xs font-bold rounded-xl bg-green-800 text-white hover:bg-green-950 transition-colors disabled:opacity-50 uppercase tracking-wide shadow-sm">Approve</button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-xl bg-slate-50/80 border border-slate-100 px-4 py-3 flex gap-2.5 items-start text-xs text-slate-500 leading-relaxed">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                            <span>You cannot act on this requisition right now (it's not your turn or you are not the assigned approver).</span>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-16 text-slate-400">
                    <i data-lucide="folder-open" class="w-10 h-10 mx-auto text-slate-300 mb-3"></i>
                    <p class="text-xs font-semibold">Select a requisition from the queue to view detailed info.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
