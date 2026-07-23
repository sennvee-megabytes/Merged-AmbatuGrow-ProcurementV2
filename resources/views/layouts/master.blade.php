@php
    $suppliers = \App\Models\Supplier::get()
        ->unique(function ($s) {
            return strtolower(trim(preg_replace('/\s+\d+$/', '', $s->name)));
        })
        ->sortBy('name')
        ->values();
    $catalogProducts = \App\Models\Product::with('uom')->orderBy('name')->get();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ambatugrow Procurement')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#1f5c3d',
                            dark: '#163f2b',
                            light: '#e9f5ee',
                        },
                        accent: {
                            DEFAULT: '#d97706',
                            soft: '#fff7ed',
                        },
                    },
                    boxShadow: {
                        soft: '0 12px 30px rgba(15, 23, 42, 0.08)',
                    },
                },
            },
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html, body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        [x-cloak] { display: none !important; }
        .sidebar-link { transition: background-color 0.2s ease, transform 0.2s ease, color 0.2s ease; }
        .sidebar-link:hover { transform: translateX(2px); }
        .module-chip { letter-spacing: 0.02em; }

        /* Hide scrollbars but keep scrolling working */
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
        }
        .no-scrollbar {
            -ms-overflow-style: none !important;  /* IE and Edge */
            scrollbar-width: none !important;  /* Firefox */
        }

        /* Unified Top Header Bar based on Goods Receipt & Invoice Matching style */
        .top-bar-unified {
            background-color: #1e7d43;
            color: #ffffff;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .top-bar-left h2 {
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
            color: #ffffff;
        }

        .top-bar-left p {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.85);
            margin: 4px 0 0 0;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .icon-badge-container {
            position: relative;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .top-bar-icon {
            color: #ffffff;
            width: 20px;
            height: 20px;
            opacity: 0.9;
            transition: opacity 0.2s;
        }

        .top-bar-icon:hover {
            opacity: 1;
        }

        .icon-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            border: 1.5px solid var(--primary, #1e7d43);
        }

        .top-bar-btn {
            background-color: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
        }

        .top-bar-btn:hover {
            background-color: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.35);
        }

        /* Unified Card Styles matching Goods Receipt style */
        .unified-card {
            background-color: #ffffff !important;
            border: 1px solid #e4e8e2 !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
            padding: 20px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        }

        /* Unified Table Styles matching Goods Receipt style */
        .unified-table-card {
            background-color: #ffffff !important;
            border: 1px solid #e4e8e2 !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
            overflow-x: auto !important;
            width: 100% !important;
        }

        .unified-table {
            width: 100% !important;
            border-collapse: collapse !important;
            text-align: left !important;
        }

        .unified-table th {
            background-color: #f9fafb !important;
            padding: 12px 12px !important;
            font-size: 0.72rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            color: #4b5563 !important;
            letter-spacing: 0.5px !important;
            border-bottom: 1px solid #e4e8e2 !important;
        }

        .unified-table td {
            padding: 10px 12px !important;
            border-bottom: 1px solid #e4e8e2 !important;
            font-size: 0.8rem !important;
            color: #1f2937 !important;
            vertical-align: middle !important;
        }

        .unified-table tbody tr {
            transition: background-color 0.15s ease !important;
        }

        .unified-table tbody tr:hover {
            background-color: #f9fafb !important;
        }

        /* Unified Status Badges matching Goods Receipt style */
        .unified-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            padding: 4px 10px !important;
            border-radius: 9999px !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            white-space: nowrap !important;
        }

        .unified-badge-success {
            background-color: #d9f2e2 !important;
            color: #1e7d43 !important;
        }

        .unified-badge-warning {
            background-color: #fdf1c7 !important;
            color: #92680b !important;
        }

        .unified-badge-danger {
            background-color: #fde2e2 !important;
            color: #c53030 !important;
        }

        .unified-badge-info {
            background-color: #e0f2fe !important;
            color: #0369a1 !important;
        }

        .unified-badge-neutral {
            background-color: #f3f4f6 !important;
            color: #4b5563 !important;
        }

        /* Unified Form Controls matching Goods Receipt style */
        .unified-input, .unified-select {
            background-color: #f9fafb !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
            font-size: 0.82rem !important;
            color: #111827 !important;
            outline: none !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }

        .unified-input:focus, .unified-select:focus {
            border-color: #1e7d43 !important;
            box-shadow: 0 0 0 3px rgba(30, 125, 67, 0.15) !important;
        }

        /* Modal styling */
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
            width: min(100%, 980px);
            max-height: calc(100vh - 48px);
            overflow: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Sidebar transition and state styles */
        .sidebar-aside {
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: 144px; /* default collapsed */
        }
        .sidebar-aside.is-expanded {
            width: 320px;
        }

        .submenu-bar {
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: 72px; /* default collapsed */
            padding: 24px 12px !important; /* adjust padding for collapsed state */
        }
        .sidebar-aside.is-expanded .submenu-bar {
            width: 248px;
            padding: 24px 20px !important;
        }

        /* Toggle visibility based on state */
        .sidebar-aside .expanded-only {
            display: none !important;
        }
        .sidebar-aside.is-expanded .expanded-only {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            text-align: left !important;
        }
        .sidebar-aside.is-expanded .collapsed-only {
            display: none !important;
        }
        .sidebar-aside .collapsed-only {
            display: flex !important;
        }

        /* Premium High-Contrast Dark Mode Stylesheet */
        .dark body {
            background-color: #090d16 !important; /* deep rich dark void */
            color: #f8fafc !important; /* pristine slate white */
        }
        .dark .bg-slate-100, .dark .bg-\[\#f9fafb\], .dark .bg-\[\#F9FAFB\], .dark main.flex-grow {
            background-color: #090d16 !important;
        }
        .dark .bg-white, .dark .bg-slate-50, .dark .bg-slate-50\/50, .dark .bg-slate-100\/50 {
            background-color: #111827 !important; /* solid card dark */
            color: #f8fafc !important;
        }
        .dark .card {
            background-color: #111827 !important;
            border-color: #1e293b !important;
        }
        .dark .border-slate-200, .dark .border-slate-100, .dark .border-slate-200\/60, .dark .border-slate-200\/50, .dark .border-slate-350, .dark .border-slate-300 {
            border-color: #1e293b !important;
        }
        
        /* High Contrast Typography Adjustments (Light Mode) */
        body {
            color: #0f172a !important; /* solid charcoal */
        }
        .text-slate-500, .text-gray-500, .text-slate-400, .text-gray-400 {
            color: #4b5563 !important; /* rich gray - readable on white */
        }
        .text-slate-600, .text-gray-600 {
            color: #374151 !important; /* dark slate gray */
        }
        .text-slate-700, .text-gray-700, .text-slate-800, .text-gray-800 {
            color: #1f2937 !important; /* deep near-black */
        }

        /* High Contrast Typography Mappings (Dark Mode - Scoped outside sidebar) */
        .dark :not(aside):not(aside *) .text-slate-750, 
        .dark :not(aside):not(aside *) .text-slate-805, 
        .dark :not(aside):not(aside *) .text-slate-800, 
        .dark :not(aside):not(aside *) .text-slate-900, 
        .dark :not(aside):not(aside *) .text-gray-800, 
        .dark :not(aside):not(aside *) .text-gray-900,
        .dark :not(aside):not(aside *) [class*="text-slate-8"], 
        .dark :not(aside):not(aside *) [class*="text-slate-9"],
        .dark :not(aside):not(aside *) [class*="text-gray-8"], 
        .dark :not(aside):not(aside *) [class*="text-gray-9"],
        .dark :not(aside):not(aside *) [class*="text-neutral-8"], 
        .dark :not(aside):not(aside *) [class*="text-neutral-9"] {
            color: #ffffff !important; /* maximum brightness for headings/bold labels */
        }
        
        .dark :not(aside):not(aside *) .text-slate-700, 
        .dark :not(aside):not(aside *) .text-slate-600, 
        .dark :not(aside):not(aside *) .text-gray-700, 
        .dark :not(aside):not(aside *) .text-gray-600,
        .dark :not(aside):not(aside *) [class*="text-slate-6"], 
        .dark :not(aside):not(aside *) [class*="text-slate-7"],
        .dark :not(aside):not(aside *) [class*="text-gray-6"], 
        .dark :not(aside):not(aside *) [class*="text-gray-7"],
        .dark :not(aside):not(aside *) [class*="text-neutral-6"], 
        .dark :not(aside):not(aside *) [class*="text-neutral-7"] {
            color: #f1f5f9 !important; /* very clear off-white for regular body text */
        }
        
        .dark :not(aside):not(aside *) .text-slate-500, 
        .dark :not(aside):not(aside *) .text-gray-500,
        .dark :not(aside):not(aside *) [class*="text-slate-4"], 
        .dark :not(aside):not(aside *) [class*="text-slate-5"],
        .dark :not(aside):not(aside *) [class*="text-gray-4"], 
        .dark :not(aside):not(aside *) [class*="text-gray-5"],
        .dark :not(aside):not(aside *) [class*="text-neutral-4"], 
        .dark :not(aside):not(aside *) [class*="text-neutral-5"] {
            color: #cbd5e1 !important; /* clear light gray for secondary labels/descriptions */
        }

        .dark :not(aside):not(aside *) .text-slate-400, 
        .dark :not(aside):not(aside *) .text-gray-400 {
            color: #94a3b8 !important; /* muted gray - still readable on void background */
        }
        
        /* Dynamic Accent Elements (Scoped outside sidebar) */
        .dark :not(aside):not(aside *) .text-\[\#235c2b\], 
        .dark :not(aside):not(aside *) .text-green-800, 
        .dark :not(aside):not(aside *) .text-[#1f5c3d] {
            color: #34d399 !important; /* mint green for high contrast */
        }
        .dark :not(aside):not(aside *) .bg-\[\#235c2b\], 
        .dark :not(aside):not(aside *) .bg-emerald-600, 
        .dark :not(aside):not(aside *) .bg-emerald-700 {
            background-color: #059669 !important; /* high visibility green button */
            color: #ffffff !important;
        }
        .dark :not(aside):not(aside *) .bg-\[\#235c2b\]:hover, 
        .dark :not(aside):not(aside *) .bg-emerald-600:hover, 
        .dark :not(aside):not(aside *) .bg-emerald-700:hover {
            background-color: #10b981 !important;
        }
        
        /* Form controls */
        .dark input, .dark select, .dark textarea {
            background-color: #090d16 !important;
            color: #f8fafc !important;
            border-color: #1e293b !important;
        }
        .dark input:focus, .dark select:focus, .dark textarea:focus {
            border-color: #34d399 !important;
            outline: none;
        }
        
        /* Table Styling */
        .dark table, .dark .fig-table, .dark .unified-table {
            border-color: #1e293b !important;
        }
        .dark th, .dark .fig-table th, .dark .unified-table th {
            background-color: #111827 !important;
            color: #ffffff !important;
            border-bottom: 2px solid #1e293b !important;
        }
        .dark td, .dark .fig-table td, .dark .unified-table td {
            color: #e2e8f0 !important;
            border-bottom: 1px solid #1e293b !important;
        }
        .dark tr:hover, .dark .fig-table tr:hover {
            background-color: #1e293b/40 !important;
        }
        
        /* Modern Glowing Status Badges */
        .dark .badge-active, .dark .unified-badge-active {
            background-color: rgba(16, 185, 129, 0.12) !important;
            color: #34d399 !important;
            border: 1px solid rgba(16, 185, 129, 0.25) !important;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 700;
        }
        .dark .badge-pending, .dark .unified-badge-pending {
            background-color: rgba(245, 158, 11, 0.12) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(245, 158, 11, 0.25) !important;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 700;
        }
        .dark .badge-blacklisted, .dark .unified-badge-blacklisted {
            background-color: rgba(239, 68, 68, 0.12) !important;
            color: #f87171 !important;
            border: 1px solid rgba(239, 68, 68, 0.25) !important;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 700;
        }
        .dark .unified-badge-neutral {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border: 1px solid #334155 !important;
        }
        
        /* Top bar styling */
        .dark .top-bar-unified, .dark .topbar-actions {
            background-color: #111827 !important;
            border-color: #1e293b !important;
        }
        .dark .top-bar-unified h2 {
            color: #ffffff !important;
        }
        .dark .top-bar-unified p {
            color: #94a3b8 !important;
        }
    </style>
    @stack('head')
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 antialiased" x-data='appLayout({ requestorName: @json(auth()->check() ? auth()->user()->name : ""), suppliersList: @json($suppliers ?? []), catalogProducts: @json($catalogProducts ?? []) })'
      @open-requisition-modal.window="showRequisitionModal = true"
      @open-po-modal.window="showPoModal = true">
@php
    $primaryModules = [
        [
            'label' => 'Purchase Requisitions',
            'route' => 'approvals.index',
            'icon' => 'clipboard-list',
        ],
        [
            'label' => 'Supplier Directory',
            'route' => 'suppliers.dashboard',
            'icon' => 'users',
        ],
        [
            'label' => 'Order Management',
            'route' => 'procurement.home',
            'icon' => 'shopping-cart',
        ],
        [
            'label' => 'Receipt & Invoice',
            'route' => 'matching.index',
            'icon' => 'file-check',
        ],
    ];
@endphp

<div class="min-h-screen w-full min-w-0 max-w-none lg:flex">
    <!-- Mobile Top Bar -->
    <div class="lg:hidden bg-[#f4f7f3] border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-30 w-full shrink-0">
        <div class="flex items-center gap-3">
            <button @click="mobileMenuOpen = true" class="text-slate-700 hover:text-slate-900 focus:outline-none flex items-center justify-center p-1.5 rounded-lg hover:bg-slate-200/50">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo.jpg') }}" alt="Ambatugrow" class="w-8 h-8 rounded-full border border-slate-200 object-cover">
                <span class="font-extrabold text-sm tracking-tight text-slate-800">AMBATUGROW</span>
            </div>
        </div>
        <div>
            @if (Auth::check())
                <div class="w-8 h-8 rounded-full bg-emerald-50 border border-emerald-200 text-[#235c2b] flex items-center justify-center text-[10px] font-black" title="{{ Auth::user()->name }}">
                    {{ Auth::user()->avatar_initial ?? strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
            @endif
        </div>
    </div>

    <!-- Mobile Sidebar Drawer Backdrop -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden" 
         @click="mobileMenuOpen = false" 
         style="display: none;">
    </div>

    @php
        $isSupplierRoute = request()->routeIs('suppliers.*') || request()->is('supplier-management*');
    @endphp
    <aside id="sidebar-container" 
           @if($isSupplierRoute)
               :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="sidebar-aside fixed inset-y-0 left-0 z-50 flex transform -translate-x-full transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:z-auto lg:h-screen lg:sticky lg:top-0 border-r border-slate-200 no-scrollbar shrink-0 bg-[#e8ece6]"
           @else
               class="w-[72px] fixed inset-y-0 left-0 z-50 flex transform -translate-x-full transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:z-auto lg:h-screen lg:sticky lg:top-0 border-r border-slate-200 no-scrollbar shrink-0 bg-[#e8ece6]"
           @endif>
        <!-- Stage 1: Icon bar -->
        <div class="w-[72px] shrink-0 border-r border-slate-200/60 bg-[#e8ece6] flex flex-col items-center py-6 justify-between h-full">
            <!-- Top Logo + Hub Icons -->
            <div class="flex flex-col items-center gap-4 w-full px-2">
                <!-- Logo -->
                <div class="w-12 h-12 rounded-full overflow-hidden border border-slate-200 bg-white p-1 shadow-sm flex items-center justify-center mb-2">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ambatugrow" class="w-full h-full object-cover rounded-full">
                </div>

                <!-- Hub Icons -->
                <!-- Purchase Requisitions -->
                <a href="{{ route('approvals.index') }}" class="relative w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 {{ (request()->routeIs('approvals.*') || request()->routeIs('requisitions.*') || request()->is('purchase-and-requisition*')) ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-700 hover:bg-slate-200/50' }}" title="Purchase Requisitions">
                    @if (request()->routeIs('approvals.*') || request()->routeIs('requisitions.*') || request()->is('purchase-and-requisition*'))
                        <div class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-white rounded-r"></div>
                    @endif
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </a>
                
                <!-- Supplier Directory -->
                <a href="{{ route('suppliers.dashboard') }}" class="relative w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 {{ (request()->routeIs('suppliers.*') || request()->is('supplier-management*')) ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-700 hover:bg-slate-200/50' }}" title="Supplier Directory">
                    @if (request()->routeIs('suppliers.*') || request()->is('supplier-management*'))
                        <div class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-white rounded-r"></div>
                    @endif
                    <i data-lucide="users" class="w-5 h-5"></i>
                </a>

                <!-- Order Management -->
                <a href="{{ route('procurement.home') }}" class="relative w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 {{ (request()->routeIs('procurement.*') || request()->routeIs('purchase_orders.*') || request()->is('order-management*')) ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-700 hover:bg-slate-200/50' }}" title="Order Management">
                    @if (request()->routeIs('procurement.*') || request()->routeIs('purchase_orders.*') || request()->is('order-management*'))
                        <div class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-white rounded-r"></div>
                    @endif
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                </a>

                <!-- Receipt & Invoice -->
                <a href="{{ route('matching.index') }}" class="relative w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 {{ (request()->routeIs('matching.*') || request()->is('goods-receipt-invoice-matching*')) ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-700 hover:bg-slate-200/50' }}" title="Goods Receipt & Invoice Matching">
                    @if (request()->routeIs('matching.*') || request()->is('goods-receipt-invoice-matching*'))
                        <div class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-white rounded-r"></div>
                    @endif
                    <i data-lucide="file-check" class="w-5 h-5"></i>
                </a>

                <!-- Divider -->
                <div class="w-8 h-[1px] bg-slate-300 my-1"></div>

                <!-- Secondary Icons -->
                <a href="{{ route('dashboard') }}" class="relative w-12 h-12 rounded-xl flex items-center justify-center text-slate-700 hover:bg-slate-200/50 cursor-pointer transition-all" title="Dashboard Home">
                    <i data-lucide="home" class="w-5 h-5"></i>
                </a>

                <a href="{{ route('erd.schema') }}" class="relative w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 {{ request()->routeIs('erd.schema') ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-700 hover:bg-slate-200/50' }}" title="Database Schema Explorer">
                    @if (request()->routeIs('erd.schema'))
                        <div class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-white rounded-r"></div>
                    @endif
                    <i data-lucide="database" class="w-5 h-5"></i>
                </a>

                <div @click="activeRightPanel = 'notifications'" class="relative w-12 h-12 rounded-xl flex items-center justify-center text-slate-700 hover:bg-slate-200/50 cursor-pointer transition-all" title="Notifications">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <div class="absolute top-3.5 right-3.5 w-2 h-2 bg-red-500 rounded-full border border-white" x-show="notificationsList.some(n => !n.read)"></div>
                </div>

                <!-- Avatar bubbles -->
                <div class="flex flex-col items-center gap-1.5 my-1">
                    <div @click="openChat('Emily Cooper')" class="w-7 h-7 rounded-full bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center text-[9px] font-black shadow-sm cursor-pointer" title="Emily Cooper">EC</div>
                    <div @click="openChat('Alex Morgan')" class="w-7 h-7 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center text-[9px] font-black shadow-sm cursor-pointer" title="Alex Morgan">AM</div>
                    <div @click="openChat('Marcus Davis')" class="w-7 h-7 rounded-full bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-[9px] font-black shadow-sm cursor-pointer" title="Marcus Davis">MD</div>
                </div>
            </div>

            <!-- Bottom icons -->
            <div class="flex flex-col items-center gap-4 w-full px-2">
                <!-- Divider -->
                <div class="w-8 h-[1px] bg-slate-300 my-1"></div>

                <div @click="activeRightPanel = 'settings'" class="w-12 h-12 rounded-xl flex items-center justify-center text-slate-700 hover:bg-slate-200/50 cursor-pointer transition-all" title="Settings">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                </div>

                <div @click="toggleDarkMode()" class="w-12 h-12 rounded-xl flex items-center justify-center text-indigo-500 hover:bg-slate-200/50 cursor-pointer transition-all" title="Toggle Dark Mode">
                    <i data-lucide="moon" class="w-5 h-5"></i>
                </div>

                <div @click="activeRightPanel = 'help'" class="w-12 h-12 rounded-xl flex items-center justify-center text-slate-700 hover:bg-slate-200/50 cursor-pointer transition-all" title="Support">
                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                </div>

                @if (Auth::check())
                    <div @click="activeRightPanel = 'profile'" class="w-8 h-8 rounded-full border border-[#235c2b] p-0.5 flex items-center justify-center bg-white shadow-sm mt-1 cursor-pointer" title="{{ Auth::user()->name }} ({{ str_replace('_', ' ', Auth::user()->role) }})">
                        <div class="w-full h-full rounded-full bg-emerald-50 text-[#235c2b] flex items-center justify-center text-[9px] font-black">
                            {{ Auth::user()->avatar_initial ?? strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if (request()->routeIs('suppliers.*') || request()->is('supplier-management*'))
        <!-- Stage 2: Submenu bar -->
        <div class="submenu-bar bg-[#f4f7f3] flex flex-col p-5 overflow-y-auto no-scrollbar justify-between h-full border-r border-slate-200/50">
            <!-- Expanded State Content -->
            <div class="expanded-only flex-col justify-between h-full w-full items-start text-left">
                <div class="w-full">
                    <!-- Header -->
                    <div class="mb-6 mt-1 text-left w-full flex items-center justify-between font-sans">
                        <div>
                            <div class="text-[10px] font-extrabold tracking-wider text-[#235c2b] uppercase text-left">Procurement</div>
                            <div class="text-base font-black tracking-tight text-slate-900 mt-0.5 text-left uppercase font-black">Supplier Management</div>
                        </div>
                        <button type="button" @click="mobileMenuOpen = false" class="lg:hidden text-slate-400 hover:text-slate-650 focus:outline-none p-1 rounded-md hover:bg-slate-200/40">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Navigation List -->
                    <nav class="space-y-1.5 w-full text-left font-sans">
                        <a href="{{ route('suppliers.dashboard') }}" class="flex items-center justify-start gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all w-full text-left {{ request()->routeIs('suppliers.dashboard') ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-800 hover:bg-[#e8ece6] hover:text-slate-900' }}">
                            <i class="fa-solid fa-chart-line w-5 text-center text-base shrink-0"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="flex items-center justify-start gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all w-full text-left {{ request()->routeIs('suppliers.index') ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-800 hover:bg-[#e8ece6] hover:text-slate-900' }}">
                            <i class="fa-solid fa-handshake w-5 text-center text-base shrink-0"></i>
                            <span>View All Suppliers</span>
                        </a>
                        <a href="{{ route('suppliers.active') }}" class="flex items-center justify-start gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all w-full text-left {{ request()->routeIs('suppliers.active') ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-800 hover:bg-[#e8ece6] hover:text-slate-900' }}">
                            <i class="fa-solid fa-circle-check w-5 text-center text-base shrink-0"></i>
                            <span>Active Suppliers</span>
                        </a>
                        <a href="{{ route('suppliers.pending') }}" class="flex items-center justify-start gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all w-full text-left {{ request()->routeIs('suppliers.pending') ? 'bg-[#235c2b] text-white shadow-sm' : 'text-slate-800 hover:bg-[#e8ece6] hover:text-slate-900' }}">
                            <i class="fa-solid fa-clock w-5 text-center text-base shrink-0"></i>
                            <span>Pending Verification</span>
                        </a>
                    </nav>
                </div>

                <!-- Bottom Links -->
                <div class="pt-6 border-t border-slate-200/60 mt-auto flex flex-col gap-2 w-full text-left items-start font-sans">
                    @if (Auth::check())
                        <div class="px-3 py-2 w-full bg-[#e8ece6]/30 rounded-xl mb-2">
                            <div class="text-[9px] font-extrabold text-[#235c2b] uppercase tracking-wider">Signed In As</div>
                            <div class="text-xs font-black text-slate-900 truncate mt-0.5" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</div>
                            <div class="text-[9px] font-bold text-slate-500 uppercase mt-0.5">{{ str_replace('_', ' ', Auth::user()->role) }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="w-full m-0">
                            @csrf
                            <button type="submit" class="flex items-center justify-start gap-3 px-3 py-2 rounded-lg text-sm font-bold text-red-700 hover:bg-red-50 hover:text-red-800 transition-all w-full text-left">
                                <i data-lucide="log-out" class="w-5 h-5 text-red-600"></i>
                                <span>Log Out</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Requirement 7: Collapsed State Content with Custom Floating Animated Tooltips -->
            <div class="collapsed-only flex-col items-center gap-4 mt-2 w-full">
                <!-- Dashboard Tooltip -->
                <div class="relative group/tooltip">
                    <a href="{{ route('suppliers.dashboard') }}" 
                       class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 ease-in-out hover:scale-105 {{ request()->routeIs('suppliers.dashboard') ? 'bg-[#235c2b] text-white shadow-md' : 'text-slate-700 bg-white/50 hover:bg-[#e9f5ee] hover:text-[#1f5c3d] shadow-sm' }}">
                        <span class="text-lg select-none">📊</span>
                    </a>
                    <div class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-lg opacity-0 translate-x-2 transition-all duration-200 ease-in-out group-hover/tooltip:opacity-100 group-hover/tooltip:translate-x-0 flex items-center gap-2">
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                        <span>📊</span>
                        <span>Dashboard</span>
                    </div>
                </div>

                <!-- Supplier List Tooltip -->
                <div class="relative group/tooltip">
                    <a href="{{ route('suppliers.index') }}" 
                       class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 ease-in-out hover:scale-105 {{ request()->routeIs('suppliers.index') ? 'bg-[#235c2b] text-white shadow-md' : 'text-slate-700 bg-white/50 hover:bg-[#e9f5ee] hover:text-[#1f5c3d] shadow-sm' }}">
                        <span class="text-lg select-none">🤝</span>
                    </a>
                    <div class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-lg opacity-0 translate-x-2 transition-all duration-200 ease-in-out group-hover/tooltip:opacity-100 group-hover/tooltip:translate-x-0 flex items-center gap-2">
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                        <span>🤝</span>
                        <span>Supplier List</span>
                    </div>
                </div>

                <!-- Active Suppliers Tooltip -->
                <div class="relative group/tooltip">
                    <a href="{{ route('suppliers.active') }}" 
                       class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 ease-in-out hover:scale-105 {{ request()->routeIs('suppliers.active') ? 'bg-[#235c2b] text-white shadow-md' : 'text-slate-700 bg-white/50 hover:bg-[#e9f5ee] hover:text-[#1f5c3d] shadow-sm' }}">
                        <span class="text-lg select-none">✅</span>
                    </a>
                    <div class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-lg opacity-0 translate-x-2 transition-all duration-200 ease-in-out group-hover/tooltip:opacity-100 group-hover/tooltip:translate-x-0 flex items-center gap-2">
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                        <span>✅</span>
                        <span>Active Suppliers</span>
                    </div>
                </div>

                <!-- Pending Verification Tooltip -->
                <div class="relative group/tooltip">
                    <a href="{{ route('suppliers.pending') }}" 
                       class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 ease-in-out hover:scale-105 {{ request()->routeIs('suppliers.pending') ? 'bg-[#235c2b] text-white shadow-md' : 'text-slate-700 bg-white/50 hover:bg-[#e9f5ee] hover:text-[#1f5c3d] shadow-sm' }}">
                        <span class="text-lg select-none">⏳</span>
                    </a>
                    <div class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-lg opacity-0 translate-x-2 transition-all duration-200 ease-in-out group-hover/tooltip:opacity-100 group-hover/tooltip:translate-x-0 flex items-center gap-2">
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                        <span>⏳</span>
                        <span>Pending Verification</span>
                    </div>
                </div>

                <!-- Blacklisted Suppliers Tooltip -->
                <div class="relative group/tooltip">
                    <a href="{{ route('suppliers.blacklisted') }}" 
                       class="w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 ease-in-out hover:scale-105 {{ request()->routeIs('suppliers.blacklisted') ? 'bg-[#235c2b] text-white shadow-md' : 'text-slate-700 bg-white/50 hover:bg-[#e9f5ee] hover:text-[#1f5c3d] shadow-sm' }}">
                        <span class="text-lg select-none">🚫</span>
                    </a>
                    <div class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-lg opacity-0 translate-x-2 transition-all duration-200 ease-in-out group-hover/tooltip:opacity-100 group-hover/tooltip:translate-x-0 flex items-center gap-2">
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                        <span>🚫</span>
                        <span>Blacklisted Suppliers</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </aside>

    <div class="flex-1 w-full min-w-0 max-w-none flex flex-col">
        <header class="top-bar-unified">
            <div class="top-bar-left">
                <h2>@yield('title', 'Dashboard')</h2>
                <p>@yield('subtitle', 'Agricultural Procurement - June 2024')</p>
            </div>
            <div class="top-bar-right">
                @yield('topbar-actions')
            </div>
        </header>

        <main class="flex-1 w-full min-w-0 max-w-none flex flex-col p-6">
            @if (session('success') || session('status') || session('error'))
                <div class="px-5 lg:px-8 pt-6">
                    @if (session('success'))
                        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-5 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sky-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@yield('scripts')
@stack('scripts')
    <!-- Script for Toggling and Preserving Sidebar State -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar-container');
            const isExpanded = localStorage.getItem('sidebar_expanded') === 'true';
            if (isExpanded) {
                sidebar.classList.add('is-expanded');
            } else {
                sidebar.classList.remove('is-expanded');
            }
            // Initialize Lucide Icons on load
            lucide.createIcons();
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-container');
            const willExpand = !sidebar.classList.contains('is-expanded');
            if (willExpand) {
                sidebar.classList.add('is-expanded');
                localStorage.setItem('sidebar_expanded', 'true');
            } else {
                sidebar.classList.remove('is-expanded');
                localStorage.setItem('sidebar_expanded', 'false');
            }
            // Re-trigger icon creation on toggle just in case
            lucide.createIcons();
        }
    </script>

    <!-- Unified Right-Side Panel Backdrop -->
    <div x-show="activeRightPanel !== ''" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-y-0 left-[72px] right-0 bg-slate-900/60 backdrop-blur-sm z-[100] lg:flex" 
         @click="activeRightPanel = ''" 
         style="display: none;">
    </div>

    <!-- Unified Right-Side Panel Drawer -->
    <div x-show="activeRightPanel !== ''"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-250 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-[72px] z-[101] w-full max-w-md bg-white border-r border-slate-200 shadow-2xl flex flex-col justify-between h-full dark:bg-slate-800 dark:border-slate-700"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/40 dark:border-slate-700">
            <div>
                <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider" x-text="activeRightPanel === 'profile' ? 'User Profile' : activeRightPanel === 'settings' ? 'Configure Settings' : activeRightPanel === 'help' ? 'Help & Documentation' : activeRightPanel === 'notifications' ? 'Alerts & Notifications' : 'Chat: ' + chatRecipient">Panel</h3>
            </div>
            <button @click="activeRightPanel = ''" class="text-slate-400 hover:text-slate-650 focus:outline-none p-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- PROFILE PANEL -->
            <template x-if="activeRightPanel === 'profile'">
                <div class="space-y-6">
                    <div class="flex flex-col items-center text-center pb-6 border-b border-slate-100 dark:border-slate-700">
                        <div class="w-20 h-20 rounded-full bg-emerald-50 border-2 border-[#235c2b] flex items-center justify-center text-2xl font-black text-[#235c2b] shadow-md mb-3 dark:bg-slate-900">
                            @if(Auth::check()) {{ Auth::user()->avatar_initial ?? strtoupper(substr(Auth::user()->name, 0, 2)) }} @else SA @endif
                        </div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">@if(Auth::check()) {{ Auth::user()->name }} @else System Admin @endif</h4>
                        <p class="text-xs font-bold text-[#235c2b] uppercase tracking-wider mt-0.5">@if(Auth::check()) {{ str_replace('_', ' ', Auth::user()->role) }} @else admin @endif</p>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-slate-50 rounded-xl p-4 dark:bg-slate-900/50">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Username</span>
                            <div class="text-xs font-bold text-slate-800 mt-0.5 dark:text-slate-200">@if(Auth::check()) {{ Auth::user()->username }} @else system.admin @endif</div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 dark:bg-slate-900/50">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Email Address</span>
                            <div class="text-xs font-bold text-slate-800 mt-0.5 dark:text-slate-200">@if(Auth::check()) {{ Auth::user()->email }} @else admin@ambatugrow.test @endif</div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 dark:bg-slate-900/50">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Department</span>
                            <div class="text-xs font-bold text-slate-800 mt-0.5 dark:text-slate-200">@if(Auth::check()) {{ Auth::user()->department ?? 'IT Operations' }} @else IT @endif</div>
                        </div>
                    </div>
                    
                    @if (Auth::check())
                        <form method="POST" action="{{ route('logout') }}" class="w-full pt-4">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm">LOG OUT ACCOUNT</button>
                        </form>
                    @endif
                </div>
            </template>

            <!-- SETTINGS PANEL -->
            <template x-if="activeRightPanel === 'settings'">
                <div class="space-y-5">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl dark:bg-slate-900/50">
                        <div>
                            <div class="text-xs font-black text-slate-800 dark:text-slate-200">Email Notifications</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Receive digests of pending approvals</div>
                        </div>
                        <input type="checkbox" x-model="systemSettings.emailAlerts" class="rounded border-slate-350 text-emerald-600 focus:ring-emerald-500 bg-white dark:bg-slate-800">
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl dark:bg-slate-900/50">
                        <div>
                            <div class="text-xs font-black text-slate-800 dark:text-slate-200">Desktop Alerts</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Push notifications for urgent reviews</div>
                        </div>
                        <input type="checkbox" x-model="systemSettings.desktopAlerts" class="rounded border-slate-350 text-emerald-600 focus:ring-emerald-500 bg-white dark:bg-slate-800">
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl dark:bg-slate-900/50 space-y-2">
                        <label class="text-xs font-black text-slate-800 dark:text-slate-200 block">Default Urgency Level</label>
                        <select x-model="systemSettings.defaultUrgency" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white dark:bg-slate-800 dark:border-slate-700 text-slate-800 dark:text-slate-200">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                </div>
            </template>

            <!-- HELP PANEL -->
            <template x-if="activeRightPanel === 'help'">
                <div class="space-y-4 font-sans text-center py-4 bg-slate-50 rounded-2xl dark:bg-slate-900/40 p-5 border border-slate-100 dark:border-slate-700/50">
                    <div class="flex flex-col items-center gap-2 mb-4">
                        <i data-lucide="music" class="w-8 h-8 text-[#235c2b] animate-bounce"></i>
                        <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Never Gonna Give You Up</h4>
                        <p class="text-[10px] font-bold text-slate-400">Rick Astley &middot; 1987</p>
                    </div>
                    <div class="text-xs text-slate-600 dark:text-slate-350 space-y-4 max-h-[380px] overflow-y-auto custom-scrollbar leading-relaxed font-medium italic">
                        <p>We're no strangers to love<br>
                        You know the rules and so do I (do I)<br>
                        A full commitment's what I'm thinking of<br>
                        You wouldn't get this from any other guy</p>
                        
                        <p>I just wanna tell you how I'm feeling<br>
                        Gotta make you understand</p>
                        
                        <p class="font-bold text-[#235c2b] not-italic">Never gonna give you up<br>
                        Never gonna let you down<br>
                        Never gonna run around and desert you<br>
                        Never gonna make you cry<br>
                        Never gonna say goodbye<br>
                        Never gonna tell a lie and hurt you</p>
                        
                        <p>We've known each other for so long<br>
                        Your heart's been aching, but you're too shy to say it (to say it)<br>
                        Inside, we both know what's been going on<br>
                        We know the game and we're gonna play it</p>
                        
                        <p>And if you ask me how I'm feeling<br>
                        Don't tell me you're too blind to see</p>
                        
                        <p class="font-bold text-[#235c2b] not-italic">Never gonna give you up<br>
                        Never gonna let you down<br>
                        Never gonna run around and desert you<br>
                        Never gonna make you cry<br>
                        Never gonna say goodbye<br>
                        Never gonna tell a lie and hurt you</p>
                        
                        <p>Never gonna give you up<br>
                        Never gonna let you down<br>
                        Never gonna run around and desert you<br>
                        Never gonna make you cry<br>
                        Never gonna say goodbye<br>
                        Never gonna tell a lie and hurt you</p>
                        
                        <p>We've known each other for so long<br>
                        Your heart's been aching, but you're too shy to say it (to say it)<br>
                        Inside, we both know what's been going on<br>
                        We know the game and we're gonna play it</p>
                        
                        <p>I just wanna tell you how I'm feeling<br>
                        Gotta make you understand</p>
                        
                        <p class="font-bold text-[#235c2b] not-italic">Never gonna give you up<br>
                        Never gonna let you down<br>
                        Never gonna run around and desert you<br>
                        Never gonna make you cry<br>
                        Never gonna say goodbye<br>
                        Never gonna tell a lie and hurt you</p>
                    </div>
                </div>
            </template>

            <!-- NOTIFICATIONS PANEL -->
            <template x-if="activeRightPanel === 'notifications'">
                <div class="space-y-3">
                    <template x-for="item in notificationsList" :key="item.id">
                        <div class="p-3 bg-slate-50 rounded-xl flex items-start justify-between gap-3 dark:bg-slate-900/50" :class="item.read ? 'opacity-60' : ''">
                            <div class="flex items-start gap-2.5">
                                <span class="w-2 h-2 bg-emerald-500 rounded-full mt-1.5 shrink-0 animate-pulse" x-show="!item.read"></span>
                                <div>
                                    <div class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="item.text"></div>
                                    <span class="text-[9px] text-slate-400 block mt-1" x-text="item.time"></span>
                                </div>
                            </div>
                            <button @click="item.read = true" x-show="!item.read" class="text-[9px] text-emerald-600 hover:text-emerald-700 hover:underline font-extrabold shrink-0">Mark read</button>
                        </div>
                    </template>
                </div>
            </template>

            <!-- CHAT PANEL -->
            <template x-if="activeRightPanel === 'chat'">
                <div class="flex flex-col h-full justify-between">
                    <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                        <template x-for="msg in chatMessages[chatRecipient]">
                            <div class="flex flex-col" :class="msg.sender === 'You' ? 'items-end' : 'items-start'">
                                <div class="px-3 py-2 rounded-xl text-xs max-w-[85%]" :class="msg.sender === 'You' ? 'bg-[#235c2b] text-white rounded-tr-none' : 'bg-slate-100 text-slate-800 rounded-tl-none dark:bg-slate-900 dark:text-slate-200'">
                                    <span x-text="msg.text"></span>
                                </div>
                                <span class="text-[8px] text-slate-400 mt-0.5" x-text="msg.time"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer / Input Form for Chat -->
        <template x-if="activeRightPanel === 'chat'">
            <form @submit.prevent="sendChatMessage()" class="p-4 border-t border-slate-200/80 bg-slate-50 dark:bg-slate-900/60 dark:border-slate-700 flex gap-2">
                <input type="text" x-model="chatInput" placeholder="Type a message..." class="flex-1 px-3 py-1.5 border border-slate-250 rounded-lg text-xs bg-white focus:outline-none dark:bg-slate-800 dark:border-slate-700 text-slate-850 dark:text-slate-100">
                <button type="submit" class="px-3.5 py-1.5 bg-[#235c2b] text-white rounded-lg text-xs font-bold hover:bg-[#1a4a21] transition">Send</button>
            </form>
        </template>
    </div>

    <!-- Toast Notification Container -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-2 opacity-0"
         class="fixed bottom-5 right-5 z-[9999] flex items-center gap-3 px-4 py-3 bg-slate-900 text-white rounded-xl shadow-lg border border-slate-700/50"
         style="display: none;">
        <i class="fa-solid fa-circle-info text-emerald-400"></i>
        <span class="text-xs font-bold" x-text="toastMessage"></span>
    </div>
</body>

@if (!Auth::check())
    <!-- Login Modal -->
    <div id="login-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop with glassmorphism blur -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeLoginModal()"></div>

        <!-- Modal Content Card -->
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-soft border border-slate-200/50 max-w-md w-full mx-4 overflow-hidden transform transition-all p-6 z-10">
            <div class="absolute top-4 right-4">
                <button onclick="closeLoginModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="mb-6 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#e9f5ee] text-[#1f5c3d] mb-3">
                    <i data-lucide="lock" class="w-6 h-6"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modal-title">Sign In Required</h3>
                <p class="text-xs text-gray-500 mt-1">Please sign in to access the Purchase Requisition system</p>
            </div>

            <!-- Error message container -->
            <div id="login-modal-error" class="hidden mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs text-red-800"></div>

            <form id="login-modal-form" onsubmit="handleModalSubmit(event)" class="space-y-4">
                <!-- Email Address -->
                <div>
                    <label for="modal-email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Email Address</label>
                    <input id="modal-email" class="block mt-1 w-full px-3.5 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-xs text-gray-900 dark:text-gray-100 focus:border-[#1f5c3d] focus:ring focus:ring-[#1f5c3d]/20 transition" type="email" name="email" required autocomplete="username" />
                </div>

                <!-- Password -->
                <div>
                    <label for="modal-password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Password</label>
                    <input id="modal-password" class="block mt-1 w-full px-3.5 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-xs text-gray-900 dark:text-gray-100 focus:border-[#1f5c3d] focus:ring focus:ring-[#1f5c3d]/20 transition" type="password" name="password" required autocomplete="current-password" />
                </div>

                <button type="submit" id="modal-submit-btn" class="w-full py-2.5 bg-[#1f5c3d] hover:bg-[#163f2b] text-white text-xs font-bold rounded-xl transition shadow-md shadow-emerald-950/20 flex items-center justify-center gap-2">
                    <span>Sign In</span>
                </button>
            </form>

            <div class="relative flex py-4 items-center">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="flex-shrink mx-3 text-gray-400 text-[10px] font-bold uppercase">Quick Login (Demo Roles)</span>
                <div class="flex-grow border-t border-gray-200"></div>
            </div>

            <div class="grid grid-cols-1 gap-1.5">
                <button onclick="modalQuickLogin('sarah.jenkins@ambatugrow.test')" class="flex items-center justify-between p-2.5 rounded-xl bg-sky-50 border border-sky-100 hover:bg-sky-100/70 text-sky-950 text-left transition text-xs">
                    <div>
                        <div class="font-bold">Sarah Jenkins</div>
                        <div class="text-[9px] text-sky-600 font-semibold uppercase">Manager Role</div>
                    </div>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-sky-700"></i>
                </button>

                <button onclick="modalQuickLogin('finance.manager@ambatugrow.test')" class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50 border border-amber-100 hover:bg-amber-100/70 text-amber-950 text-left transition text-xs">
                    <div>
                        <div class="font-bold">Michael Finn</div>
                        <div class="text-[9px] text-amber-600 font-semibold uppercase">Finance Manager</div>
                    </div>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-amber-700"></i>
                </button>

                <button onclick="modalQuickLogin('johny.papa@ambatugrow.test')" class="flex items-center justify-between p-2.5 rounded-xl bg-purple-50 border border-purple-100 hover:bg-purple-100/70 text-purple-950 text-left transition text-xs">
                    <div>
                        <div class="font-bold">Johny Papa</div>
                        <div class="text-[9px] text-purple-600 font-semibold uppercase">Department Head (Final)</div>
                    </div>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-purple-700"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        function openLoginModal() {
            const modal = document.getElementById('login-modal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            // Re-trigger lucide icons inside modal
            if (window.lucide) {
                lucide.createIcons();
            }
        }

        function closeLoginModal() {
            const modal = document.getElementById('login-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            document.getElementById('login-modal-error').classList.add('hidden');
        }

        function modalQuickLogin(email) {
            document.getElementById('modal-email').value = email;
            document.getElementById('modal-password').value = 'password';
            submitModalLogin(email, 'password');
        }

        function handleModalSubmit(e) {
            e.preventDefault();
            const email = document.getElementById('modal-email').value;
            const password = document.getElementById('modal-password').value;
            submitModalLogin(email, password);
        }

        function submitModalLogin(email, password) {
            const errorDiv = document.getElementById('login-modal-error');
            const submitBtn = document.getElementById('modal-submit-btn');
            errorDiv.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Signing In...</span>
            `;

            const params = new URLSearchParams();
            params.append('email', email);
            params.append('password', password);
            params.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            fetch('{{ route("login") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: params.toString()
            })
            .then(async response => {
                if (response.ok) {
                    window.location.href = '{{ route("approvals.index") }}';
                } else {
                    let errorMessage = 'Invalid email or password.';
                    try {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            const data = await response.json();
                            errorMessage = data.message || errorMessage;
                        } else {
                            if (response.status === 419) {
                                errorMessage = 'Session expired. Please refresh the page.';
                            } else {
                                errorMessage = 'Error (' + response.status + '): Please check your credentials.';
                            }
                        }
                    } catch (e) {
                        // fallback
                    }
                    throw new Error(errorMessage);
                }
            })
            .catch(err => {
                errorDiv.textContent = err.message;
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Sign In</span>';
            });
        }
    </script>
@endif


    <!-- Requisition Modal Overlay -->
    <div x-show="showRequisitionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showRequisitionModal = false"></div>
        
        <form action="{{ route('requisitions.store') }}" method="POST" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col z-50 animate-in fade-in zoom-in duration-200">
            @csrf
            <input type="hidden" name="action" value="continue">
            <input type="hidden" name="title" x-bind:value="'PR - ' + (reqItems[0] && reqItems[0].name ? reqItems[0].name : 'Office Supplies') + ' (' + new Date().toLocaleDateString() + ')'">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-base font-bold text-slate-805">Raise Purchase Requisition</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Total: <span class="font-bold text-[#1f5c3d]" x-text="'₱' + Number(reqTotal()).toLocaleString('en-US', {minimumFractionDigits: 2})">₱0.00</span> · Approval: <span class="text-emerald-700 font-semibold">Level 1</span>
                    </p>
                </div>
                <button type="button" @click="showRequisitionModal = false" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 overflow-y-auto flex-1 text-sm text-left">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Fields (2/3 width) -->
                    <div class="lg:col-span-2 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">REQUESTED BY *</label>
                                <input type="text" name="requestor_name" required x-model="requestorName" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">DEPARTMENT</label>
                                <select name="department" x-model="department" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="Farm Operations">Farm Operations</option>
                                    <option value="Logistics">Logistics</option>
                                    <option value="Procurement">Procurement</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Marketing">Marketing</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">DATE NEEDED *</label>
                                <input type="date" name="needed_by" required x-model="neededBy" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">PRIORITY</label>
                                <select name="urgency" x-model="urgency" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="Medium">Normal</option>
                                    <option value="High">High</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">RECOMMENDED SUPPLIER</label>
                                <select name="supplier_id" x-model="supplierId" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">-- Select Recommended Supplier (Optional) --</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="block text-xs font-semibold text-slate-500 uppercase">Line Items *</span>
                                <button type="button" @click="addReqItem()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                                    <i class="fa-solid fa-plus"></i> Add Line
                                </button>
                            </div>

                            <!-- Table Header (hidden on mobile) -->
                            <div class="hidden md:grid grid-cols-12 gap-2 px-4 py-2 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                <div class="col-span-2">SKU</div>
                                <div class="col-span-3">Item Name</div>
                                <div class="col-span-1">UOM</div>
                                <div class="col-span-1">Qty</div>
                                <div class="col-span-2">Unit Cost (₱)</div>
                                <div class="col-span-2 text-right">Total</div>
                                <div class="col-span-1 text-center">Action</div>
                            </div>

                            <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50 p-3 space-y-3 max-h-[280px] overflow-y-auto">
                                <template x-for="(item, index) in reqItems" :key="index">
                                    <div class="grid grid-cols-12 gap-2 items-center bg-white p-3 rounded-lg border border-slate-100 shadow-sm relative">
                                        <!-- Database Order Catalog Dropdown -->
                                        <div class="col-span-12 pb-1 border-b border-slate-100 mb-1">
                                            <label class="block text-[10px] font-extrabold text-emerald-800 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                                <i class="fa-solid fa-cart-shopping text-emerald-600"></i> Select Order Item (Database Catalog)
                                            </label>
                                            <select @change="selectCatalogItem(index, $event.target.value)" class="w-full px-2.5 py-1.5 border border-emerald-300 rounded-lg text-xs bg-emerald-50/50 text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                                <option value="">-- Select Order Item from Database --</option>
                                                <template x-for="prod in catalogProducts" :key="prod.id">
                                                    <option :value="prod.id" x-text="prod.sku + ' - ' + prod.name + ' (₱' + Number(prod.base_price).toLocaleString('en-US', {minimumFractionDigits: 2}) + ')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="col-span-12 md:col-span-2">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">SKU</label>
                                            <input type="text" x-model="item.sku" :name="'items[' + index + '][sku]'" placeholder="e.g. AGRI-042" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-12 md:col-span-3">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">Item Name</label>
                                            <input type="text" x-model="item.name" :name="'items[' + index + '][name]'" required placeholder="e.g. Rice Seeds" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs truncate">
                                        </div>
                                        <div class="col-span-6 md:col-span-1">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">UOM</label>
                                            <input type="text" x-model="item.unit" :name="'items[' + index + '][unit]'" required placeholder="Unit" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-6 md:col-span-1">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">Qty</label>
                                            <input type="number" x-model.number="item.qty" :name="'items[' + index + '][qty]'" required min="1" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-6 md:col-span-2">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">Unit Cost (₱)</label>
                                            <input type="number" x-model.number="item.cost" :name="'items[' + index + '][unit_price]'" required min="0" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-5 md:col-span-2 flex items-center justify-end text-right">
                                            <span class="text-xs font-bold text-slate-700 whitespace-nowrap" x-text="'₱' + Number(item.qty * item.cost).toLocaleString('en-US', {minimumFractionDigits: 2})">₱0.00</span>
                                        </div>
                                        <div class="col-span-1 md:col-span-1 flex items-center justify-center">
                                            <button type="button" @click="if(reqItems.length > 1) removeReqItem(index)" class="text-slate-400 hover:text-red-500 transition-colors p-1" title="Remove Item">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                        <div class="col-span-12 mt-1">
                                            <input type="text" x-model="item.justification" placeholder="Reason / Description for request..." class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs bg-slate-50/60">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">REMARKS</label>
                            <textarea name="purpose" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                        </div>
                    </div>

                    <!-- Right Column: Purchase Summary (1/3 width) -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-4 h-fit">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Purchase Summary</h4>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Requested By</span>
                                <span class="font-semibold text-slate-700 text-xs text-right truncate max-w-[120px]" x-text="requestorName || '—'">—</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Department</span>
                                <span class="font-semibold text-slate-700 text-xs" x-text="department">Farm Operations</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Date Needed</span>
                                <span class="font-semibold text-slate-700 text-xs" x-text="neededBy ? new Date(neededBy).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : '—'">—</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Priority</span>
                                <span class="font-semibold text-slate-700 text-xs" x-text="urgency === 'Medium' ? 'Normal' : urgency">Normal</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Total (₱)</span>
                                <span class="font-extrabold text-[#1f5c3d] text-sm" x-text="'₱' + Number(reqTotal()).toLocaleString('en-US', {minimumFractionDigits: 2})">₱0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-slate-500 text-xs">Status</span>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-semibold text-[10px]">Level 1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3 bg-slate-50">
                <button type="button" @click="showRequisitionModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-100 text-xs font-bold transition">CANCEL</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">SUBMIT PR</button>
            </div>
        </form>
    </div>

    <!-- PO Modal Overlay -->
    <div x-show="showPoModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showPoModal = false"></div>
        
        <form action="{{ route('purchase_orders.store') }}" method="POST" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col z-50 animate-in fade-in zoom-in duration-200">
            @csrf
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-base font-bold text-slate-805">Create Purchase Order</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Subtotal: <span class="font-bold text-slate-700" x-text="'₱' + Number(poSubtotal()).toLocaleString('en-US')">₱0</span> · VAT (12%): <span class="font-bold text-slate-700" x-text="'₱' + Number(poVat()).toLocaleString('en-US')">₱0</span> · Total: <span class="font-bold text-[#1f5c3d]" x-text="'₱' + Number(poTotal()).toLocaleString('en-US')">₱0</span>
                    </p>
                </div>
                <button type="button" @click="showPoModal = false" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 overflow-y-auto flex-1 text-sm text-left">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Form Fields (2/3 width) -->
                    <div class="lg:col-span-2 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">SUPPLIER *</label>
                                <select name="supplier_id" required x-model="supplierId" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#1f5c3d]/20 focus:border-[#1f5c3d]">
                                    <option value="">-- Choose Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">EXPECTED DELIVERY *</label>
                                <input type="date" name="expected_delivery" required x-model="expectedDelivery" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1f5c3d]/20 focus:border-[#1f5c3d]">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">PAYMENT TERMS</label>
                                <input type="text" name="payment_terms" value="Net 30" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1f5c3d]/20 focus:border-[#1f5c3d]">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="block text-xs font-semibold text-slate-500 uppercase">Line Items</span>
                                <button type="button" @click="addPoItem()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                                    <i class="fa-solid fa-plus"></i> Add Line
                                </button>
                            </div>

                            <!-- Table Header (hidden on mobile) -->
                            <div class="hidden md:grid grid-cols-12 gap-3 px-4 py-2 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                <div class="col-span-2">SKU</div>
                                <div class="col-span-3">Item Name</div>
                                <div class="col-span-2">UOM</div>
                                <div class="col-span-2">Ordered Qty</div>
                                <div class="col-span-2">Unit Price (₱)</div>
                                <div class="col-span-1 text-right">Total</div>
                            </div>

                            <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50 p-4 space-y-4 max-h-[250px] overflow-y-auto">
                                <template x-for="(item, index) in poItems" :key="index">
                                    <div class="grid grid-cols-12 gap-3 items-center bg-white p-3 rounded-lg border border-slate-100 shadow-sm relative pt-6 md:pt-3">
                                        <button type="button" @click="if(poItems.length > 1) removePoItem(index)" class="absolute top-1 right-2 text-slate-400 hover:text-red-500 md:hidden">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                        <div class="col-span-12 md:col-span-2">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">SKU</label>
                                            <input type="text" x-model="item.sku" :name="'items[' + index + '][sku]'" placeholder="e.g. AGRI-SEED-042" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-12 md:col-span-3">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">Item Name</label>
                                            <input type="text" x-model="item.name" :name="'items[' + index + '][name]'" required placeholder="e.g. Hybrid Rice Seeds" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-4 md:col-span-2">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">UOM</label>
                                            <input type="text" x-model="item.unit" placeholder="Unit" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-4 md:col-span-2">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">Ordered Qty</label>
                                            <input type="number" x-model.number="item.qty" :name="'items[' + index + '][quantity]'" required min="1" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-4 md:col-span-2">
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 md:hidden">Unit Price (₱)</label>
                                            <input type="number" x-model.number="item.cost" :name="'items[' + index + '][unit_price]'" required min="0" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        </div>
                                        <div class="col-span-12 md:col-span-1 flex items-center justify-between pt-1 md:pt-0">
                                            <span class="text-xs font-bold text-slate-600 pr-2 md:pr-0 md:w-full md:text-right" x-text="'₱' + Number(item.qty * item.cost).toLocaleString('en-US')">₱0</span>
                                            <button type="button" @click="if(poItems.length > 1) removePoItem(index)" class="text-slate-400 hover:text-red-500 hidden md:block ml-2 shrink-0">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">NOTES / INSTRUCTIONS</label>
                            <textarea name="notes" rows="2" placeholder="Any specific delivery instructions or warehouse directions..." class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1f5c3d]/20 focus:border-[#1f5c3d]"></textarea>
                        </div>
                    </div>

                    <!-- Right Column: Purchase Summary (1/3 width) -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-4 h-fit">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Purchase Summary</h4>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Supplier</span>
                                <span class="font-semibold text-slate-700 text-xs text-right truncate max-w-[120px]" x-text="getSupplierName()">—</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Expected Delivery</span>
                                <span class="font-semibold text-slate-700 text-xs" x-text="expectedDelivery ? new Date(expectedDelivery).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : '—'">—</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Subtotal (₱)</span>
                                <span class="font-bold text-slate-750 text-xs" x-text="'₱' + Number(poSubtotal()).toLocaleString('en-US', {minimumFractionDigits: 2})">₱0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">VAT (12%) (₱)</span>
                                <span class="font-bold text-slate-750 text-xs" x-text="'₱' + Number(poVat()).toLocaleString('en-US', {minimumFractionDigits: 2})">₱0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/60">
                                <span class="text-slate-500 text-xs">Total (₱)</span>
                                <span class="font-extrabold text-[#1f5c3d] text-sm" x-text="'₱' + Number(poTotal()).toLocaleString('en-US', {minimumFractionDigits: 2})">₱0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-slate-500 text-xs">Status</span>
                                <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded-full font-semibold text-[10px]">Draft</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3 bg-slate-50">
                <button type="button" @click="showPoModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-100 text-xs font-bold transition">CANCEL</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-sm">CREATE PO</button>
            </div>
        </form>
    </div>
</html>