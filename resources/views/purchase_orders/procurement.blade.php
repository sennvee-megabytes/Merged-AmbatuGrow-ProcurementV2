@extends('layouts.procurement', [
    'pageTitle' => 'Procurement Landing',
    'workspaceTitle' => 'Procurement Landing',
    'workspaceSubtitle' => 'Purchase on the left, alerts on the right, and Create PO in a modal.',
])

@section('title', 'Order Management')
@section('subtitle', 'Purchase orders and procurement tracking')

@section('header_right')
@endsection

@section('content')
    <style>
        .dashboard-layout {
            display: grid;
            grid-template-columns: minmax(0, 4fr) minmax(300px, 1fr);
            gap: 24px;
            align-items: stretch;
        }

        .page-section {
            background: #fff;
            border: 1px solid #dfe5dc;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        /* Both panels always maintain consistent minimum height */
        #purchase.page-section,
        #sidenotif.page-section {
            min-height: 700px;
            align-self: stretch;
        }

        /* Sidebar inner body never scrolls */
        #sidenotif .section-body {
            overflow: visible !important;
        }

        .page-section-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            flex-wrap: wrap;
            padding: 22px 24px;
            border-bottom: 1px solid #e8ece7;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #165c32;
            margin-bottom: 8px;
        }

        .section-label::before {
            content: '';
            width: 28px;
            height: 3px;
            border-radius: 999px;
            background: #1e7d43;
        }

        .page-section-header h2 {
            margin: 0 0 6px;
            font-size: 26px;
            color: #0f1724;
            font-weight: 800;
        }

        .page-section-header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .section-body {
            padding: 18px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .stat-card {
            background: #f8faf8;
            border: 1px solid #e3e8e1;
            border-radius: 14px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 76px;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            letter-spacing: .04em;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #14213d;
        }

        .tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .tab {
            border: 1px solid #e3e8e1;
            background: #f6f7f8;
            color: #4b5563;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .tab.active {
            background: #eef7f0;
            color: #1e7d43;
            border-color: #d5e9d9;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            padding: 0 6px;
            margin-left: 6px;
            border-radius: 999px;
            background: rgba(255,255,255,.75);
            font-size: 11px;
        }

        .table-wrap {
            border: 1px solid #e4e8e2;
            border-radius: 14px;
            overflow-x: auto;
            overflow-y: auto;
            background: #fff;
            max-height: 420px;
            width: 100%;
            max-width: 100%;
        }

        .table-wrap table {
            width: auto !important;
            min-width: 100% !important;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: auto !important;
        }

        /* Ensure columns have adequate minimum widths to prevent squishing and overlaps */
        .table-wrap th:nth-child(1), .table-wrap td:nth-child(1) { min-width: 110px; } /* PO ID */
        .table-wrap th:nth-child(2), .table-wrap td:nth-child(2) { min-width: 100px; } /* Linked PR */
        .table-wrap th:nth-child(3), .table-wrap td:nth-child(3) { min-width: 180px; } /* Supplier */
        .table-wrap th:nth-child(4), .table-wrap td:nth-child(4) { min-width: 120px; } /* Date Issued */
        .table-wrap th:nth-child(5), .table-wrap td:nth-child(5) { min-width: 130px; } /* Expected Delivery */
        .table-wrap th:nth-child(6), .table-wrap td:nth-child(6) { min-width: 110px; } /* Subtotal */
        .table-wrap th:nth-child(7), .table-wrap td:nth-child(7) { min-width: 90px; }  /* VAT */
        .table-wrap th:nth-child(8), .table-wrap td:nth-child(8) { min-width: 110px; } /* Total */
        .table-wrap th:nth-child(9), .table-wrap td:nth-child(9) { min-width: 150px; } /* Status */
        .table-wrap th:nth-child(10), .table-wrap td:nth-child(10) { min-width: 140px; } /* Actions */

        .table-wrap thead th {
            text-align: left;
            padding: 14px 16px;
            color: #6b7280;
            font-size: 12px;
            background: #f8faf8;
            border-bottom: 1px solid #e8ece7;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-wrap tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #eef1ed;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-wrap tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-sent { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .badge-received { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-overdue { background: #b91c1c; color: #ffffff; border: 1px solid #b91c1c; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-partial { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-draft { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
        .badge-approved { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .badge-cancelled { background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; }

        .po-row-overdue {
            background-color: #fff8f8 !important;
        }

        .btn-outline-green {
            background: #fff;
            color: #1e7d43;
            border: 1px solid #1e7d43;
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            font-weight: 700;
        }
        .btn-outline-green:hover {
            background: #f4faf6;
        }

        .btn-outline-red {
            background: #fff;
            color: #dc2626;
            border: 1px solid #dc2626;
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            font-weight: 700;
        }
        .btn-outline-red:hover {
            background: #fff5f5;
        }

        .action-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn,
        .action-link,
        .chip-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #d8dfd4;
            background: #fff;
            color: #165c32;
            font-weight: 700;
            text-decoration: none;
            padding: 10px 14px;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-primary {
            background: #1e7d43;
            color: #fff;
            border-color: #1e7d43;
            padding: 12px 18px;
            box-shadow: 0 8px 20px rgba(30,125,67,0.18);
            font-size: 14px;
        }

        .btn-soft {
            background: #f4f5f7;
            color: #334155;
            border-color: #e2e8f0;
        }

        .btn-ghost {
            background: #eef7f0;
            color: #165c32;
            border-color: #d5e9d9;
        }

        .alerts-card {
            display: grid;
            gap: 16px;
        }

        .mini-card {
            border: 1px solid #e4e8e2;
            border-radius: 14px;
            padding: 18px;
            background: #fff;
        }

        .mini-card:target {
            outline: 2px solid #1e7d43;
            outline-offset: 2px;
        }

        .mini-title {
            font-size: 14px;
            font-weight: 800;
            color: #2f5d34;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .muted {
            color: #6b7280;
        }

        .log-list {
            display: grid;
            gap: 12px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .log-item {
            padding: 12px 14px;
            border: 1px solid #e4e8e2;
            border-radius: 12px;
            background: #fdfefe;
        }

        .log-item strong {
            display: block;
            margin-bottom: 4px;
            color: #14213d;
        }

        .overlay-card {
            width: min(980px, calc(100vw - 48px));
            max-height: calc(100vh - 48px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            z-index: 50;
        }

        .modal-backdrop:target {
            display: flex;
        }

        .modal {
            width: min(980px, calc(100vw - 48px));
            max-height: calc(100vh - 48px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .modal .drawer-header {
            flex-shrink: 0;
        }

        .modal .drawer-body {
            flex: 1;
            overflow: auto;
            min-height: 0;
        }

        .modal .drawer-footer {
            flex-shrink: 0;
        }

        @media (max-width: 1180px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .table-wrap tbody { max-height: 320px; }
        }

        @media (max-width: 720px) {
            .section-body,
            .page-section-header {
                padding-left: 18px;
                padding-right: 18px;
            }

        /* Dark Mode Overrides for Procurement Landing */
        .dark .page-section,
        .dark #purchase.page-section,
        .dark #sidenotif.page-section {
            background: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
        }

        .dark .page-section-header {
            border-bottom-color: #334155 !important;
        }

        .dark .page-section-header h2 { color: #f8fafc !important; }
        .dark .page-section-header p { color: #94a3b8 !important; }

        .dark .stat-card {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        .dark .stat-label { color: #94a3b8 !important; }
        .dark .stat-value { color: #f8fafc !important; }

        .dark .bg-white {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        .dark .tab {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        .dark .tab.active {
            background: rgba(30, 125, 67, 0.25) !important;
            border-color: #1e7d43 !important;
            color: #34d399 !important;
        }

        .dark .table-wrap {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        .dark .table-wrap thead th {
            background: #0f172a !important;
            color: #cbd5e1 !important;
            border-bottom-color: #334155 !important;
        }

        .dark .table-wrap tbody tr,
        .dark .table-wrap tbody td,
        .dark .unified-table tbody tr,
        .dark .unified-table tbody td,
        .dark .po-row,
        .dark .po-row td {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-bottom-color: #334155 !important;
        }

        .dark .table-wrap tbody tr:hover td,
        .dark .unified-table tbody tr:hover td,
        .dark .po-row:hover td {
            background-color: #26334d !important;
        }

        .dark .po-row-overdue td {
            background-color: rgba(239, 68, 68, 0.12) !important;
        }

        .dark .mini-card,
        .dark .chart-card,
        .dark .log-item {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        .dark .mini-title { color: #34d399 !important; }
        .dark .log-item strong { color: #f8fafc !important; }
        .dark .muted { color: #94a3b8 !important; }
        .dark .po-row td[style*="font-weight: 700"],
        .dark .po-row td[style*="color: #165c32"] {
            color: #f8fafc !important;
        }

        .dark .btn-soft {
            background: #0f172a !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        .dark .btn-ghost {
            background: rgba(30, 125, 67, 0.2) !important;
            color: #34d399 !important;
            border-color: rgba(30, 125, 67, 0.4) !important;
        }

        .dark .btn-outline-green {
            background: #1e293b !important;
            color: #34d399 !important;
            border-color: #1e7d43 !important;
        }

        .dark .btn-outline-red {
            background: #1e293b !important;
            color: #f87171 !important;
            border-color: #ef4444 !important;
        }

        .dark .modal {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        .dark .text-slate-800,
        .dark .text-slate-900 { color: #f8fafc !important; }
        .dark .text-slate-600,
        .dark .text-slate-700 { color: #cbd5e1 !important; }
        .dark .text-slate-500,
        .dark .text-slate-400 { color: #94a3b8 !important; }
        .dark select#po-sort-select {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
    </style>

    <div class="dashboard-layout" id="dashboard-root">
        <section id="purchase" class="page-section">
            <div class="page-section-header" style="display: none !important;">
                <div>
                    <div class="section-label">Procurement Overview</div>
                </div>
            </div>

            <div class="section-body">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                    <!-- Card 1 -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                            </div>
                            <span class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                <i data-lucide="trending-up" class="w-3 h-3"></i>
                                +12% vs last month
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-3xl font-black text-slate-900 tracking-tight">{{ $stats['total'] }}</div>
                            <div class="text-sm font-semibold text-slate-500 mt-1">Total Purchase Orders</div>
                            <div class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase mt-0.5">This Month</div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <span class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                Draft Status
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-3xl font-black text-slate-900 tracking-tight">{{ $stats['draft'] }}</div>
                            <div class="text-sm font-semibold text-slate-500 mt-1">Pending / Draft</div>
                            <div class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase mt-0.5">Awaiting action</div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i data-lucide="send" class="w-5 h-5"></i>
                            </div>
                            <span class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                <i data-lucide="trending-up" class="w-3 h-3"></i>
                                Active
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-3xl font-black text-slate-900 tracking-tight">{{ $stats['sent'] }}</div>
                            <div class="text-sm font-semibold text-slate-500 mt-1">Active / Sent</div>
                            <div class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase mt-0.5">Sent to suppliers</div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                            <span class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700">
                                <i data-lucide="trending-up" class="w-3 h-3"></i>
                                Attention
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-3xl font-black text-slate-900 tracking-tight" style="color: #dc2626;">{{ $stats['overdue'] }}</div>
                            <div class="text-sm font-semibold text-slate-500 mt-1">Overdue Orders</div>
                            <div class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase mt-0.5">Requires action</div>
                        </div>
                    </div>
                </div>

                <!-- Spend & Status Analytics Charts -->
                <div class="analytics-section" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin: 24px 0;">
                    <div class="chart-card" style="border: 1px solid var(--border); border-radius: 18px; padding: 22px; box-shadow: var(--shadow);">
                        <div class="mini-title" style="margin-bottom:16px; font-weight:800; font-size:13px; color:var(--brand-dark);">Supplier Spend Distribution (₱)</div>
                        <div style="height: 220px; position: relative;">
                            <canvas id="spendChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card" style="border: 1px solid var(--border); border-radius: 18px; padding: 22px; box-shadow: var(--shadow); display: flex; flex-direction: column;">
                        <div class="mini-title" style="margin-bottom:16px; font-weight:800; font-size:13px; color:var(--brand-dark);">Order Status Allocation</div>
                        <div style="height: 220px; position: relative; display: flex; justify-content: center; align-items: center; flex:1;">
                            <canvas id="statusChart" style="max-height: 180px; max-width: 180px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Filter & Sorting Control Toolbar -->
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-4 bg-white p-3.5 border border-slate-200 rounded-2xl shadow-sm">
                    <!-- Filter Options -->
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1 w-full md:w-auto">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1 shrink-0">Filter:</span>
                        <button type="button" class="po-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition active bg-[#235c2b] text-white shadow-sm" data-filter="all">
                            All <span class="ml-1 opacity-80">({{ $purchaseOrders->count() }})</span>
                        </button>
                        <button type="button" class="po-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition text-slate-600 hover:bg-slate-100" data-filter="pending">
                            Pending <span class="ml-1 opacity-80">({{ $purchaseOrders->whereIn('status', ['draft', 'pending', 'sent'])->count() }})</span>
                        </button>
                        <button type="button" class="po-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition text-slate-600 hover:bg-slate-100" data-filter="approved">
                            Approved <span class="ml-1 opacity-80">({{ $purchaseOrders->where('status', 'approved')->count() }})</span>
                        </button>
                        <button type="button" class="po-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition text-slate-600 hover:bg-slate-100" data-filter="rejected">
                            Rejected <span class="ml-1 opacity-80">({{ $stats['rejected'] }})</span>
                        </button>
                        <button type="button" class="po-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition text-slate-600 hover:bg-slate-100" data-filter="completed">
                            Completed <span class="ml-1 opacity-80">({{ $purchaseOrders->whereIn('status', ['received', 'completed'])->count() }})</span>
                        </button>
                    </div>

                    <!-- Search Input & Sorting Options -->
                    <div class="flex items-center gap-3 shrink-0 w-full md:w-auto justify-end">
                        <div class="relative">
                            <input type="text" id="po-search-input" placeholder="Search PO, PR, Vendor, Approver..." class="px-3 py-1.5 pl-8 border border-slate-300 rounded-xl text-xs font-semibold bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 w-48 md:w-56" />
                            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>
                        <label for="po-sort-select" class="text-xs font-bold text-slate-400 uppercase tracking-wider shrink-0">Sort By:</label>
                        <select id="po-sort-select" class="px-3 py-1.5 border border-slate-300 rounded-xl text-xs font-semibold bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="latest">Latest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="priority">Priority Level</option>
                            <option value="po_number">Purchase Order Number</option>
                            <option value="date_created">Date Created</option>
                            <option value="vendor">Vendor Name</option>
                            <option value="status">Status</option>
                            <option value="amount">Total Amount</option>
                        </select>
                    </div>
                </div>

                <!-- TABLE: ALL -->
                <div class="table-container" id="tab-all">
                    <div class="table-wrap">
                        <table class="unified-table" id="table-data-all">
                            <thead>
                                <tr id="po-table-header-row">
                                    <th>PO ID</th>
                                    <th>Linked PR</th>
                                    <th>Supplier</th>
                                    <th>Date Issued</th>
                                    <th class="col-default">Expected Delivery</th>
                                    <th class="col-default">Subtotal (₱)</th>
                                    <th class="col-default">VAT (₱)</th>
                                    <th class="col-default">Total (₱)</th>
                                    <th class="col-rejected" style="display:none;">Rejected By</th>
                                    <th class="col-rejected" style="display:none;">Rejected Date</th>
                                    <th class="col-rejected" style="display:none;">Rejection Reason</th>
                                    <th>Status</th>
                                    <th class="col-default">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $po)
                                    @php
                                        $subtotal = $po->total / 1.12;
                                        $vat = $subtotal * 0.12;
                                        $isOverdue = !in_array($po->status, ['received', 'rejected', 'cancelled']) && $po->expected_delivery && $po->expected_delivery->isPast();
                                        $isRejected = in_array($po->status, ['rejected', 'cancelled']);
                                        $rejectedBy = $po->rejected_by_name;
                                        $rejectedDate = $po->rejected_date_formatted;
                                        $rejectionReason = $po->rejection_reason_text;
                                    @endphp
                                    <tr class="po-row {{ $isOverdue ? 'po-row-overdue' : '' }}"
                                        data-po-id="{{ $po->id }}"
                                        data-po-number="{{ strtolower($po->po_number) }}"
                                        data-status="{{ strtolower($po->status) }}"
                                        data-vendor="{{ strtolower($po->supplier->name ?? '') }}"
                                        data-priority="{{ strtolower($po->requisition->urgency ?? 'medium') }}"
                                        data-date="{{ $po->created_at ? $po->created_at->timestamp : 0 }}"
                                        data-amount="{{ (float) $po->total }}"
                                        data-search="{{ strtolower($po->po_number . ' ' . ($po->requisition->code ?? '') . ' ' . ($po->supplier->name ?? '') . ' ' . $rejectedBy . ' ' . $rejectionReason) }}"
                                        style="transition: all 0.2s ease;">
                                        <td style="font-weight: 700; color: #165c32;">{{ $po->po_number }}</td>
                                        <td>{{ $po->requisition->code ?? '—' }}</td>
                                        <td>{{ $po->supplier->name ?? '—' }}</td>
                                        <td>{{ optional($po->issued_at)->format('M d, Y') ?? optional($po->created_at)->format('M d, Y') }}</td>
                                        <td class="col-default" style="{{ $isOverdue ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                            {{ optional($po->expected_delivery)->format('M d, Y') ?? '—' }}
                                        </td>
                                        <td class="col-default">{{ number_format($subtotal, 2) }}</td>
                                        <td class="col-default">{{ number_format($vat, 2) }}</td>
                                        <td class="col-default" style="font-weight: 700;">{{ number_format($po->total, 2) }}</td>
                                        
                                        <td class="col-rejected" style="display:none;">{{ $rejectedBy }}</td>
                                        <td class="col-rejected" style="display:none;">{{ $rejectedDate }}</td>
                                        <td class="col-rejected" style="display:none; max-width:240px; white-space:normal;">{{ $rejectionReason }}</td>

                                        <td>
                                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                @if($po->status === 'sent')
                                                    <span class="badge badge-sent"><i data-lucide="send" class="w-3.5 h-3.5"></i> Sent to Supplier</span>
                                                @elseif($po->status === 'received')
                                                    <span class="badge badge-received"><i data-lucide="check" class="w-3.5 h-3.5"></i> Fully Received</span>
                                                @elseif($po->status === 'draft')
                                                    <span class="badge badge-draft"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Draft</span>
                                                @elseif($po->status === 'approved')
                                                    <span class="badge badge-approved"><i data-lucide="thumbs-up" class="w-3.5 h-3.5"></i> Approved</span>
                                                @elseif($po->status === 'partial')
                                                    <span class="badge badge-partial"><i data-lucide="package" class="w-3.5 h-3.5"></i> Partially Received</span>
                                                @elseif($isRejected)
                                                    <span class="badge badge-cancelled"><i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Rejected</span>
                                                @else
                                                    <span class="badge badge-draft">{{ ucfirst($po->status) }}</span>
                                                @endif

                                                @if($isOverdue)
                                                    <span class="badge badge-overdue"><i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Overdue</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="col-default">
                                            <div class="action-row">
                                                <a class="btn-xs btn-outline-green" href="{{ route('purchase_orders.pdf', $po) }}" target="_blank"><i data-lucide="file-text" class="w-3.5 h-3.5"></i> PDF</a>
                                                <button type="button" class="btn-xs btn-soft" onclick="exportTableToCSV('table-data-all', '{{ $po->po_number }}.csv')"><i data-lucide="download" class="w-3.5 h-3.5"></i> CSV</button>
                                                @if($po->status === 'draft')
                                                    <a class="btn-xs btn-outline-green" href="{{ route('procurement.create') }}?edit={{ $po->id }}"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit</a>
                                                    <form method="POST" action="{{ route('purchase_orders.status', $po) }}" style="display:inline">
                                                        @csrf
                                                        <input type="hidden" name="status" value="cancelled" />
                                                        <button type="submit" class="btn-xs btn-outline-red"><i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Cancel</button>
                                                    </form>
                                                @elseif($po->status === 'sent')
                                                    <form method="POST" action="{{ route('purchase_orders.status', $po) }}" style="display:inline">
                                                        @csrf
                                                        <input type="hidden" name="status" value="received" />
                                                        <button type="submit" class="btn-xs btn-outline-green"><i data-lucide="check" class="w-3.5 h-3.5"></i> Receive</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" style="text-align: center; padding: 40px; color: var(--muted);">
                                            No purchase orders found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 12px; font-size: 13px; color: #6b7280; display: flex; justify-content: space-between;">
                        <span>Showing {{ $purchaseOrders->count() }} purchase orders</span>
                        @if($stats['overdue'] > 0)
                            <span style="color: #dc2626; font-weight: 700;">⚠️ {{ $stats['overdue'] }} overdue orders — immediate action required</span>
                        @endif
                    </div>
                </div>

                <!-- TABLE: DRAFT -->
                <div class="table-container" id="tab-draft" style="display:none">
                    <div class="table-wrap">
                        <table class="unified-table" id="table-data-draft">
                            <thead>
                                <tr>
                                    <th>PO ID</th>
                                    <th>Linked PR</th>
                                    <th>Supplier</th>
                                    <th>Date Issued</th>
                                    <th>Expected Delivery</th>
                                    <th>Subtotal (₱)</th>
                                    <th>VAT (₱)</th>
                                    <th>Total (₱)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders->where('status', 'draft') as $po)
                                    @php
                                        $subtotal = $po->total / 1.12;
                                        $vat = $subtotal * 0.12;
                                        $isOverdue = $po->expected_delivery && $po->expected_delivery->isPast();
                                    @endphp
                                    <tr class="po-row {{ $isOverdue ? 'po-row-overdue' : '' }}" style="transition: all 0.2s ease;">
                                        <td style="font-weight: 700; color: #165c32;">{{ $po->po_number }}</td>
                                        <td>{{ $po->requisition->code ?? '—' }}</td>
                                        <td>{{ $po->supplier->name ?? '—' }}</td>
                                        <td>{{ optional($po->issued_at)->format('M d, Y') ?? optional($po->created_at)->format('M d, Y') }}</td>
                                        <td style="{{ $isOverdue ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                            {{ optional($po->expected_delivery)->format('M d, Y') ?? '—' }}
                                        </td>
                                        <td>{{ number_format($subtotal, 2) }}</td>
                                        <td>{{ number_format($vat, 2) }}</td>
                                        <td style="font-weight: 700;">{{ number_format($po->total, 2) }}</td>
                                        <td>
                                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                <span class="badge badge-draft"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Draft</span>
                                                @if($isOverdue)
                                                    <span class="badge badge-overdue"><i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Overdue</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-row">
                                                <button type="button" class="btn-xs btn-soft" onclick="exportTableToCSV('table-data-draft', '{{ $po->po_number }}.csv')"><i data-lucide="download" class="w-3.5 h-3.5"></i> CSV</button>
                                                <a class="btn-xs btn-outline-green" href="{{ route('procurement.create') }}?edit={{ $po->id }}"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit</a>
                                                <form method="POST" action="{{ route('purchase_orders.status', $po) }}" style="display:inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="cancelled" />
                                                    <button type="submit" class="btn-xs btn-outline-red"><i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Cancel</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" style="text-align: center; padding: 40px; color: var(--muted);">
                                            No draft purchase orders found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 12px; font-size: 13px; color: #6b7280; display: flex; justify-content: space-between;">
                        <span>Showing {{ $purchaseOrders->where('status', 'draft')->count() }} purchase order with status "Draft"</span>
                        @if($stats['overdue'] > 0)
                            <span style="color: #dc2626; font-weight: 700;">⚠️ {{ $stats['overdue'] }} overdue orders — immediate action required</span>
                        @endif
                    </div>
                </div>



                <!-- TABLE: SENT TO SUPPLIER -->
                <div class="table-container" id="tab-sent" style="display:none">
                    <div class="table-wrap">
                        <table class="unified-table" id="table-data-sent">
                            <thead>
                                <tr>
                                    <th>PO ID</th>
                                    <th>Linked PR</th>
                                    <th>Supplier</th>
                                    <th>Date Issued</th>
                                    <th>Expected Delivery</th>
                                    <th>Subtotal (₱)</th>
                                    <th>VAT (₱)</th>
                                    <th>Total (₱)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders->where('status', 'sent') as $po)
                                    @php
                                        $subtotal = $po->total / 1.12;
                                        $vat = $subtotal * 0.12;
                                        $isOverdue = $po->expected_delivery && $po->expected_delivery->isPast();
                                    @endphp
                                    <tr class="po-row {{ $isOverdue ? 'po-row-overdue' : '' }}" style="transition: all 0.2s ease;">
                                        <td style="font-weight: 700; color: #165c32;">{{ $po->po_number }}</td>
                                        <td>{{ $po->requisition->code ?? '—' }}</td>
                                        <td>{{ $po->supplier->name ?? '—' }}</td>
                                        <td>{{ optional($po->issued_at)->format('M d, Y') ?? optional($po->created_at)->format('M d, Y') }}</td>
                                        <td style="{{ $isOverdue ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                            {{ optional($po->expected_delivery)->format('M d, Y') ?? '—' }}
                                        </td>
                                        <td>{{ number_format($subtotal, 2) }}</td>
                                        <td>{{ number_format($vat, 2) }}</td>
                                        <td style="font-weight: 700;">{{ number_format($po->total, 2) }}</td>
                                        <td>
                                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                <span class="badge badge-sent"><i data-lucide="send" class="w-3.5 h-3.5"></i> Sent to Supplier</span>
                                                @if($isOverdue)
                                                    <span class="badge badge-overdue"><i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Overdue</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 40px; color: var(--muted);">
                                            No purchase orders sent to suppliers.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 12px; font-size: 13px; color: #6b7280; display: flex; justify-content: space-between;">
                        <span>Showing {{ $purchaseOrders->where('status', 'sent')->count() }} purchase order with status "Sent to Supplier"</span>
                        @if($stats['overdue'] > 0)
                            <span style="color: #dc2626; font-weight: 700;">⚠️ {{ $stats['overdue'] }} overdue orders — immediate action required</span>
                        @endif
                    </div>
                </div>



                <!-- TABLE: FULLY RECEIVED -->
                <div class="table-container" id="tab-received" style="display:none">
                    <div class="table-wrap">
                        <table class="unified-table" id="table-data-received">
                            <thead>
                                <tr>
                                    <th>PO ID</th>
                                    <th>Linked PR</th>
                                    <th>Supplier</th>
                                    <th>Date Issued</th>
                                    <th>Expected Delivery</th>
                                    <th>Subtotal (₱)</th>
                                    <th>VAT (₱)</th>
                                    <th>Total (₱)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders->where('status', 'received') as $po)
                                    @php
                                        $subtotal = $po->total / 1.12;
                                        $vat = $subtotal * 0.12;
                                    @endphp
                                    <tr class="po-row" style="transition: all 0.2s ease;">
                                        <td style="font-weight: 700; color: #165c32;">{{ $po->po_number }}</td>
                                        <td>{{ $po->requisition->code ?? '—' }}</td>
                                        <td>{{ $po->supplier->name ?? '—' }}</td>
                                        <td>{{ optional($po->issued_at)->format('M d, Y') ?? optional($po->created_at)->format('M d, Y') }}</td>
                                        <td>{{ optional($po->expected_delivery)->format('M d, Y') ?? '—' }}</td>
                                        <td>{{ number_format($subtotal, 2) }}</td>
                                        <td>{{ number_format($vat, 2) }}</td>
                                        <td style="font-weight: 700;">{{ number_format($po->total, 2) }}</td>
                                        <td>
                                            <span class="badge badge-received"><i data-lucide="check" class="w-3.5 h-3.5"></i> Fully Received</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 40px; color: var(--muted);">
                                            No fully received purchase orders.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 12px; font-size: 13px; color: #6b7280; display: flex; justify-content: space-between;">
                        <span>Showing {{ $purchaseOrders->where('status', 'received')->count() }} purchase order with status "Fully Received"</span>
                        @if($stats['overdue'] > 0)
                            <span style="color: #dc2626; font-weight: 700;">⚠️ {{ $stats['overdue'] }} overdue orders — immediate action required</span>
                        @endif
                    </div>
                </div>


            </div>
        </section>

        <aside id="sidenotif" class="page-section">
            <div class="page-section-header">
                <div>
                    <div class="section-label">Workflow</div>
                    <h2>Review &amp; Logs</h2>
                    <p>Access approvals and activity logs.</p>
                </div>
            </div>

            <div class="section-body">
                <div class="sidebar-nav" style="display:grid; gap:16px;">
                    <div class="tier-primary" style="display:flex; gap:6px; flex-wrap:wrap;">
                        <button type="button" class="tab side-tab active" data-section="notifications" style="padding: 8px 12px; font-size:12px;">Alerts</button>
                        <button type="button" class="tab side-tab" data-section="logs" style="padding: 8px 12px; font-size:12px;">Activity</button>
                    </div>

                    <div class="tier-secondary">
                        <!-- Notifications/Alerts Section -->
                        <div class="mini-card side-section" data-section="notifications">
                            <div class="mini-title">Delivery Alerts</div>
                            <div class="log-list">
                                @php $alertCount = 0; @endphp
                                @foreach($purchaseOrders as $po)
                                    @if($po->status !== 'received' && $po->expected_delivery && $po->expected_delivery->isPast())
                                        @php $alertCount++; @endphp
                                        <div class="log-item" style="border-left: 3px solid #dc2626;">
                                            <strong style="color: #b91c1c;">⚠️ {{ $po->po_number }} Overdue</strong>
                                            <div class="muted" style="font-size:12px;">{{ $po->supplier->name ?? '—' }}</div>
                                            <div style="font-size:11px; margin-top:4px;">Expected: {{ $po->expected_delivery->format('M d, Y') }}</div>
                                        </div>
                                    @endif
                                @endforeach
                                @if($alertCount === 0)
                                    <div class="muted" style="text-align: center; padding: 20px; font-size:13px;">No pending delivery alerts.</div>
                                @endif
                            </div>
                        </div>

                        <!-- Logs Section -->
                        <div class="mini-card side-section" data-section="logs" style="display:none">
                            <div class="mini-title">Recent Activities</div>
                            <div class="log-list">
                                @foreach($purchaseOrders->where('status', '!=', 'draft')->take(3) as $po)
                                    <div class="log-item">
                                        <strong>PO Transmitted</strong>
                                        <div class="muted" style="font-size:12px;">{{ $po->po_number }} issued to {{ $po->supplier->name }}</div>
                                        <div style="font-size:10px; margin-top:4px; color:var(--muted);">{{ $po->updated_at->diffForHumans() }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            // --- POPULAR SIDEBAR SECTIONS SWITCHER ---
            const tabs = document.querySelectorAll('#sidenotif .side-tab');
            const sections = document.querySelectorAll('#sidenotif .side-section');
            if(tabs.length && sections.length) {
                function showSection(name){
                    sections.forEach(s=> s.style.display = (s.dataset.section === name) ? '' : 'none');
                    tabs.forEach(t=> t.classList.toggle('active', t.dataset.section === name));
                }
                tabs.forEach(t=> t.addEventListener('click', ()=> showSection(t.dataset.section)));
                const active = Array.from(tabs).find(t=> t.classList.contains('active')) || tabs[0];
                if(active) showSection(active.dataset.section);
            }

            // --- CLIENT-SIDE TABLE STATUS FILTERING WITH SEPARATE TABLES ---
            const tabButtons = document.querySelectorAll('.tabs .tab');
            const tableContainers = document.querySelectorAll('.table-container');

            window.exportTableToCSV = function(tableId, filename) {
                const table = document.getElementById(tableId);
                if (!table) return;

                let csv = [];
                const rows = table.querySelectorAll('tr');

                for (let i = 0; i < rows.length; i++) {
                    const row = [];
                    const cols = rows[i].querySelectorAll('td, th');

                    for (let j = 0; j < cols.length; j++) {
                        // Skip actions column
                        if (cols[j].textContent.trim().toLowerCase() === 'actions' || 
                            cols[j].querySelector('.action-row')) {
                            continue;
                        }

                        let text = cols[j].textContent.trim()
                            .replace(/"/g, '""') // Escape double quotes
                            .replace(/\n/g, ' ') // Remove newlines
                            .replace(/\s+/g, ' '); // Clean spaces

                        row.push(`"${text}"`);
                    }

                    if (row.length > 0) {
                        csv.push(row.join(','));
                    }
                }

                const csvContent = csv.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);

                const link = document.createElement("a");
                link.setAttribute("href", url);
                link.setAttribute("download", filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            // --- Dynamic Order Management Sorting & Filtering & Searching ---
            const filterBtns = document.querySelectorAll('.po-filter-btn');
            const sortSelect = document.getElementById('po-sort-select');
            const searchInput = document.getElementById('po-search-input');
            const tableBody = document.querySelector('#table-data-all tbody');

            if (tableBody && filterBtns.length) {
                let currentFilter = 'all';
                let currentSort = 'latest';
                let currentQuery = '';

                function applySortAndFilter() {
                    const rows = Array.from(tableBody.querySelectorAll('tr.po-row'));
                    const isRejectedFilter = (currentFilter === 'rejected');

                    // Toggle header row columns if available
                    const headerRow = document.getElementById('po-table-header-row');
                    if (headerRow) {
                        headerRow.querySelectorAll('.col-default').forEach(el => el.style.display = isRejectedFilter ? 'none' : '');
                        headerRow.querySelectorAll('.col-rejected').forEach(el => el.style.display = isRejectedFilter ? '' : 'none');
                    }

                    // 1. Filter & Search
                    rows.forEach(row => {
                        const status = (row.dataset.status || '').toLowerCase();
                        const searchText = (row.dataset.search || row.textContent || '').toLowerCase();
                        let matchesFilter = false;

                        if (currentFilter === 'all') {
                            matchesFilter = true;
                        } else if (currentFilter === 'pending') {
                            matchesFilter = ['draft', 'pending', 'sent'].includes(status);
                        } else if (currentFilter === 'approved') {
                            matchesFilter = status === 'approved';
                        } else if (currentFilter === 'rejected') {
                            matchesFilter = status === 'rejected' || status === 'cancelled';
                        } else if (currentFilter === 'completed') {
                            matchesFilter = status === 'received' || status === 'completed';
                        }

                        let matchesQuery = true;
                        if (currentQuery) {
                            matchesQuery = searchText.includes(currentQuery);
                        }

                        const show = matchesFilter && matchesQuery;
                        row.style.display = show ? '' : 'none';

                        if (show) {
                            row.querySelectorAll('.col-default').forEach(el => el.style.display = isRejectedFilter ? 'none' : '');
                            row.querySelectorAll('.col-rejected').forEach(el => el.style.display = isRejectedFilter ? '' : 'none');
                        }
                    });

                    // 2. Sort
                    const visibleRows = rows.filter(r => r.style.display !== 'none');
                    
                    visibleRows.sort((a, b) => {
                        if (currentSort === 'latest' || currentSort === 'date_created') {
                            return Number(b.dataset.date || 0) - Number(a.dataset.date || 0);
                        } else if (currentSort === 'oldest') {
                            return Number(a.dataset.date || 0) - Number(b.dataset.date || 0);
                        } else if (currentSort === 'priority') {
                            const priorityWeight = { high: 3, medium: 2, low: 1 };
                            const pA = priorityWeight[(a.dataset.priority || '').toLowerCase()] || 0;
                            const pB = priorityWeight[(b.dataset.priority || '').toLowerCase()] || 0;
                            return pB - pA;
                        } else if (currentSort === 'po_number') {
                            return (a.dataset.poNumber || '').localeCompare(b.dataset.poNumber || '');
                        } else if (currentSort === 'vendor') {
                            return (a.dataset.vendor || '').localeCompare(b.dataset.vendor || '');
                        } else if (currentSort === 'status') {
                            return (a.dataset.status || '').localeCompare(b.dataset.status || '');
                        } else if (currentSort === 'amount') {
                            return Number(b.dataset.amount || 0) - Number(a.dataset.amount || 0);
                        }
                        return 0;
                    });

                    // Re-append sorted rows to tableBody
                    visibleRows.forEach(r => tableBody.appendChild(r));
                }

                filterBtns.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        filterBtns.forEach(b => {
                            b.classList.remove('active', 'bg-[#235c2b]', 'text-white', 'shadow-sm');
                            b.classList.add('text-slate-600');
                        });
                        this.classList.add('active', 'bg-[#235c2b]', 'text-white', 'shadow-sm');
                        this.classList.remove('text-slate-600');

                        currentFilter = this.dataset.filter || 'all';
                        applySortAndFilter();
                    });
                });

                if (sortSelect) {
                    sortSelect.addEventListener('change', function() {
                        currentSort = this.value;
                        applySortAndFilter();
                    });
                }

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        currentQuery = this.value.trim().toLowerCase();
                        applySortAndFilter();
                    });
                }
            }



            // --- ANALYTICS CHARTS INITIALIZATION ---
            const spendData = @json($spendData);
            const spendLabels = spendData.map(d => d.supplier);
            const spendTotals = spendData.map(d => d.total);

            // Spend Chart (Bar Chart)
            const ctxSpend = document.getElementById('spendChart').getContext('2d');
            if (window.purchaseSpendChart) {
                window.purchaseSpendChart.destroy();
            }
            window.purchaseSpendChart = new Chart(ctxSpend, {
                type: 'bar',
                data: {
                    labels: spendLabels,
                    datasets: [{
                        label: 'Total Spend (₱)',
                        data: spendTotals,
                        backgroundColor: 'rgba(30, 125, 67, 0.75)',
                        borderColor: '#1e7d43',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(232, 238, 230, 0.4)' },
                            ticks: { font: { family: 'Outfit', size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Outfit', size: 11 } }
                        }
                    }
                }
            });

            // Status Chart (Doughnut Chart)
            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            if (window.purchaseStatusChart) {
                window.purchaseStatusChart.destroy();
            }
            window.purchaseStatusChart = new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Draft', 'Sent', 'Received'],
                    datasets: [{
                        data: [
                            {{ $purchaseOrders->where('status', 'draft')->count() }},
                            {{ $purchaseOrders->where('status', 'sent')->count() }},
                            {{ $purchaseOrders->where('status', 'received')->count() }}
                        ],
                        backgroundColor: [
                            'rgba(107, 114, 128, 0.75)', // Draft
                            'rgba(35, 84, 201, 0.75)',  // Sent
                            'rgba(30, 125, 67, 0.75)'   // Received
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: { family: 'Outfit', size: 12 } }
                        }
                    },
                    cutout: '65%'
                }
            });
        });
    </script>
@endsection
