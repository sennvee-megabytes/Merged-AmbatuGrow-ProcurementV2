@extends('layouts.master')

@section('title', 'Purchase Requisition Receipt')
@section('subtitle', 'Submission Proof & Approval Tracker')

@section('topbar-actions')
    <div class="flex items-center gap-2">
        <button onclick="window.print()" class="top-bar-btn bg-blue-600/80 hover:bg-blue-700/90 border-blue-500/30">
            <i data-lucide="printer" class="w-4 h-4 mr-1"></i>
            <span>Print Receipt</span>
        </button>
        <a href="{{ route('requisitions.receipt_pdf', $requisition) }}" target="_blank" class="top-bar-btn bg-emerald-600/80 hover:bg-emerald-700/90 border-emerald-500/30">
            <i data-lucide="download" class="w-4 h-4 mr-1"></i>
            <span>Download PDF</span>
        </a>
    </div>
@endsection

@section('content')
<style>
    @media print {
        header.top-bar-unified, aside, .topbar-actions, .no-print {
            display: none !important;
        }
        body {
            background: #fff !important;
            padding: 0 !important;
        }
        .receipt-card {
            box-shadow: none !important;
            border: none !important;
            width: 100% !important;
        }
    }
</style>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Navigation / Back button -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('requisitions.tracking') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Tracking
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-200 text-slate-700 hover:bg-slate-300 transition flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Receipt
            </button>
            <a href="{{ route('requisitions.receipt_pdf', $requisition) }}" target="_blank" class="px-4 py-2 text-xs font-bold rounded-xl bg-[#235c2b] text-white hover:bg-[#163f2b] transition flex items-center gap-1.5 shadow-sm">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Printable Receipt Container -->
    <div class="receipt-card bg-white border border-slate-200 rounded-2xl p-8 shadow-sm space-y-8 font-sans">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start border-b border-slate-200 pb-6 gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full overflow-hidden border border-slate-200 bg-white p-1 shadow-sm flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ambatugrow" class="w-full h-full object-cover rounded-full">
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">AMBATUGROW</h1>
                    <p class="text-xs text-slate-500">Indang, Cavite</p>
                    <p class="text-[11px] text-slate-400">Phone: (000) 000-0000 | Fax: (111) 111-1111 | Website: www.ambatugrow.com</p>
                </div>
            </div>

            <div class="text-left md:text-right">
                <span class="inline-block px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full text-xs font-black uppercase tracking-wider mb-2">
                    {{ $requisition->statusLabel() }}
                </span>
                <h2 class="text-lg font-black text-slate-800 tracking-tight">PURCHASE REQUISITION RECEIPT</h2>
                <p class="text-xs font-bold text-emerald-800">{{ $requisition->code }}</p>
            </div>
        </div>

        <!-- Requisition Metadata Grid -->
        <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Submission Date & Time</span>
                <span class="font-bold text-slate-800">{{ $requisition->submitted_at?->format('M d, Y · h:i A') ?? $requisition->created_at->format('M d, Y · h:i A') }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Requester Name</span>
                <span class="font-bold text-slate-800">{{ $requisition->requestor->name }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Department</span>
                <span class="font-bold text-slate-800">{{ $requisition->department }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Recommended Supplier</span>
                <span class="font-bold text-emerald-800">{{ $requisition->recommendedSupplier->name ?? 'None Recommended' }}</span>
            </div>
            <div class="md:col-span-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Purpose of Request</span>
                <span class="font-medium text-slate-700 leading-relaxed">{{ $requisition->purpose ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Date Needed</span>
                <span class="font-bold text-slate-800">{{ optional($requisition->needed_by)->format('M d, Y') ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Priority</span>
                <span class="font-bold text-slate-800">{{ $requisition->urgency }}</span>
            </div>
        </div>

        <!-- Line Items Table -->
        <div>
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Requested Items</h3>
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-100/80 text-[10px] font-extrabold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Item Code / SKU</th>
                            <th class="px-4 py-3">Item Name</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3 text-center">Qty</th>
                            <th class="px-4 py-3 text-center">Unit</th>
                            <th class="px-4 py-3 text-right">Est. Unit Cost</th>
                            <th class="px-4 py-3 text-right">Est. Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($requisition->items as $item)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-semibold text-slate-600">{{ $item->sku ?? ('ITEM-' . sprintf('%05d', $item->id)) }}</td>
                                <td class="px-4 py-3 font-bold text-slate-800">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $item->justification ?? $item->name }}</td>
                                <td class="px-4 py-3 text-center font-bold text-slate-700">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}</td>
                                <td class="px-4 py-3 text-center text-slate-500 uppercase">{{ $item->unit }}</td>
                                <td class="px-4 py-3 text-right font-medium text-slate-700">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right font-black text-slate-800">₱{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Total Calculation Box -->
            <div class="flex justify-end mt-4">
                <div class="w-full md:w-64 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal:</span>
                        <span class="font-bold text-slate-800">₱{{ number_format($requisition->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Estimated Tax ({{ rtrim(rtrim($requisition->tax_rate, '0'), '.') }}%):</span>
                        <span class="font-bold text-slate-800">₱{{ number_format($requisition->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-black text-emerald-950 pt-2 border-t border-slate-200">
                        <span>Estimated Total:</span>
                        <span class="text-base text-[#1f5c3d]">₱{{ number_format($requisition->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3-Level Approval Workflow -->
        <div class="border-t border-slate-200 pt-6">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-4">Fixed 3-Level Approval Workflow</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $steps = $requisition->approvalSteps;
                    $approver1 = $steps->firstWhere('step_order', 1);
                    $approver2 = $steps->firstWhere('step_order', 2);
                    $approver3 = $steps->firstWhere('step_order', 3);
                @endphp

                <!-- Approver 1: Sarah Jenkins -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400">Level 1 · Project Manager</span>
                        @php
                            $st1 = $approver1?->status ?? 'pending';
                            $b1 = match($st1) {
                                'approved' => 'bg-emerald-100 text-emerald-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-amber-100 text-amber-800',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $b1 }}">{{ ucfirst($st1) }}</span>
                    </div>
                    <div class="font-bold text-slate-800 text-sm">Sarah Jenkins</div>
                    <div class="text-[10px] text-slate-500">Project Manager</div>
                </div>

                <!-- Approver 2: Michael Finn -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400">Level 2 · Finance Manager</span>
                        @php
                            $st2 = $approver2?->status ?? 'pending';
                            $b2 = match($st2) {
                                'approved' => 'bg-emerald-100 text-emerald-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-amber-100 text-amber-800',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $b2 }}">{{ ucfirst($st2) }}</span>
                    </div>
                    <div class="font-bold text-slate-800 text-sm">Michael Finn</div>
                    <div class="text-[10px] text-slate-500">Finance Manager</div>
                </div>

                <!-- Approver 3: Johny Papa -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400">Level 3 · Head</span>
                        @php
                            $st3 = $approver3?->status ?? 'pending';
                            $b3 = match($st3) {
                                'approved' => 'bg-emerald-100 text-emerald-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-amber-100 text-amber-800',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $b3 }}">{{ ucfirst($st3) }}</span>
                    </div>
                    <div class="font-bold text-slate-800 text-sm">Johny Papa</div>
                    <div class="text-[10px] text-slate-500">Director of Marketing / Head</div>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="border-t border-slate-200 pt-6 text-center text-xs text-slate-400 leading-relaxed">
            This receipt is proof of submission for your Purchase Requisition. It is <strong>NOT</strong> a Purchase Order.<br>
            If you have any questions regarding this requisition, please contact <strong>procurement@ambatugrow.com</strong>.
        </div>
    </div>
</div>
@endsection
