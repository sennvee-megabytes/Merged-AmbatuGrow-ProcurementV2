@extends('layouts.master')

@section('title', 'Procurement Dashboard')

@section('content')
@php
    $moduleCards = [
        [
            'title' => 'Goods Receipt & Invoice Matching',
            'route' => 'matching.index',
            'description' => 'Review PO, GRN, and invoice matching in one operational view.',
            'accent' => 'from-emerald-500 to-emerald-700',
            'stat' => '10 open matches',
        ],
        [
            'title' => 'Order Management',
            'route' => 'procurement.home',
            'description' => 'Track purchase orders, create new POs, and monitor spend.',
            'accent' => 'from-sky-500 to-blue-700',
            'stat' => '4 active POs',
        ],
        [
            'title' => 'Purchase & Requisition',
            'route' => 'approvals.index',
            'description' => 'Create requisitions and move them through approvals.',
            'accent' => 'from-amber-500 to-orange-700',
            'stat' => '3 pending approvals',
        ],
        [
            'title' => 'Supplier Management',
            'route' => 'suppliers.dashboard',
            'description' => 'Manage suppliers, contracts, performance, and risk status.',
            'accent' => 'from-violet-500 to-indigo-700',
            'stat' => '4 supplier views',
        ],
    ];

    $statusRows = [
        ['label' => 'Unified modules online', 'value' => '4 / 4'],
        ['label' => 'Live route groups', 'value' => '4'],
        ['label' => 'Shared layout shell', 'value' => 'Enabled'],
        ['label' => 'Module entrypoints', 'value' => 'Wired'],
    ];
@endphp

<div class="space-y-6">
    <section class="rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-brand text-white p-8 shadow-soft overflow-hidden relative">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at top right, rgba(255,255,255,0.22), transparent 30%), radial-gradient(circle at bottom left, rgba(16,185,129,0.25), transparent 28%);"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <div class="text-xs uppercase tracking-[0.28em] text-white/55">Master Dashboard</div>
                <h2 class="mt-3 text-3xl lg:text-5xl font-black tracking-tight">Unified Procurement Control Center</h2>
                <p class="mt-4 text-white/75 text-base lg:text-lg leading-7">
                    One landing page for all four folder-based modules. Use this hub to jump into matching, orders, requisitions, and supplier management without leaving the shared shell.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:min-w-[320px]">
                @foreach ($statusRows as $status)
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                        <div class="text-[11px] uppercase tracking-[0.2em] text-white/45">{{ $status['label'] }}</div>
                        <div class="mt-1 text-lg font-bold">{{ $status['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($moduleCards as $card)
            <a href="{{ route($card['route']) }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-soft transition-shadow">
                <div class="flex items-center justify-between gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br {{ $card['accent'] }} text-white flex items-center justify-center font-black shadow-lg">
                        {{ strtoupper(substr($card['title'], 0, 2)) }}
                    </div>
                    <span class="text-xs font-semibold text-slate-500 group-hover:text-slate-700">Open module</span>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-950 leading-tight">{{ $card['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-6">{{ $card['description'] }}</p>
                <div class="mt-5 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ $card['stat'] }}
                </div>
            </a>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-400">Quick Entry</div>
                    <h3 class="mt-1 text-xl font-bold text-slate-950">Jump straight into a module</h3>
                </div>
                <a href="{{ route('matching.index') }}" class="rounded-full bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Open Matching</a>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('procurement.create') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-slate-100">
                    <div class="text-sm font-semibold text-slate-900">Create Purchase Order</div>
                    <div class="mt-1 text-sm text-slate-500">Start a new PO in Order Management.</div>
                </a>
                <a href="{{ route('requisitions.create') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-slate-100">
                    <div class="text-sm font-semibold text-slate-900">Create Requisition</div>
                    <div class="mt-1 text-sm text-slate-500">Draft a new PR and route it for approval.</div>
                </a>
                <a href="{{ route('suppliers.dashboard') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-slate-100">
                    <div class="text-sm font-semibold text-slate-900">Supplier Dashboard</div>
                    <div class="mt-1 text-sm text-slate-500">Open supplier KPIs and management tools.</div>
                </a>
                <a href="{{ route('matching.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-slate-100">
                    <div class="text-sm font-semibold text-slate-900">Matching Workspace</div>
                    <div class="mt-1 text-sm text-slate-500">Review GRN and invoice matching exceptions.</div>
                </a>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-xs uppercase tracking-[0.24em] text-slate-400">Navigation</div>
            <h3 class="mt-1 text-xl font-bold text-slate-950">Module shortcuts</h3>

            <div class="mt-5 space-y-3">
                <a href="{{ route('matching.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
                    <span>Goods Receipt & Invoice Matching</span>
                    <span class="text-slate-400">→</span>
                </a>
                <a href="{{ route('procurement.home') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
                    <span>Order Management</span>
                    <span class="text-slate-400">→</span>
                </a>
                <a href="{{ route('approvals.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
                    <span>Purchase & Requisition</span>
                    <span class="text-slate-400">→</span>
                </a>
                <a href="{{ route('suppliers.dashboard') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
                    <span>Supplier Management</span>
                    <span class="text-slate-400">→</span>
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
