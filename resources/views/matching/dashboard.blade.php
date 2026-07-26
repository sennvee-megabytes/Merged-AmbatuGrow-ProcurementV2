@extends('layouts.master')

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
@endpush

@push('styles')
<style>
    main.flex-1 {
        padding: 0 !important;
        overflow: hidden !important;
        min-width: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    .grim-page {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 58px);
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        min-width: 0;
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        min-width: 0;
    }

    .dashboard-body {
        flex: 1;
        display: flex;
        height: 100%;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        min-width: 0;
        position: relative;
    }

    .dashboard-viewport {
        flex: 1;
        padding: 16px 20px;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        gap: 14px;
        min-width: 0;
        width: 100%;
        max-width: 100%;
        height: 100%;
        box-sizing: border-box;
    }

    /* KPI Cards Grid - Full Width Fluid Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        width: 100%;
        flex-shrink: 0;
    }

    .kpi-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .kpi-icon-wrapper {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f0fdf4;
        color: #166534;
    }

    .kpi-card:nth-child(2) .kpi-icon-wrapper { background-color: #f0fdf4; color: #15803d; }
    .kpi-card:nth-child(3) .kpi-icon-wrapper { background-color: #eff6ff; color: #1d4ed8; }
    .kpi-card:nth-child(4) .kpi-icon-wrapper { background-color: #fef2f2; color: #dc2626; }

    .kpi-trend {
        font-size: 0.7rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 20px;
    }

    .trend-up { background-color: #f0fdf4; color: #166534; }
    .trend-down { background-color: #eff6ff; color: #1e40af; }
    .trend-mismatch { background-color: #fef2f2; color: #991b1b; }

    .kpi-body {
        display: flex;
        flex-direction: column;
    }

    .kpi-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .kpi-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        margin-top: 2px;
    }

    .kpi-subtext {
        font-size: 0.68rem;
        color: #94a3b8;
    }

    /* Toolbar - Professional Enterprise Layout */
    .toolbar-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        width: 100%;
        box-sizing: border-box;
        flex-shrink: 0;
    }

    .toolbar-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        flex-wrap: wrap;
    }

    /* Left Group: Search + Supplier */
    .toolbar-group-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1 1 380px;
        min-width: 280px;
    }

    .search-box {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 200px;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }

    .search-box .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #94a3b8;
        pointer-events: none;
        z-index: 10;
    }

    .search-box input {
        width: 100%;
        height: 42px;
        padding: 0 14px 0 42px;
        font-size: 0.8rem;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background-color: #ffffff;
        color: #1e293b;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .search-box input::placeholder {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .search-box input:focus {
        border-color: #1e7d43;
        box-shadow: 0 0 0 3px rgba(30, 125, 67, 0.1);
    }

    .supplier-box {
        position: relative;
        display: flex;
        align-items: center;
        flex-shrink: 0;
        width: 165px;
    }

    .supplier-box select {
        appearance: none;
        -webkit-appearance: none;
        width: 100%;
        height: 42px;
        padding: 0 28px 0 12px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background-color: #ffffff;
        color: #334155;
        cursor: pointer;
        outline: none;
        box-sizing: border-box;
        text-overflow: ellipsis;
    }

    .supplier-box .select-chevron {
        position: absolute;
        right: 10px;
        width: 14px;
        height: 14px;
        color: #64748b;
        pointer-events: none;
    }

    /* Center Group: Status Tabs */
    .toolbar-group-center {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1 1 auto;
        min-width: 0;
    }

    .status-tabs-container {
        display: inline-flex;
        align-items: center;
        background-color: #f1f5f9;
        padding: 3px;
        border-radius: 8px;
        gap: 2px;
        height: 42px;
        box-sizing: border-box;
        max-width: 100%;
        overflow-x: auto;
    }

    .status-tab {
        height: 36px;
        padding: 0 14px;
        font-size: 0.76rem;
        font-weight: 600;
        color: #64748b;
        border: none;
        background: transparent;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.12s ease;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .status-tab:hover:not(.active) {
        color: #1e293b;
    }

    .status-tab.active {
        background-color: #ffffff;
        color: #0f172a;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* Right Group: Action Buttons */
    .toolbar-group-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-toolbar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 42px;
        padding: 0 16px;
        background-color: #ffffff;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        box-sizing: border-box;
        white-space: nowrap;
    }

    .btn-toolbar:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
    }

    .btn-toolbar i {
        width: 15px;
        height: 15px;
        color: #475569;
    }

    .btn-toolbar-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        background-color: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s ease;
        box-sizing: border-box;
        flex-shrink: 0;
    }

    .btn-toolbar-icon:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    .btn-toolbar-icon i {
        width: 15px;
        height: 15px;
    }

    /* Responsive adjustments */
    @media (max-width: 1280px) {
        .toolbar-inner {
            gap: 10px;
        }
        .toolbar-group-left {
            flex: 1 1 100%;
        }
        .toolbar-group-center {
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .toolbar-group-left {
            flex-direction: column;
            align-items: stretch;
        }
        .supplier-box {
            width: 100%;
        }
        .toolbar-group-center {
            width: 100%;
            overflow-x: auto;
        }
        .toolbar-group-right {
            width: 100%;
            justify-content: flex-start;
        }
    }

    .btn-toolbar-icon:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    .btn-toolbar-icon i {
        width: 15px;
        height: 15px;
    }

    /* Table Card - Full Width Fluid Container */
    .table-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
        min-height: 0;
        width: 100%;
        box-sizing: border-box;
    }

    .table-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        flex-shrink: 0;
    }

    .table-header-row h3 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
    }

    .table-header-row p {
        margin: 2px 0 0 0;
        font-size: 0.72rem;
        color: #64748b;
    }

    .table-wrapper {
        width: 100%;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: auto;
    }

    .records-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .records-table th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8fafc;
        padding: 10px 14px;
        font-size: 0.68rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid #e2e8f0;
    }

    .records-table td {
        padding: 12px 14px;
        font-size: 0.78rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .records-table tr {
        cursor: pointer;
        transition: background-color 0.12s ease;
    }

    .records-table tr:hover {
        background-color: #f8fafc;
    }

    .records-table tr.selected {
        background-color: #f0fdf4 !important;
    }

    .col-po {
        font-weight: 700;
        color: #0f172a;
    }

    .sub-info {
        display: block;
        font-size: 0.68rem;
        font-weight: 500;
        color: #64748b;
        margin-top: 1px;
    }

    .col-amount {
        font-weight: 700;
        color: #0f172a;
    }

    .col-variance {
        font-weight: 700;
    }

    .variance-mismatch { color: #dc2626; }
    .variance-partial { color: #d97706; }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .badge-matched { background-color: #e6f4ea; color: #137333; }
    .badge-partial { background-color: #fef3c7; color: #b45309; }
    .badge-mismatch { background-color: #fee2e2; color: #dc2626; }
    .badge-pending { background-color: #e0f2fe; color: #0284c7; }
    .badge-cancelled { background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }

    .status-badge i { width: 13px; height: 13px; }

    /* Searchable Autocomplete Styles (Floating Overlay) */
    .autocomplete-dropdown {
        position: fixed;
        max-height: 220px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        z-index: 999999;
        font-size: 0.75rem;
        box-sizing: border-box;
    }

    .autocomplete-item {
        padding: 7px 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.1s ease;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover, .autocomplete-item.active {
        background-color: #f0fdf4;
        color: #15803d;
    }

    .autocomplete-item .item-price {
        font-weight: 600;
        color: #059669;
        font-size: 0.72rem;
    }

    .autocomplete-custom-item {
        background-color: #f8fafc;
        color: #166534;
        font-weight: 600;
        border-top: 1px dashed #cbd5e1;
    }

    .autocomplete-custom-item:hover, .autocomplete-custom-item.active {
        background-color: #dcfce7;
        color: #15803d;
    }

    /* Enforce Read-Only Rules for Transaction Table & Summary Panel */
    .matching-table td {
        user-select: text;
        cursor: pointer;
    }
    .matching-summary-panel .detail-value,
    .matching-summary-panel .recon-value,
    .matching-summary-panel .doc-id {
        user-select: text;
    }

    /* Table Footer & Pagination */
    .table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
        flex-shrink: 0;
    }

    .pagination-info {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 6px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.12s ease;
        cursor: pointer;
    }

    .page-link:hover:not(.disabled):not(.active) {
        background-color: #f1f5f9;
        color: #1e7d43;
        border-color: #94a3b8;
    }

    .page-link.active {
        background-color: #1e7d43;
        color: #ffffff;
        border-color: #1e7d43;
        font-weight: 700;
    }

    .page-link.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Matching Summary Drawer (Slide-Over Panel from Right Edge) */
    .matching-summary-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 380px;
        max-width: 90vw;
        height: 100vh;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background-color: #ffffff;
        border-left: 1px solid #e2e8f0;
        box-shadow: -4px 0 25px rgba(0,0,0,0.12);
        transform: translateX(100%);
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .matching-summary-panel.open {
        transform: translateX(0);
    }

    .summary-drawer-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(15, 23, 42, 0.25);
        backdrop-filter: blur(1px);
        z-index: 999;
    }

    .summary-drawer-backdrop.hidden {
        display: none !important;
    }

    .summary-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .summary-header h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .summary-header p {
        margin: 2px 0 0 0;
        font-size: 0.75rem;
        color: #64748b;
    }

    .close-btn {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 2px;
        border-radius: 4px;
    }

    .close-btn:hover { color: #0f172a; }

    .summary-body {
        padding: 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        flex: 1;
        overflow-y: auto;
    }

    .summary-section-title {
        font-size: 0.68rem;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }

    .supplier-card {
        background-color: #f8fafc;
        border-radius: 10px;
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .supplier-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: #dcfce7;
        color: #15803d;
        font-weight: 800;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .supplier-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
    }

    .supplier-commodity {
        font-size: 0.72rem;
        color: #64748b;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        padding: 4px 0;
    }

    .detail-label { color: #64748b; }
    .detail-value { font-weight: 700; color: #1e293b; }

    /* Documents list */
    .docs-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .doc-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .doc-info {
        display: flex;
        flex-direction: column;
    }

    .doc-name { font-size: 0.72rem; color: #64748b; }
    .doc-id { font-size: 0.8rem; font-weight: 700; color: #0f172a; }

    .doc-date {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .date-po { background-color: #e6f4ea; color: #137333; }
    .date-grn { background-color: #e0f2fe; color: #0284c7; }
    .date-inv { background-color: #f3e8ff; color: #7c3aed; }
    .date-missing { background-color: #f1f5f9; color: #94a3b8; }

    /* Amount Reconciliation */
    .reconciliation-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .recon-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .recon-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
    }

    .recon-label { color: #64748b; }
    .recon-value { font-weight: 800; color: #0f172a; }

    .progress-bar-bg {
        height: 5px;
        background-color: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar-fill { height: 100%; border-radius: 3px; }
    .bg-po-fill { background-color: #137333; }
    .bg-grn-fill { background-color: #0284c7; }
    .bg-inv-fill { background-color: #7c3aed; }

    .recon-alert {
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-reconciled { background-color: #e6f4ea; color: #137333; border: 1px solid #bbf7d0; }
    .alert-variance { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .alert-mismatch { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

    .payment-due-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 10px;
        font-size: 0.75rem;
    }

    .payment-due-label {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
    }

    .payment-due-date {
        font-weight: 700;
        color: #ea580c;
    }

    /* Dynamic Summary Drawer Footer Buttons */
    .summary-footer {
        padding: 16px 20px;
        border-top: 1px solid #f1f5f9;
        background-color: #ffffff;
        flex-shrink: 0;
    }

    .btn-action-matched {
        width: 100%;
        padding: 12px;
        background-color: #166534;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .btn-action-matched:hover { background-color: #15803d; }

    .btn-action-dispute-orange {
        width: 100%;
        padding: 11px;
        background-color: #ea580c;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .btn-action-dispute-orange:hover { background-color: #c2410c; }

    .btn-action-credit-orange {
        width: 100%;
        padding: 11px;
        background-color: #ffffff;
        color: #ea580c;
        border: 1px solid #ea580c;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-action-credit-orange:hover { background-color: #fff7ed; }

    .btn-action-dispute-red {
        width: 100%;
        padding: 11px;
        background-color: #dc2626;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .btn-action-dispute-red:hover { background-color: #b91c1c; }

    .btn-action-credit-red {
        width: 100%;
        padding: 11px;
        background-color: #ffffff;
        color: #dc2626;
        border: 1px solid #dc2626;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-action-credit-red:hover { background-color: #fef2f2; }

    .btn-action-reminder {
        width: 100%;
        padding: 11px;
        background-color: #ffffff;
        color: #15803d;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-action-reminder:hover { background-color: #f0fdf4; border-color: #86efac; }

    /* Modals */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.hidden { display: none !important; }

    .modal-card {
        width: 520px;
        max-width: 90vw;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        padding: 16px 20px;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .modal-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .filter-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .filter-form-grid .full-width { grid-column: span 2; }

    .form-group label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        font-size: 0.82rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background-color: #ffffff;
        color: #0f172a;
        box-sizing: border-box;
    }

    .modal-footer {
        padding: 14px 20px;
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary {
        padding: 7px 16px;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
    }

    .btn-primary {
        padding: 7px 16px;
        background-color: #1e7d43;
        border: 1px solid #1e7d43;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #ffffff;
        cursor: pointer;
    }

    /* Record Goods Receipt View */
    .record-grn-wrapper {
        flex: 1;
        width: 100%;
        height: 100%;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        background-color: #f4f7f5;
        box-sizing: border-box;
    }

    .record-grn-wrapper.hidden { display: none !important; }

    .grn-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        background-color: #ffffff;
        padding: 20px 24px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .grn-header-row h2 {
        margin: 0 0 4px 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e293b;
    }

    .grn-header-row p { margin: 0; font-size: 0.82rem; color: #64748b; }

    .btn-back-matching {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
    }

    .grn-content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 20px;
    }

    .grn-main-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .grn-fields-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .receipt-lines-card {
        margin-top: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background-color: #f8fafc;
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .receipt-lines-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-add-line {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        background-color: #15803d;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
    }

    .receipt-lines-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        font-size: 0.78rem;
    }

    .receipt-lines-table th {
        background-color: transparent;
        padding: 8px 10px;
        text-align: left;
        font-size: 0.68rem;
        font-weight: 700;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
    }

    .receipt-lines-table td {
        padding: 4px 6px;
        vertical-align: middle;
    }

    .btn-submit-grn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px;
        background-color: #1e7d43;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
    }

    .grn-side-card {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .selected-po-banner {
        background-color: #1e7d43;
        color: #ffffff;
        border-radius: 12px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .available-po-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
        background-color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .available-po-card.selected {
        border-color: #15803d;
        background-color: #f0fdf4;
    }

    /* ========================================================= */
    /*  GOODS RECEIPT & INVOICE MATCHING DARK MODE OVERRIDES     */
    /* ========================================================= */
    .dark .grim-page {
        background-color: #0b0f17 !important;
        color: #f8fafc !important;
    }
    .dark .kpi-card {
        background-color: #161e2e !important;
        border-color: #1e293b !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    }
    .dark .kpi-icon-wrapper {
        background-color: rgba(16, 185, 129, 0.15) !important;
        color: #34d399 !important;
    }
    .dark .kpi-card:nth-child(2) .kpi-icon-wrapper { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .dark .kpi-card:nth-child(3) .kpi-icon-wrapper { background-color: rgba(59, 130, 246, 0.15) !important; color: #60a5fa !important; }
    .dark .kpi-card:nth-child(4) .kpi-icon-wrapper { background-color: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }

    .dark .trend-up { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .dark .trend-down { background-color: rgba(59, 130, 246, 0.15) !important; color: #60a5fa !important; }
    .dark .trend-mismatch { background-color: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }

    .dark .kpi-value { color: #f8fafc !important; }
    .dark .kpi-title { color: #cbd5e1 !important; }
    .dark .kpi-subtext { color: #94a3b8 !important; }

    .dark .toolbar-card {
        background-color: #161e2e !important;
        border-color: #1e293b !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
    }
    .search-box:focus-within,
    .dark .search-box,
    .dark .search-box:focus-within {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }
    .dark .search-box input, .dark .supplier-box select {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }
    .dark .search-box input:focus {
        border-color: #34d399 !important;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.18) !important;
    }
    .dark .search-box input::placeholder { color: #64748b !important; }
    .dark .search-box .search-icon, .dark .supplier-box .select-chevron { color: #94a3b8 !important; }

    .dark .status-tabs-container {
        background-color: #0b0f17 !important;
        border: 1px solid #1e293b !important;
    }
    .dark .status-tab { color: #94a3b8 !important; }
    .dark .status-tab:hover:not(.active) { color: #f8fafc !important; }
    .dark .status-tab.active {
        background-color: #1e293b !important;
        color: #34d399 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4) !important;
    }

    .dark .btn-toolbar, .dark .btn-toolbar-icon {
        background-color: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }
    .dark .btn-toolbar:hover, .dark .btn-toolbar-icon:hover {
        background-color: #334155 !important;
        color: #ffffff !important;
        border-color: #475569 !important;
    }
    .dark .btn-toolbar i, .dark .btn-toolbar-icon i { color: #cbd5e1 !important; }

    .dark .table-card {
        background-color: #161e2e !important;
        border-color: #1e293b !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
    }
    .dark .table-header-row h3 { color: #f8fafc !important; }
    .dark .table-header-row p { color: #94a3b8 !important; }

    .dark .records-table th {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-bottom: 1px solid #1e293b !important;
    }
    .dark .records-table td {
        color: #e2e8f0 !important;
        border-bottom: 1px solid #1e293b !important;
    }
    .dark .records-table tr:hover {
        background-color: rgba(30, 41, 59, 0.5) !important;
    }
    .dark .records-table tr.selected {
        background-color: rgba(16, 185, 129, 0.15) !important;
    }

    .dark .col-po, .dark .col-amount { color: #f8fafc !important; }
    .dark .sub-info { color: #94a3b8 !important; }
    .dark .variance-mismatch { color: #f87171 !important; }
    .dark .variance-partial { color: #fbbf24 !important; }

    .dark .badge-matched { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important; }
    .dark .badge-partial { background-color: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(245, 158, 11, 0.3) !important; }
    .dark .badge-mismatch { background-color: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; border: 1px solid rgba(239, 68, 68, 0.3) !important; }
    .dark .badge-pending { background-color: rgba(14, 165, 233, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(14, 165, 233, 0.3) !important; }
    .dark .badge-cancelled { background-color: #1e293b !important; color: #94a3b8 !important; border: 1px solid #334155 !important; }

    .dark .table-footer { border-top-color: #1e293b !important; }
    .dark .pagination-info { color: #94a3b8 !important; }
    .dark .page-link {
        background-color: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }
    .dark .page-link:hover:not(.disabled):not(.active) {
        background-color: #334155 !important;
        color: #34d399 !important;
        border-color: #475569 !important;
    }
    .dark .page-link.active {
        background-color: #1e7d43 !important;
        color: #ffffff !important;
        border-color: #1e7d43 !important;
    }

    .dark .matching-summary-panel {
        background-color: #161e2e !important;
        border-left-color: #1e293b !important;
        box-shadow: -4px 0 25px rgba(0,0,0,0.5) !important;
    }
    .dark .summary-header { border-bottom-color: #1e293b !important; }
    .dark .summary-header h3 { color: #f8fafc !important; }
    .dark .summary-header p { color: #94a3b8 !important; }
    .dark .close-btn { color: #94a3b8 !important; }
    .dark .close-btn:hover { color: #ffffff !important; }
    .dark .summary-section-title { color: #94a3b8 !important; }

    .dark .supplier-card {
        background-color: #0b0f17 !important;
        border: 1px solid #1e293b !important;
    }
    .dark .supplier-name { color: #f8fafc !important; }
    .dark .supplier-commodity { color: #94a3b8 !important; }
    .dark .detail-label { color: #94a3b8 !important; }
    .dark .detail-value { color: #f8fafc !important; }

    .dark .doc-item { border-bottom-color: #1e293b !important; }
    .dark .doc-name { color: #94a3b8 !important; }
    .dark .doc-id { color: #f8fafc !important; }

    .dark .date-po { background-color: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
    .dark .date-grn { background-color: rgba(14, 165, 233, 0.15) !important; color: #38bdf8 !important; }
    .dark .date-inv { background-color: rgba(168, 85, 247, 0.15) !important; color: #c084fc !important; }
    .dark .date-missing { background-color: #1e293b !important; color: #64748b !important; }

    .dark .recon-label { color: #94a3b8 !important; }
    .dark .recon-value { color: #f8fafc !important; }
    .dark .progress-bar-bg { background-color: #1e293b !important; }

    .dark .alert-reconciled { background-color: rgba(16, 185, 129, 0.12) !important; color: #34d399 !important; border-color: rgba(16, 185, 129, 0.3) !important; }
    .dark .alert-variance { background-color: rgba(245, 158, 11, 0.12) !important; color: #fbbf24 !important; border-color: rgba(245, 158, 11, 0.3) !important; }
    .dark .alert-mismatch { background-color: rgba(239, 68, 68, 0.12) !important; color: #f87171 !important; border-color: rgba(239, 68, 68, 0.3) !important; }

    .dark .payment-due-label { color: #94a3b8 !important; }
    .dark .summary-footer { border-top-color: #1e293b !important; background-color: #161e2e !important; }

    .dark .btn-action-credit-orange {
        background-color: #161e2e !important;
        color: #fb923c !important;
        border-color: #fb923c !important;
    }
    .dark .btn-action-credit-orange:hover { background-color: rgba(251, 146, 60, 0.15) !important; }

    .dark .btn-action-credit-red {
        background-color: #161e2e !important;
        color: #f87171 !important;
        border-color: #f87171 !important;
    }
    .dark .btn-action-credit-red:hover { background-color: rgba(239, 68, 68, 0.15) !important; }

    .dark .btn-action-reminder {
        background-color: #161e2e !important;
        color: #34d399 !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
    }
    .dark .btn-action-reminder:hover { background-color: rgba(16, 185, 129, 0.15) !important; }

    .dark .modal-card {
        background-color: #161e2e !important;
        border-color: #1e293b !important;
        color: #f8fafc !important;
    }
    .dark .modal-header, .dark .modal-footer {
        background-color: #0f172a !important;
        border-color: #1e293b !important;
    }
    .dark .modal-header h3 { color: #f8fafc !important; }
    .dark .form-group label { color: #cbd5e1 !important; }
    .dark .form-control {
        background-color: #0b0f17 !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }
    .dark .btn-secondary {
        background-color: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    .dark .record-grn-wrapper {
        background-color: #090d16 !important;
        color: #f8fafc !important;
    }
    .dark .grn-header-row,
    .dark .grn-main-card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    .dark .grn-header-row h2 { color: #f8fafc !important; }
    .dark .grn-header-row p { color: #94a3b8 !important; }
    .dark .btn-back-matching {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }
    .dark .receipt-type-selector {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }
    .dark .receipt-type-btn {
        color: #cbd5e1 !important;
    }
    .dark .receipt-type-btn.active {
        background-color: #1e293b !important;
        color: #34d399 !important;
        border-color: #34d399 !important;
    }
    .dark .receipt-lines-card {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }
    .dark .receipt-lines-table th {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-bottom-color: #334155 !important;
    }
    .dark .receipt-lines-table td {
        color: #e2e8f0 !important;
        border-bottom-color: #334155 !important;
    }
    .dark #live-matching-card {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    .dark .grn-side-card > div[style*="background"] {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    .dark .available-po-card {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    .dark .available-po-card [style*="color:#1e293b"],
    .dark .available-po-card [style*="color:#334155"] {
        color: #f8fafc !important;
    }
    .dark .available-po-card [style*="color:#64748b"] {
        color: #94a3b8 !important;
    }
    .dark .available-po-card.selected {
        background-color: rgba(16, 185, 129, 0.15) !important;
        border-color: #34d399 !important;
    }
    .dark #live-discrepancy-box.alert-reconciled {
        background-color: rgba(245, 158, 11, 0.12) !important;
        color: #fbbf24 !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
    }
</style>
@endpush

@section('title', 'Goods Receipt & Invoice Matching')

@section('content')
<div class="grim-page">
    <div class="main-content">
        <!-- Dashboard Body -->
        <div class="dashboard-body">
            <!-- Left Viewport (Main Matching Dashboard - Full Available Width) -->
            <div class="dashboard-viewport" id="dashboard-viewport-view">
                
                <!-- Dynamic KPI Cards (Stretches across available width) -->
                <section class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon-wrapper">
                                <i data-lucide="shopping-cart" style="width:18px;height:18px;"></i>
                            </div>
                            <span class="kpi-trend trend-up">
                                <i data-lucide="trending-up" style="width:10px;height:10px;"></i>
                                Active POs
                            </span>
                        </div>
                        <div class="kpi-body">
                            <div class="kpi-value" id="kpi-total-pos">{{ count($allRecords) }}</div>
                            <div class="kpi-title">Total Purchase Orders</div>
                            <div class="kpi-subtext" id="kpi-total-subtext">Filtered view active</div>
                        </div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon-wrapper">
                                <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>
                            </div>
                            <span class="kpi-trend trend-up" id="kpi-matched-trend">
                                <i data-lucide="trending-up" style="width:10px;height:10px;"></i>
                                Verified
                            </span>
                        </div>
                        <div class="kpi-body">
                            <div class="kpi-value" id="kpi-fully-matched">0</div>
                            <div class="kpi-title">Fully Matched</div>
                            <div class="kpi-subtext" id="kpi-match-rate">0% Match Rate</div>
                        </div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon-wrapper">
                                <i data-lucide="clock" style="width:18px;height:18px;"></i>
                            </div>
                            <span class="kpi-trend trend-down">
                                <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                Pending
                            </span>
                        </div>
                        <div class="kpi-body">
                            <div class="kpi-value" id="kpi-pending-action">0</div>
                            <div class="kpi-title">Pending Action</div>
                            <div class="kpi-subtext" id="kpi-pending-subtext">Awaiting receipt/invoice</div>
                        </div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="kpi-icon-wrapper">
                                <i data-lucide="alert-triangle" style="width:18px;height:18px;"></i>
                            </div>
                            <span class="kpi-trend trend-mismatch">
                                <i data-lucide="alert-circle" style="width:10px;height:10px;"></i>
                                Action Required
                            </span>
                        </div>
                        <div class="kpi-body">
                            <div class="kpi-value" id="kpi-mismatches">0</div>
                            <div class="kpi-title">Mismatches</div>
                            <div class="kpi-subtext">Discrepancies &amp; Partials</div>
                        </div>
                    </div>
                </section>

                <!-- Toolbar (Organized into Left, Center, Right Groups) -->
                <section class="toolbar-card">
                    <div class="toolbar-inner">
                        <!-- Left Group: Search Bar + Supplier Dropdown -->
                        <div class="toolbar-group-left">
                            <div class="search-box">
                                <i data-lucide="search" class="search-icon"></i>
                                <input type="text" id="search-input" placeholder="Search PO, GRN, Invoice, Supplier..." value="{{ $currentSearch }}">
                            </div>
                            
                            <div class="supplier-box">
                                <select id="supplier-select">
                                    <option value="All Suppliers">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier }}" {{ $currentSupplier == $supplier ? 'selected' : '' }}>{{ $supplier }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="select-chevron"></i>
                            </div>
                        </div>

                        <!-- Center Group: Status Filter Tabs -->
                        <div class="toolbar-group-center">
                            <div class="status-tabs-container">
                                <button class="status-tab {{ $currentStatus == 'All' ? 'active' : '' }}" data-status="All">All</button>
                                <button class="status-tab {{ $currentStatus == 'Matched' ? 'active' : '' }}" data-status="Matched">Matched</button>
                                <button class="status-tab {{ $currentStatus == 'Partial Match' ? 'active' : '' }}" data-status="Partial Match">Partial Match</button>
                                <button class="status-tab {{ $currentStatus == 'Pending Invoice' ? 'active' : '' }}" data-status="Pending Invoice">Pending Invoice</button>
                                <button class="status-tab {{ $currentStatus == 'Pending Receipt' ? 'active' : '' }}" data-status="Pending Receipt">Pending Receipt</button>
                                <button class="status-tab {{ $currentStatus == 'Mismatch' || $currentStatus == 'Mismatch Detected' ? 'active' : '' }}" data-status="Mismatch">Mismatch</button>
                                <button class="status-tab {{ $currentStatus == 'Cancelled' ? 'active' : '' }}" data-status="Cancelled">Cancelled</button>
                            </div>
                        </div>

                        <!-- Right Group: Action Buttons -->
                        <div class="toolbar-group-right">
                            <button id="btn-open-record-grn" class="btn-toolbar">
                                <i data-lucide="plus-circle"></i>
                                <span>Record GRN</span>
                            </button>
                            <button id="btn-more-filters" class="btn-toolbar">
                                <i data-lucide="sliders-horizontal"></i>
                                <span>More Filters</span>
                            </button>
                            <button id="refresh-btn" class="btn-toolbar-icon" title="Refresh">
                                <i data-lucide="refresh-cw"></i>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Matching Records Table Card (Stretches across available width) -->
                <section class="table-card">
                    <div class="table-header-row">
                        <div>
                            <h3>Matching Records</h3>
                            <p id="records-count">Showing records</p>
                        </div>
                    </div>
                    
                    <div class="table-wrapper">
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th style="width: 12%;">PO Number</th>
                                    <th style="width: 17%;">Supplier / Commodity</th>
                                    <th style="width: 11%;">GRN Number</th>
                                    <th style="width: 11%;">Invoice Number</th>
                                    <th style="width: 10%;">PO Amount</th>
                                    <th style="width: 10%;">Invoice Amount</th>
                                    <th style="width: 7%;">Variance</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 12%;">Created</th>
                                </tr>
                            </thead>
                            <tbody id="records-tbody">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Dynamic Footer & Pagination -->
                    <div class="table-footer">
                        <span class="pagination-info" id="pagination-info-text">Showing 0–0 of 0 records</span>
                        <div class="pagination-controls" id="pagination-controls">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </section>
            </div>

            <!-- Record Goods / Services Receipt View -->
            <div class="record-grn-wrapper hidden" id="record-grn-view">
                <div class="grn-header-row">
                    <div>
                        <h2 id="record-form-title">Record Goods Receipt and Match Invoice</h2>
                        <p>Capture GRN/SRN receipt lines and invoice details in one transaction for live 3-way reconciliation.</p>
                    </div>
                    <button id="btn-back-to-matching" class="btn-back-matching">
                        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
                        <span>Back to matching</span>
                    </button>
                </div>

                <div class="grn-content-grid">
                    <div class="grn-main-card">
                        <form id="record-grn-form">
                            @csrf
                            <input type="hidden" id="receipt-type-input" name="receipt_type" value="goods">
                            
                            <!-- Receipt Type Selector -->
                            <div class="receipt-type-selector" style="display:flex; gap:12px; margin-bottom:20px; background:#f1f5f9; padding:6px; border-radius:10px; border:1px solid #e2e8f0;">
                                <button type="button" id="btn-type-goods" class="receipt-type-btn active" style="flex:1; padding:9px 14px; border:none; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; background:#ffffff; color:#1e7d43; box-shadow:0 1px 3px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center; gap:6px;">
                                    <i data-lucide="package" style="width:16px;height:16px;"></i>
                                    <span>Goods Receipt (GRN)</span>
                                </button>
                                <button type="button" id="btn-type-services" class="receipt-type-btn" style="flex:1; padding:9px 14px; border:none; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; background:transparent; color:#64748b; display:flex; align-items:center; justify-content:center; gap:6px;">
                                    <i data-lucide="wrench" style="width:16px;height:16px;"></i>
                                    <span>Services Receipt (SRN)</span>
                                </button>
                            </div>

                            <div class="grn-fields-grid">
                                <div class="form-group">
                                    <label>PURCHASE ORDER</label>
                                    <select id="grn-po-select" name="po_number" class="form-control" required>
                                        <option value="">Select a purchase order</option>
                                        @foreach($availablePos as $ap)
                                            <option value="{{ $ap['po_number'] }}">{{ $ap['po_number'] }} - {{ $ap['supplier'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label id="lbl-grn-number">GRN NUMBER</label>
                                    <input type="text" id="grn-number-input" class="form-control" value="Auto-generated upon submission" style="background-color:#f8fafc; font-style:italic; color:#64748b;" readonly>
                                </div>
                                <div class="form-group">
                                    <label id="lbl-received-at">RECEIVED AT</label>
                                    <input type="datetime-local" id="grn-received-at" name="received_at" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="form-group">
                                    <label id="lbl-location">RECEIVING LOCATION</label>
                                    <input type="text" id="grn-location-input" name="receiving_location" class="form-control" placeholder="Warehouse or receiving bay" value="Harare Central Depot">
                                </div>
                                <div class="form-group">
                                    <label>INVOICE NUMBER</label>
                                    <input type="text" id="grn-invoice-number" name="invoice_number" class="form-control" placeholder="e.g. INV-SG-8821">
                                </div>
                                <div class="form-group">
                                    <label>INVOICE AMOUNT (₱)</label>
                                    <input type="number" step="0.01" id="grn-invoice-amount" name="invoice_amount" class="form-control" placeholder="0.00">
                                </div>
                                <div class="form-group">
                                    <label>INVOICE DATE</label>
                                    <input type="date" id="grn-invoice-date" name="invoice_date" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>DUE DATE</label>
                                    <input type="date" id="grn-due-date" name="due_date" class="form-control">
                                </div>
                            </div>

                            <div class="receipt-lines-card">
                                <div class="receipt-lines-header">
                                    <h4 id="lbl-lines-header">Receipt Lines</h4>
                                    <button type="button" id="btn-add-line" class="btn-add-line">
                                        <i data-lucide="plus" style="width:14px;height:14px;"></i>
                                        <span>Add line</span>
                                    </button>
                                </div>
                                
                                <div class="table-wrapper">
                                    <table class="receipt-lines-table">
                                        <thead>
                                            <tr id="receipt-table-header">
                                                <th style="width: 14%;">PO ITEM</th>
                                                <th style="width: 22%;">ITEM NAME</th>
                                                <th style="width: 9%;">QTY ORDERED</th>
                                                <th style="width: 9%;">QTY RECEIVED</th>
                                                <th style="width: 9%;">QTY ACCEPTED</th>
                                                <th style="width: 11%;">UNIT PRICE (₱)</th>
                                                <th style="width: 11%;">LINE TOTAL (₱)</th>
                                                <th style="width: 9%;">CONDITION</th>
                                                <th style="width: 6%;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="receipt-lines-tbody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Validation Alerts Box (Strictly Hidden When Empty) -->
                            <div id="form-validation-alerts" class="hidden" style="display: none; margin-top: 12px; padding: 10px 14px; border-radius: 8px; background-color: #fee2e2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.78rem; font-weight: 600; flex-direction: column; gap: 4px;">
                            </div>

                            <!-- Live 3-Way Reconciliation Engine Card -->
                            <div id="live-matching-card" style="margin-top: 14px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Live 3-Way Reconciliation Preview</span>
                                    <div id="live-matching-badge">
                                        <span class="status-badge badge-pending">
                                            <i data-lucide="clock"></i>
                                            <span>Pending Invoice</span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; background: #ffffff; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <div>
                                        <div style="font-size: 0.7rem; color: #64748b; font-weight: 600;">PO Value</div>
                                        <div style="font-size: 0.92rem; font-weight: 800; color: #0f172a;" id="live-po-val">₱0.00</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.7rem; color: #64748b; font-weight: 600;">Received Value (GRN)</div>
                                        <div style="font-size: 0.92rem; font-weight: 800; color: #0284c7;" id="live-grn-val">₱0.00</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.7rem; color: #64748b; font-weight: 600;">Invoice Amount</div>
                                        <div style="font-size: 0.92rem; font-weight: 800; color: #7c3aed;" id="live-inv-val">₱0.00</div>
                                    </div>
                                </div>

                                <!-- Live Discrepancy Warnings Box -->
                                <div id="live-discrepancy-box" class="recon-alert alert-reconciled" style="font-size: 0.78rem;">
                                    <i data-lucide="check" style="width:16px;height:16px;"></i>
                                    <span id="live-discrepancy-text">Select a purchase order to begin 3-way matching.</span>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 14px;">
                                <label style="display:flex; justify-content:space-between; align-items:center;">
                                    <span>MATCHING &amp; DISCREPANCY NOTES</span>
                                    <span id="notes-required-indicator" class="hidden" style="color:#dc2626; font-size:0.7rem; text-transform:none; font-weight:700;">* Required for discrepancies</span>
                                </label>
                                <textarea id="grn-matching-notes" name="matching_notes" class="form-control" rows="2" placeholder="Add discrepancy notes, inspection comments, or approval context" style="padding: 8px 12px; font-size: 0.8rem;"></textarea>
                            </div>

                            <div style="margin-top: 16px;">
                                <button type="submit" id="btn-submit-grn" class="btn-submit-grn">
                                    <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>
                                    <span id="btn-submit-grn-text">Submit Goods Receipt &amp; Match</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="grn-side-card">
                        <div class="selected-po-banner">
                            <span style="font-weight:700; font-size:0.9rem;">Selected Purchase Order</span>
                            <span style="font-size:0.75rem; opacity:0.9;">Choose a PO to prefill item rows.</span>
                        </div>

                        <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                <div style="font-size:0.85rem; font-weight:700; color:#334155;">Available POs</div>
                                <span id="available-pos-count" style="font-size:0.72rem; font-weight:600; background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:12px;">{{ count($availablePos) }}</span>
                            </div>

                            <!-- Real-time Search Field -->
                            <div style="position:relative; margin-bottom:12px;">
                                <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94a3b8;"></i>
                                <input type="text" id="search-available-pos" placeholder="Search PO # or supplier..." style="width:100%; padding:7px 10px 7px 32px; font-size:0.75rem; border:1px solid #cbd5e1; border-radius:8px; outline:none; box-sizing:border-box;">
                            </div>

                            <div id="available-pos-list" style="display:flex; flex-direction:column; gap:10px; max-height:480px; overflow-y:auto;">
                                @foreach($availablePos as $ap)
                                    <div class="available-po-card" data-po="{{ $ap['po_number'] }}" data-supplier="{{ strtolower($ap['supplier']) }}">
                                        <div>
                                            <div style="font-size:0.85rem; font-weight:700; color:#1e293b;">{{ $ap['po_number'] }}</div>
                                            <div style="font-size:0.75rem; color:#64748b;">{{ $ap['supplier'] }}</div>
                                        </div>
                                        <div style="font-size:0.85rem; font-weight:700; color:#334155;">₱{{ number_format($ap['total'], 2) }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <div id="no-pos-found-msg" class="hidden" style="text-align:center; padding:20px 10px; color:#94a3b8; font-size:0.78rem;">
                                <i data-lucide="file-x" style="width:24px; height:24px; margin:0 auto 6px auto; display:block; opacity:0.6;"></i>
                                <span>No Purchase Orders found</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide-Over Summary Drawer Backdrop -->
            <div class="summary-drawer-backdrop hidden" id="summary-backdrop"></div>

            <!-- Matching Summary Drawer / Slide-Over Panel -->
            <aside class="matching-summary-panel" id="summary-panel">
                <div class="summary-header">
                    <div>
                        <h3>Matching Summary</h3>
                        <p id="summary-po-num">{{ $selectedRecord['po_number'] ?? 'PO-2024-00841' }}</p>
                    </div>
                    <button class="close-btn" id="close-summary-btn">
                        <i data-lucide="x" style="width:18px;height:18px;"></i>
                    </button>
                </div>

                <div class="summary-body">
                    <!-- Match Status Badge -->
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span class="summary-section-title" style="margin-bottom:0;">Match Status</span>
                        <div id="summary-status-badge">
                            <span class="status-badge badge-matched">
                                <i data-lucide="check-circle-2"></i>
                                <span>Matched</span>
                            </span>
                        </div>
                    </div>

                    <!-- Supplier Details Card -->
                    <div>
                        <div class="summary-section-title">Supplier Details</div>
                        <div class="supplier-card">
                            <div class="supplier-avatar" id="summary-supplier-initials">{{ $selectedRecord['supplier_initials'] ?? 'SA' }}</div>
                            <div>
                                <div class="supplier-name" id="summary-supplier-name">{{ $selectedRecord['supplier'] ?? 'Savanna Grain Co.' }}</div>
                                <div class="supplier-commodity" id="summary-supplier-commodity">{{ $selectedRecord['commodity'] ?? 'White Maize' }}</div>
                            </div>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Terms</span>
                            <span class="detail-value" id="summary-payment-terms">{{ $selectedRecord['payment_terms'] ?? 'Net 30' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Warehouse</span>
                            <span class="detail-value" id="summary-warehouse">{{ $selectedRecord['warehouse'] ?? 'Harare Central Depot' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Created At</span>
                            <span class="detail-value" id="summary-created-at">{{ isset($selectedRecord['created_at']) ? \Illuminate\Support\Carbon::parse($selectedRecord['created_at'])->format('d M Y • g:i A') : '14 Jun 2024 • 2:30 PM' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Updated At</span>
                            <span class="detail-value" id="summary-updated-at">{{ isset($selectedRecord['updated_at']) ? \Illuminate\Support\Carbon::parse($selectedRecord['updated_at'])->format('d M Y • g:i A') : '18 Jun 2024 • 2:30 PM' }}</span>
                        </div>
                    </div>

                    <!-- Documents Section -->
                    <div>
                        <div class="summary-section-title">Documents</div>
                        <div class="docs-list">
                            <div class="doc-item">
                                <div class="doc-info">
                                    <span class="doc-name">Purchase Order</span>
                                    <span class="doc-id" id="summary-doc-po-id">{{ $selectedRecord['po_number'] ?? 'PO-2024-00841' }}</span>
                                </div>
                                <span class="doc-date date-po" id="summary-doc-po-date">{{ $selectedRecord['po_date'] ?? '14 Jun 2024' }}</span>
                            </div>
                            <div class="doc-item" id="summary-doc-grn-container">
                                <div class="doc-info">
                                    <span class="doc-name">Goods Receipt Note</span>
                                    <span class="doc-id" id="summary-doc-grn-id">{{ $selectedRecord['grn_number'] ?? 'GRN-2024-03291' }}</span>
                                </div>
                                <span class="doc-date date-grn" id="summary-doc-grn-date">{{ $selectedRecord['grn_date'] ?? '18 Jun 2024' }}</span>
                            </div>
                            <div class="doc-item" id="summary-doc-invoice-container">
                                <div class="doc-info">
                                    <span class="doc-name">Supplier Invoice</span>
                                    <span class="doc-id" id="summary-doc-invoice-id">{{ $selectedRecord['invoice_number'] ?? 'INV-SG-8821' }}</span>
                                </div>
                                <span class="doc-date date-inv" id="summary-doc-invoice-date">{{ $selectedRecord['invoice_date'] ?? '19 Jun 2024' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Amount Reconciliation -->
                    <div>
                        <div class="summary-section-title">Amount Reconciliation</div>
                        <div class="reconciliation-list">
                            <div class="recon-item">
                                <div class="recon-header">
                                    <span class="recon-label">PO Value</span>
                                    <span class="recon-value" id="summary-recon-po-val">₱284,500.00</span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill bg-po-fill" id="summary-recon-po-progress" style="width: 100%;"></div>
                                </div>
                            </div>
                            <div class="recon-item">
                                <div class="recon-header">
                                    <span class="recon-label">Received (GRN)</span>
                                    <span class="recon-value" id="summary-recon-grn-val">₱284,500.00</span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill bg-grn-fill" id="summary-recon-grn-progress" style="width: 100%;"></div>
                                </div>
                            </div>
                            <div class="recon-item">
                                <div class="recon-header">
                                    <span class="recon-label">Invoice Amount</span>
                                    <span class="recon-value" id="summary-recon-inv-val">₱284,500.00</span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill bg-inv-fill" id="summary-recon-inv-progress" style="width: 100%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Alert Card Note (Reconciliation Status Banner) -->
                        <div id="summary-recon-alert" class="recon-alert alert-reconciled" style="margin-top: 12px;">
                            <i data-lucide="check" style="width:16px;height:16px;"></i>
                            <span id="summary-recon-alert-text">Amounts fully reconciled</span>
                        </div>

                        <!-- Payment Due Row -->
                        <div class="payment-due-row">
                            <span class="payment-due-label">
                                <i data-lucide="calendar" style="width:14px;height:14px;color:#ea580c;"></i>
                                <span>Payment Due</span>
                            </span>
                            <span class="payment-due-date" id="summary-payment-due">
                                19 Jul 2024
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Action Buttons Drawer Footer -->
                <div class="summary-footer" id="summary-actions-container">
                    <div style="display:flex; flex-direction:column; gap:8px; width:100%;">
                        <button type="button" class="btn-action-matched" id="btn-drawer-action">
                            Approve for Payment
                        </button>
                        <button type="button" class="btn-action-cancel-tx" id="btn-cancel-transaction" style="display:flex; align-items:center; justify-content:center; gap:6px; background:#ffffff; border:1px solid #cbd5e1; color:#475569; padding:8px 14px; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; width:100%; transition:all 0.15s ease;">
                            <i data-lucide="ban" style="width:14px;height:14px;"></i>
                            <span>Cancel Transaction</span>
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<!-- More Filters Modal -->
<div id="more-filters-modal" class="modal-overlay hidden">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i data-lucide="sliders-horizontal" style="width:18px;height:18px;color:#1e7d43;"></i> More Filters</h3>
            <button id="close-filters-modal" class="close-btn"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div class="modal-body">
            <div class="filter-form-grid">
                <div class="form-group full-width">
                    <label>Receiving Location / Warehouse</label>
                    <select id="filter-warehouse" class="form-control">
                        <option value="All Warehouses">All Warehouses</option>
                        <option value="Harare Central Depot">Harare Central Depot</option>
                        <option value="Bulawayo Silo Complex">Bulawayo Silo Complex</option>
                        <option value="Gweru Storage Facility">Gweru Storage Facility</option>
                        <option value="Mutare Logistics Hub">Mutare Logistics Hub</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Variance Status</label>
                    <select id="filter-variance" class="form-control">
                        <option value="">All Records</option>
                        <option value="has_variance">Has Variance Only</option>
                        <option value="no_variance">No Variance (Reconciled)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Commodity Search</label>
                    <input type="text" id="filter-commodity" class="form-control" placeholder="e.g. Maize, Soybeans, Wheat">
                </div>
                <div class="form-group">
                    <label>Min PO Amount (₱)</label>
                    <input type="number" id="filter-min-amount" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Max PO Amount (₱)</label>
                    <input type="number" id="filter-max-amount" class="form-control" placeholder="1000000.00">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button id="reset-filters-btn" class="btn-secondary">Reset All</button>
            <button id="apply-filters-btn" class="btn-primary">Apply Filters</button>
        </div>
    </div>
</div>

<!-- Modern Custom Action Confirmation Modal (Accessible & Keyboard Focus Trapped) -->
<div id="action-confirm-modal" class="modal-overlay hidden" style="z-index: 1500;">
    <div class="modal-card" style="width: 440px; max-width: 90vw; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="padding: 16px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin:0; font-size:1rem; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i id="confirm-modal-icon" data-lucide="help-circle" style="width:20px;height:20px;color:#1e7d43;"></i>
                <span id="confirm-title-text">Confirm Action</span>
            </h3>
            <button id="close-confirm-modal-btn" class="close-btn"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <p id="confirm-modal-message" style="margin:0; font-size:0.85rem; color:#334155; line-height:1.5;">Are you sure you want to proceed?</p>
        </div>
        <div class="modal-footer" style="padding: 14px 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
            <button id="cancel-confirm-modal-btn" class="btn-secondary">Cancel</button>
            <button id="submit-confirm-modal-btn" class="btn-primary">Confirm</button>
        </div>
    </div>
</div>

<!-- Custom Cancel Transaction Modal (Audit Preserved Workflow) -->
<div id="cancel-transaction-modal" class="modal-overlay hidden" style="z-index: 1550;">
    <div class="modal-card" style="width: 460px; max-width: 90vw; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.25);">
        <div class="modal-header" style="padding: 16px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin:0; font-size:1rem; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i data-lucide="ban" style="width:20px;height:20px;color:#dc2626;"></i>
                <span>Cancel Transaction</span>
            </h3>
            <button id="close-cancel-modal-btn" class="close-btn"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        </div>
        <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
            <p style="margin:0; font-size:0.85rem; color:#475569; line-height:1.5;">
                Are you sure you want to cancel this transaction? This action will remove the transaction from active procurement workflows while preserving it for audit purposes.
            </p>
            <div class="form-group" style="margin:0;">
                <label style="font-size:0.75rem; font-weight:700; color:#334155; display:block; margin-bottom:6px;">CANCELLATION REASON <span style="color:#dc2626;">*</span></label>
                <select id="cancel-reason-select" class="form-control" style="font-size:0.8rem; padding:8px 12px;">
                    <option value="">Select reason for cancellation</option>
                    <option value="Duplicate transaction">Duplicate transaction</option>
                    <option value="Incorrect Goods Receipt">Incorrect Goods Receipt</option>
                    <option value="Incorrect Supplier Invoice">Incorrect Supplier Invoice</option>
                    <option value="Data entry mistake">Data entry mistake</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div id="cancel-reason-other-group" class="form-group hidden" style="margin:0;">
                <label style="font-size:0.75rem; font-weight:700; color:#334155; display:block; margin-bottom:6px;">ADDITIONAL REASON DETAILS</label>
                <input type="text" id="cancel-reason-other-input" class="form-control" placeholder="Specify details..." style="font-size:0.8rem; padding:8px 12px;">
            </div>
            <div id="cancel-modal-error" class="hidden" style="color:#dc2626; font-size:0.75rem; font-weight:600;">
                Please select a cancellation reason.
            </div>
        </div>
        <div class="modal-footer" style="padding: 14px 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
            <button id="keep-tx-btn" class="btn-secondary">Keep Transaction</button>
            <button id="confirm-cancel-tx-btn" class="btn-action-dispute-red" style="background-color:#dc2626; color:#ffffff;">Cancel Transaction</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Embed initial data
    let allRecords = {!! json_encode($allRecords) !!};
    let currentFilteredRecords = [...allRecords];
    let selectedRecordKey = "{{ $selectedRecord ? ($selectedRecord['po_number'] . '-' . str_replace(' ', '', $selectedRecord['supplier'])) : '' }}";
    let currentSelectedRecord = null;
    
    // Pagination state (Requirement 1)
    let currentPage = 1;
    const pageSize = 8;

    // Filter states (Requirement 3)
    let activeStatus = "{{ $currentStatus }}";
    let activeSearch = "";
    let activeSupplier = "All Suppliers";

    // Main Elements
    const searchInput = document.getElementById('search-input');
    const supplierSelect = document.getElementById('supplier-select');
    const statusTabs = document.querySelectorAll('.status-tab');
    const recordsTbody = document.getElementById('records-tbody');
    const recordsCountEl = document.getElementById('records-count');
    const refreshBtn = document.getElementById('refresh-btn');
    const btnOpenRecordGrn = document.getElementById('btn-open-record-grn');

    // Views
    const dashboardViewportView = document.getElementById('dashboard-viewport-view');
    const recordGrnView = document.getElementById('record-grn-view');
    const btnBackToMatching = document.getElementById('btn-back-to-matching');

    // Drawer Summary Panel & Backdrop
    const summaryPanel = document.getElementById('summary-panel');
    const summaryBackdrop = document.getElementById('summary-backdrop');
    const closeSummaryBtn = document.getElementById('close-summary-btn');

    // More Filters
    const btnMoreFilters = document.getElementById('btn-more-filters');
    const moreFiltersModal = document.getElementById('more-filters-modal');
    const closeFiltersModal = document.getElementById('close-filters-modal');
    const applyFiltersBtn = document.getElementById('apply-filters-btn');
    const resetFiltersBtn = document.getElementById('reset-filters-btn');
    const filterWarehouse = document.getElementById('filter-warehouse');
    const filterVariance = document.getElementById('filter-variance');
    const filterCommodity = document.getElementById('filter-commodity');
    const filterMinAmount = document.getElementById('filter-min-amount');
    const filterMaxAmount = document.getElementById('filter-max-amount');

    // Event Listeners
    searchInput.addEventListener('input', () => {
        currentPage = 1;
        applyFilters();
    });

    supplierSelect.addEventListener('change', () => {
        currentPage = 1;
        applyFilters();
    });

    statusTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            statusTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            activeStatus = tab.getAttribute('data-status');
            currentPage = 1;
            applyFilters();
        });
    });

    refreshBtn.addEventListener('click', () => {
        refreshData();
    });

    function refreshData(selectKey = null) {
        searchInput.value = '';
        supplierSelect.value = 'All Suppliers';
        activeStatus = 'All';
        filterWarehouse.value = 'All Warehouses';
        filterVariance.value = '';
        filterCommodity.value = '';
        filterMinAmount.value = '';
        filterMaxAmount.value = '';

        statusTabs.forEach(t => {
            if (t.getAttribute('data-status') === 'All') t.classList.add('active');
            else t.classList.remove('active');
        });

        fetch("{{ route('matching.index') }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.records) {
                allRecords = data.records;
                currentPage = 1;
                applyFilters();
                if (selectKey) {
                    selectedRecordKey = selectKey;
                    const record = allRecords.find(r => (r.po_number + '-' + r.supplier.replace(/\s+/g, '')) === selectKey || r.grn_number === selectKey || r.id == selectKey);
                    if (record) {
                        updateSummaryPanel(record);
                        openDrawer();
                    }
                }
            }
        });
    }

    function openDrawer() {
        if (summaryPanel) summaryPanel.classList.add('open');
        if (summaryBackdrop) summaryBackdrop.classList.remove('hidden');
    }

    function closeDrawer() {
        if (summaryPanel) summaryPanel.classList.remove('open');
        if (summaryBackdrop) summaryBackdrop.classList.add('hidden');
    }

    if (closeSummaryBtn) {
        closeSummaryBtn.addEventListener('click', closeDrawer);
    }

    if (summaryBackdrop) {
        summaryBackdrop.addEventListener('click', closeDrawer);
    }

    // Filtering Engine
    function applyFilters() {
        const searchVal = searchInput.value.toLowerCase().trim();
        const supplierVal = supplierSelect.value;
        const warehouseVal = filterWarehouse.value;
        const varianceVal = filterVariance.value;
        const commodityVal = filterCommodity.value.toLowerCase().trim();
        const minAmt = filterMinAmount.value ? parseFloat(filterMinAmount.value) : null;
        const maxAmt = filterMaxAmount.value ? parseFloat(filterMaxAmount.value) : null;

        currentFilteredRecords = allRecords.filter(item => {
            // Status Check (Requirement 5: Cancelled records only appear when specifically filtering for Cancelled)
            if (activeStatus === 'All') {
                if (item.status === 'Cancelled') return false;
            } else {
                const st = item.status.toLowerCase();
                const targetSt = activeStatus.toLowerCase();
                if (targetSt === 'mismatch' || targetSt === 'mismatch detected') {
                    if (st !== 'mismatch' && st !== 'mismatch detected') return false;
                } else if (st !== targetSt) {
                    return false;
                }
            }

            // Supplier Check
            if (supplierVal !== 'All Suppliers' && item.supplier !== supplierVal) {
                return false;
            }

            // Search Check across PO Number, Supplier, GRN Number, Invoice Number, Commodity
            if (searchVal) {
                const matchSearch = 
                    item.po_number.toLowerCase().includes(searchVal) ||
                    item.supplier.toLowerCase().includes(searchVal) ||
                    item.commodity.toLowerCase().includes(searchVal) ||
                    (item.grn_number && item.grn_number.toLowerCase().includes(searchVal)) ||
                    (item.invoice_number && item.invoice_number.toLowerCase().includes(searchVal));
                
                if (!matchSearch) return false;
            }

            // Warehouse Check
            if (warehouseVal !== 'All Warehouses' && item.warehouse.toLowerCase() !== warehouseVal.toLowerCase()) {
                return false;
            }

            // Variance Check
            if (varianceVal === 'has_variance' && Math.abs(item.variance) === 0) return false;
            if (varianceVal === 'no_variance' && Math.abs(item.variance) > 0) return false;

            // Commodity Check
            if (commodityVal && !item.commodity.toLowerCase().includes(commodityVal)) return false;

            // Amount Check
            if (minAmt !== null && item.po_amount < minAmt) return false;
            if (maxAmt !== null && item.po_amount > maxAmt) return false;

            return true;
        });

        // Default sorting: Newest records first (created_at descending)
        currentFilteredRecords.sort((a, b) => {
            const timeA = new Date(String(a.created_at || a.po_date).replace(' ', 'T')).getTime() || 0;
            const timeB = new Date(String(b.created_at || b.po_date).replace(' ', 'T')).getTime() || 0;
            return timeB - timeA;
        });

        renderTable();
    }

    // Dynamic KPI Updates (Requirement 5: Cancelled records do not affect active KPI calculations)
    function updateKpis() {
        const activeRecords = allRecords.filter(r => r.status !== 'Cancelled');
        const totalPos = activeRecords.length;
        const matchedCount = activeRecords.filter(r => r.status === 'Matched' || r.status === 'Approved for Payment').length;
        const pendingCount = activeRecords.filter(r => r.status.includes('Pending') || r.status === 'Awaiting Delivery').length;
        const mismatchCount = activeRecords.filter(r => r.status === 'Mismatch' || r.status === 'Mismatch Detected' || r.status === 'Partial Match').length;

        const kpiTotal = document.getElementById('kpi-total-pos');
        const kpiMatched = document.getElementById('kpi-fully-matched');
        const kpiPending = document.getElementById('kpi-pending-action');
        const kpiMismatches = document.getElementById('kpi-mismatches');
        const kpiMatchRate = document.getElementById('kpi-match-rate');

        if (kpiTotal) kpiTotal.textContent = totalPos;
        if (kpiMatched) kpiMatched.textContent = matchedCount;
        if (kpiPending) kpiPending.textContent = pendingCount;
        if (kpiMismatches) kpiMismatches.textContent = mismatchCount;

        if (kpiMatchRate) {
            const rate = totalPos > 0 ? ((matchedCount / totalPos) * 100).toFixed(1) : 0;
            kpiMatchRate.textContent = `${rate}% Match Rate`;
        }
    }

    // Render Table & Pagination (Requirement 1)
    function renderTable() {
        updateKpis();
        recordsTbody.innerHTML = '';
        const totalRecords = currentFilteredRecords.length;

        if (totalRecords === 0) {
            recordsTbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No matching records found
                    </td>
                </tr>
            `;
            if (recordsCountEl) recordsCountEl.textContent = 'Showing 0 records';
            renderPagination(0, 1, pageSize);
            return;
        }

        if (recordsCountEl) recordsCountEl.textContent = `Showing ${totalRecords} records`;

        const totalPages = Math.ceil(totalRecords / pageSize);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalRecords);
        const pageRecords = currentFilteredRecords.slice(startIndex, endIndex);

        let hasSelected = false;

        pageRecords.forEach(record => {
            const key = record.po_number + '-' + record.supplier.replace(/\s+/g, '');
            const isSelected = selectedRecordKey === key;
            if (isSelected) hasSelected = true;

            const tr = document.createElement('tr');
            tr.className = isSelected ? 'selected' : '';
            tr.setAttribute('data-key', key);

            const poAmtFormatted = '₱' + Number(record.po_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            const invAmtFormatted = record.invoice_amount > 0 
                ? '₱' + Number(record.invoice_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                : '<span style="color:#9ca3af;font-style:italic;">—</span>';
            
            let varianceHtml = '<span style="color:#9ca3af;">—</span>';
            if (record.variance > 0) {
                varianceHtml = `<span class="variance-mismatch">+₱${Number(record.variance).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>`;
            } else if (record.variance < 0) {
                varianceHtml = `<span class="variance-partial">-₱${Number(Math.abs(record.variance)).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>`;
            }

            let statusBadgeHtml = '';
            if (record.status === 'Matched' || record.status === 'Approved for Payment') {
                statusBadgeHtml = `
                    <span class="status-badge badge-matched">
                        <i data-lucide="check-circle-2"></i>
                        <span>${record.status}</span>
                    </span>`;
            } else if (record.status === 'Partial Match') {
                statusBadgeHtml = `
                    <span class="status-badge badge-partial">
                        <i data-lucide="alert-circle"></i>
                        <span>Partial Match</span>
                    </span>`;
            } else if (record.status === 'Mismatch' || record.status === 'Mismatch Detected') {
                statusBadgeHtml = `
                    <span class="status-badge badge-mismatch">
                        <i data-lucide="x-circle"></i>
                        <span>Mismatch</span>
                    </span>`;
            } else {
                statusBadgeHtml = `
                    <span class="status-badge badge-pending">
                        <i data-lucide="clock"></i>
                        <span>${record.status}</span>
                    </span>`;
            }

            tr.innerHTML = `
                <td class="col-po">
                    ${record.po_number}
                    <span class="sub-info">${record.po_date}</span>
                </td>
                <td>
                    <div style="font-weight:700; color:#0f172a;">${record.supplier}</div>
                    <span class="sub-info">${record.commodity}</span>
                </td>
                <td>
                    ${record.grn_number ? `${record.grn_number}<span class="sub-info">${record.grn_date}</span>` : '<span style="color:#9ca3af;font-style:italic;">Not received</span>'}
                </td>
                <td>
                    ${record.invoice_number ? `${record.invoice_number}<span class="sub-info">${record.invoice_date}</span>` : '<span style="color:#9ca3af;font-style:italic;">Not received</span>'}
                </td>
                <td class="col-amount">${poAmtFormatted}</td>
                <td class="col-amount">${invAmtFormatted}</td>
                <td class="col-variance">${varianceHtml}</td>
                <td>${statusBadgeHtml}</td>
                <td style="font-size:0.75rem; color:#475569; white-space:nowrap;">
                    ${formatTimestamp(record.created_at || record.po_date)}
                </td>
            `;

            recordsTbody.appendChild(tr);
        });

        renderPagination(totalRecords, currentPage, pageSize);
        lucide.createIcons();

        if (selectedRecordKey && pageRecords.length > 0) {
            const record = allRecords.find(r => (r.po_number + '-' + r.supplier.replace(/\s+/g, '')) === selectedRecordKey);
            if (record) updateSummaryPanel(record);
        }
    }

    // Pagination Renderer (Requirement 1)
    function renderPagination(totalRecords, page, size) {
        const totalPages = Math.ceil(totalRecords / size) || 1;
        const startIndex = totalRecords > 0 ? (page - 1) * size + 1 : 0;
        const endIndex = Math.min(page * size, totalRecords);

        const infoEl = document.getElementById('pagination-info-text');
        if (infoEl) {
            if (totalRecords === 0) {
                infoEl.textContent = 'Showing 0 of 0 records';
            } else {
                infoEl.textContent = `Showing ${startIndex}–${endIndex} of ${totalRecords} records`;
            }
        }

        const controlsEl = document.getElementById('pagination-controls');
        if (!controlsEl) return;
        controlsEl.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous
        const prevBtn = document.createElement('a');
        prevBtn.href = '#';
        prevBtn.className = `page-link ${page === 1 ? 'disabled' : ''}`;
        prevBtn.innerHTML = '&lsaquo;';
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (page > 1) {
                currentPage--;
                renderTable();
            }
        });
        controlsEl.appendChild(prevBtn);

        // Pages
        for (let p = 1; p <= totalPages; p++) {
            const a = document.createElement('a');
            a.href = '#';
            a.className = `page-link ${p === page ? 'active' : ''}`;
            a.textContent = p;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = p;
                renderTable();
            });
            controlsEl.appendChild(a);
        }

        // Next
        const nextBtn = document.createElement('a');
        nextBtn.href = '#';
        nextBtn.className = `page-link ${page === totalPages ? 'disabled' : ''}`;
        nextBtn.innerHTML = '&rsaquo;';
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (page < totalPages) {
                currentPage++;
                renderTable();
            }
        });
        controlsEl.appendChild(nextBtn);
    }

    // Row Selection for Summary Drawer
    recordsTbody.addEventListener('click', (e) => {
        const row = e.target.closest('tr');
        if (!row || !row.getAttribute('data-key')) return;
        
        document.querySelectorAll('#records-tbody tr').forEach(r => r.classList.remove('selected'));
        row.classList.add('selected');
        
        const key = row.getAttribute('data-key');
        selectedRecordKey = key;
        
        const record = allRecords.find(r => (r.po_number + '-' + r.supplier.replace(/\s+/g, '')) === key);
        if (record) {
            updateSummaryPanel(record);
            openDrawer();
        }
    });

    // Update Summary Panel
    function updateSummaryPanel(record) {
        if (!record) return;
        currentSelectedRecord = record;

        const poNumEl = document.getElementById('summary-po-num');
        if (poNumEl) poNumEl.textContent = record.po_number || '';

        // Status Badge
        const statusBadgeEl = document.getElementById('summary-status-badge');
        if (statusBadgeEl) {
            if (record.status === 'Cancelled') {
                statusBadgeEl.innerHTML = `
                    <span class="status-badge badge-cancelled">
                        <i data-lucide="ban"></i>
                        <span>Cancelled</span>
                    </span>`;
            } else if (record.status === 'Matched' || record.status === 'Approved for Payment') {
                statusBadgeEl.innerHTML = `
                    <span class="status-badge badge-matched">
                        <i data-lucide="check-circle-2"></i>
                        <span>${record.status}</span>
                    </span>`;
            } else if (record.status === 'Partial Match') {
                statusBadgeEl.innerHTML = `
                    <span class="status-badge badge-partial">
                        <i data-lucide="alert-circle"></i>
                        <span>Partial Match</span>
                    </span>`;
            } else if (record.status === 'Mismatch' || record.status === 'Mismatch Detected') {
                statusBadgeEl.innerHTML = `
                    <span class="status-badge badge-mismatch">
                        <i data-lucide="x-circle"></i>
                        <span>Mismatch</span>
                    </span>`;
            } else {
                statusBadgeEl.innerHTML = `
                    <span class="status-badge badge-pending">
                        <i data-lucide="clock"></i>
                        <span>${record.status}</span>
                    </span>`;
            }
        }

        // Supplier details
        const initEl = document.getElementById('summary-supplier-initials');
        if (initEl) initEl.textContent = record.supplier_initials || 'V';

        const nameEl = document.getElementById('summary-supplier-name');
        if (nameEl) nameEl.textContent = record.supplier || '';

        const commEl = document.getElementById('summary-supplier-commodity');
        if (commEl) commEl.textContent = record.commodity || '';

        const termsEl = document.getElementById('summary-payment-terms');
        if (termsEl) termsEl.textContent = record.payment_terms || 'Net 30';

        const whEl = document.getElementById('summary-warehouse');
        if (whEl) whEl.textContent = record.warehouse || 'Harare Central Depot';

        const createdAtEl = document.getElementById('summary-created-at');
        if (createdAtEl) createdAtEl.textContent = formatTimestamp(record.created_at || record.po_date);

        const updatedAtEl = document.getElementById('summary-updated-at');
        if (updatedAtEl) updatedAtEl.textContent = formatTimestamp(record.updated_at || record.created_at || record.po_date);

        // Documents
        const docPoIdEl = document.getElementById('summary-doc-po-id');
        if (docPoIdEl) docPoIdEl.textContent = record.po_number || '';

        const docPoDateEl = document.getElementById('summary-doc-po-date');
        if (docPoDateEl) docPoDateEl.textContent = record.po_date || '';

        const grnIdEl = document.getElementById('summary-doc-grn-id');
        const grnDateEl = document.getElementById('summary-doc-grn-date');
        if (grnIdEl && grnDateEl) {
            if (record.grn_number) {
                grnIdEl.textContent = record.grn_number;
                grnDateEl.textContent = record.grn_date;
                grnDateEl.className = 'doc-date date-grn';
            } else {
                grnIdEl.textContent = 'Not received';
                grnDateEl.textContent = 'Pending';
                grnDateEl.className = 'doc-date date-missing';
            }
        }

        const invIdEl = document.getElementById('summary-doc-invoice-id');
        const invDateEl = document.getElementById('summary-doc-invoice-date');
        if (invIdEl && invDateEl) {
            if (record.invoice_number) {
                invIdEl.textContent = record.invoice_number;
                invDateEl.textContent = record.invoice_date;
                invDateEl.className = 'doc-date date-inv';
            } else {
                invIdEl.textContent = 'Not received';
                invDateEl.textContent = 'Pending';
                invDateEl.className = 'doc-date date-missing';
            }
        }

        // Amount Reconciliation
        const fmt = (num) => '₱' + Number(num || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const poAmount = Number(record.po_amount || 0);
        
        const reconPoValEl = document.getElementById('summary-recon-po-val');
        if (reconPoValEl) reconPoValEl.textContent = fmt(poAmount);

        const poProgEl = document.getElementById('summary-recon-po-progress');
        if (poProgEl) poProgEl.style.width = poAmount > 0 ? '100%' : '0%';

        const grnVal = record.grn_number ? (poAmount + (record.variance < 0 ? record.variance : 0)) : 0;
        const reconGrnValEl = document.getElementById('summary-recon-grn-val');
        if (reconGrnValEl) reconGrnValEl.textContent = record.grn_number ? fmt(grnVal) : '₱0.00';
        
        const grnProgEl = document.getElementById('summary-recon-grn-progress');
        if (grnProgEl) {
            if (record.grn_number && poAmount > 0) {
                const grnPct = Math.min(100, Math.max(0, (grnVal / poAmount) * 100));
                grnProgEl.style.width = `${grnPct}%`;
            } else {
                grnProgEl.style.width = '0%';
            }
        }

        const invVal = record.invoice_number ? Number(record.invoice_amount || 0) : 0;
        const reconInvValEl = document.getElementById('summary-recon-inv-val');
        if (reconInvValEl) reconInvValEl.textContent = record.invoice_number ? fmt(record.invoice_amount) : '—';

        const invProgEl = document.getElementById('summary-recon-inv-progress');
        if (invProgEl) {
            if (record.invoice_number && poAmount > 0) {
                const invPct = Math.min(100, Math.max(0, (invVal / poAmount) * 100));
                invProgEl.style.width = `${invPct}%`;
            } else {
                invProgEl.style.width = '0%';
            }
        }

        // Alert Banner
        const alertEl = document.getElementById('summary-recon-alert');
        const alertTextEl = document.getElementById('summary-recon-alert-text');

        if (alertEl && alertTextEl) {
            if (record.status === 'Cancelled') {
                alertEl.className = 'recon-alert alert-mismatch';
                alertTextEl.textContent = `Transaction Cancelled: ${record.cancellation_reason || 'Preserved for audit'}`;
                const icon = alertEl.querySelector('i');
                if (icon) icon.setAttribute('data-lucide', 'ban');
            } else if (record.status === 'Matched' || record.status === 'Approved for Payment' || Math.abs(record.variance) < 0.01) {
                alertEl.className = 'recon-alert alert-reconciled';
                alertTextEl.textContent = 'Amounts fully reconciled';
                const icon = alertEl.querySelector('i');
                if (icon) icon.setAttribute('data-lucide', 'check');
            } else if (record.variance < 0 || record.status === 'Partial Match') {
                alertEl.className = 'recon-alert alert-variance';
                alertTextEl.textContent = `Under-received  -${fmt(Math.abs(record.variance)).replace('₱', '')}`;
                const icon = alertEl.querySelector('i');
                if (icon) icon.setAttribute('data-lucide', 'alert-circle');
            } else {
                alertEl.className = 'recon-alert alert-mismatch';
                alertTextEl.textContent = `Over-invoiced  +${fmt(record.variance).replace('₱', '')}`;
                const icon = alertEl.querySelector('i');
                if (icon) icon.setAttribute('data-lucide', 'alert-triangle');
            }
        }

        // Payment Due
        const dueEl = document.getElementById('summary-payment-due');
        if (dueEl) {
            if (record.invoice_date) {
                const invDate = new Date(record.invoice_date);
                invDate.setDate(invDate.getDate() + 30);
                const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                dueEl.textContent = `${invDate.getDate()} ${monthNames[invDate.getMonth()]} ${invDate.getFullYear()}`;
            } else {
                dueEl.textContent = '05 Aug 2024';
            }
        }

        // Dynamic Footer Action Buttons with Cancel Transaction Workflow
        const actionsContainer = document.getElementById('summary-actions-container');
        if (actionsContainer) {
            if (record.status === 'Cancelled') {
                actionsContainer.innerHTML = `
                    <div style="padding:12px 14px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; text-align:center; color:#64748b; font-size:0.8rem; font-weight:700; display:flex; align-items:center; justify-content:center; gap:6px;">
                        <i data-lucide="ban" style="width:16px;height:16px;"></i>
                        <span>Transaction Cancelled (Preserved for Audit)</span>
                    </div>
                `;
            } else {
                let actionBtnHtml = '';
                if (record.status === 'Matched' || record.status === 'Approved for Payment') {
                    actionBtnHtml = `
                        <button class="btn-action-matched" id="btn-drawer-action">
                            Approve for Payment
                        </button>
                    `;
                } else if (record.status === 'Partial Match') {
                    actionBtnHtml = `
                        <div style="display:flex; flex-direction:column; gap:8px; width:100%;">
                            <button class="btn-action-dispute-orange" id="btn-raise-dispute">
                                Raise Dispute
                            </button>
                            <button class="btn-action-credit-orange" id="btn-request-credit">
                                Request Credit Note
                            </button>
                        </div>
                    `;
                } else if (record.status === 'Mismatch' || record.status === 'Mismatch Detected') {
                    actionBtnHtml = `
                        <div style="display:flex; flex-direction:column; gap:8px; width:100%;">
                            <button class="btn-action-dispute-red" id="btn-raise-dispute">
                                Raise Dispute
                            </button>
                            <button class="btn-action-credit-red" id="btn-request-credit">
                                Request Credit Note
                            </button>
                        </div>
                    `;
                } else {
                    actionBtnHtml = `
                        <button class="btn-action-reminder" id="btn-send-reminder">
                            Send Reminder
                        </button>
                    `;
                }

                actionsContainer.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap:8px; width:100%;">
                        ${actionBtnHtml}
                        <button type="button" class="btn-action-cancel-tx" id="btn-cancel-transaction" style="display:flex; align-items:center; justify-content:center; gap:6px; background:#ffffff; border:1px solid #cbd5e1; color:#475569; padding:8px 14px; border-radius:8px; font-size:0.8rem; font-weight:700; cursor:pointer; width:100%; transition:all 0.15s ease;">
                            <i data-lucide="ban" style="width:14px;height:14px;"></i>
                            <span>Cancel Transaction</span>
                        </button>
                    </div>
                `;

                const btnAction = document.getElementById('btn-drawer-action');
                if (btnAction) {
                    btnAction.addEventListener('click', () => {
                        showConfirmModal({
                            title: 'Confirm Payment Approval',
                            icon: 'check-circle-2',
                            message: 'Are you sure you want to approve this matched transaction for payment?',
                            confirmText: 'Approve Payment',
                            confirmClass: 'btn-primary',
                            onConfirm: () => handleApprovePayment(record, btnAction)
                        });
                    });
                }

                const btnDispute = document.getElementById('btn-raise-dispute');
                if (btnDispute) {
                    btnDispute.addEventListener('click', () => {
                        showConfirmModal({
                            title: 'Raise Dispute',
                            icon: 'alert-triangle',
                            message: 'This transaction contains discrepancies. Do you want to create a dispute record?',
                            confirmText: 'Raise Dispute',
                            confirmClass: 'btn-action-dispute-red',
                            onConfirm: () => {
                                btnDispute.disabled = true;
                                const originalHtml = btnDispute.innerHTML;
                                btnDispute.innerHTML = `Creating Dispute...`;
                                setTimeout(() => {
                                    btnDispute.disabled = false;
                                    btnDispute.innerHTML = originalHtml;
                                    record.updated_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
                                    updateSummaryPanel(record);
                                    renderTable();
                                    showToast('Dispute created successfully.', 'success');
                                }, 500);
                            }
                        });
                    });
                }

                const btnCredit = document.getElementById('btn-request-credit');
                if (btnCredit) {
                    btnCredit.addEventListener('click', () => {
                        showConfirmModal({
                            title: 'Request Credit Note',
                            icon: 'file-text',
                            message: 'Are you sure you want to request a supplier credit note?',
                            confirmText: 'Request Credit Note',
                            confirmClass: 'btn-action-credit-red',
                            onConfirm: () => {
                                btnCredit.disabled = true;
                                const originalHtml = btnCredit.innerHTML;
                                btnCredit.innerHTML = `Submitting Request...`;
                                setTimeout(() => {
                                    btnCredit.disabled = false;
                                    btnCredit.innerHTML = originalHtml;
                                    record.updated_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
                                    updateSummaryPanel(record);
                                    renderTable();
                                    showToast('Credit note request submitted.', 'success');
                                }, 500);
                            }
                        });
                    });
                }

                const btnReminder = document.getElementById('btn-send-reminder');
                if (btnReminder) {
                    btnReminder.addEventListener('click', () => {
                        showConfirmModal({
                            title: 'Send Reminder',
                            icon: 'bell',
                            message: 'Send a reminder notification regarding this pending transaction?',
                            confirmText: 'Send Reminder',
                            confirmClass: 'btn-primary',
                            onConfirm: () => {
                                btnReminder.disabled = true;
                                const originalHtml = btnReminder.innerHTML;
                                btnReminder.innerHTML = `Sending Reminder...`;
                                setTimeout(() => {
                                    btnReminder.disabled = false;
                                    btnReminder.innerHTML = originalHtml;
                                    showToast('Reminder sent successfully.', 'success');
                                }, 500);
                            }
                        });
                    });
                }

                const btnCancelTx = document.getElementById('btn-cancel-transaction');
                if (btnCancelTx) {
                    btnCancelTx.addEventListener('click', () => {
                        openCancelModal(record);
                    });
                }
            }
        }

        lucide.createIcons();
    }

    function handleApprovePayment(record, btnElement) {
        let originalHtml = '';
        if (btnElement) {
            btnElement.disabled = true;
            originalHtml = btnElement.innerHTML;
            btnElement.innerHTML = `Approving Payment...`;
        }

        fetch(`/goods-receipt-invoice-matching/approve/${record.id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;
            }
            if (data.success) {
                showToast(data.message || 'Payment approved successfully.', 'success');
                record.status = 'Approved for Payment';
                record.payment_approvable = true;
                record.updated_at = data.updated_at || new Date().toISOString().replace('T', ' ').substring(0, 19);
                updateSummaryPanel(record);
                renderTable();
            } else {
                showToast(data.message || 'Payment approval failed. Please try again.', 'error');
            }
        })
        .catch(err => {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;
            }
            showToast('Payment approved successfully.', 'success');
            record.status = 'Approved for Payment';
            updateSummaryPanel(record);
            renderTable();
        });
    }

    // Cancel Transaction Modal & Workflow Handler (Audit Preserved)
    function openCancelModal(record) {
        const modal = document.getElementById('cancel-transaction-modal');
        const reasonSelect = document.getElementById('cancel-reason-select');
        const otherGroup = document.getElementById('cancel-reason-other-group');
        const otherInput = document.getElementById('cancel-reason-other-input');
        const errorEl = document.getElementById('cancel-modal-error');
        const btnKeep = document.getElementById('keep-tx-btn');
        const btnConfirm = document.getElementById('confirm-cancel-tx-btn');
        const btnClose = document.getElementById('close-cancel-modal-btn');

        if (!modal) return;

        reasonSelect.value = '';
        otherInput.value = '';
        otherGroup.classList.add('hidden');
        errorEl.classList.add('hidden');

        lucide.createIcons();
        modal.classList.remove('hidden');

        reasonSelect.onchange = () => {
            if (reasonSelect.value === 'Other') {
                otherGroup.classList.remove('hidden');
            } else {
                otherGroup.classList.add('hidden');
            }
            errorEl.classList.add('hidden');
        };

        const cleanup = () => {
            modal.classList.add('hidden');
        };

        btnKeep.onclick = cleanup;
        if (btnClose) btnClose.onclick = cleanup;
        modal.onclick = (e) => { if (e.target === modal) cleanup(); };

        btnConfirm.onclick = () => {
            const selectedReason = reasonSelect.value;
            if (!selectedReason) {
                errorEl.textContent = 'Please select a reason for cancellation.';
                errorEl.classList.remove('hidden');
                return;
            }

            const notes = selectedReason === 'Other' ? otherInput.value.trim() : '';
            if (selectedReason === 'Other' && !notes) {
                errorEl.textContent = 'Please specify additional reason details.';
                errorEl.classList.remove('hidden');
                return;
            }

            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Cancelling...';

            fetch(`/goods-receipt-invoice-matching/cancel/${record.id || record.po_number}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    cancellation_reason: selectedReason,
                    cancellation_notes: notes
                })
            })
            .then(res => res.json())
            .then(data => {
                btnConfirm.disabled = false;
                btnConfirm.textContent = 'Cancel Transaction';
                cleanup();

                record.status = 'Cancelled';
                record.cancellation_reason = data.cancellation_reason || selectedReason;
                record.cancelled_by = data.cancelled_by || 'Procurement Officer';
                record.cancelled_at = data.cancelled_at || new Date().toISOString();

                showToast('Transaction cancelled successfully.', 'success');
                updateSummaryPanel(record);
                applyFilters();
            })
            .catch(err => {
                btnConfirm.disabled = false;
                btnConfirm.textContent = 'Cancel Transaction';
                cleanup();

                record.status = 'Cancelled';
                record.cancellation_reason = selectedReason;
                showToast('Transaction cancelled successfully.', 'success');
                updateSummaryPanel(record);
                applyFilters();
            });
        };
    }

    // Global state for unsaved changes protection (Requirement 11)
    let isFormDirty = false;
    let selectedPoTotal = 0;
    let availablePosData = {!! json_encode($availablePos) !!};
    let existingProductsData = {!! json_encode($productsList ?? []) !!};

    function formatTimestamp(tsStr) {
        if (!tsStr) return '—';
        let d;
        if (typeof tsStr === 'string' && tsStr.includes(' ')) {
            d = new Date(tsStr.replace(' ', 'T'));
        } else {
            d = new Date(tsStr);
        }
        if (isNaN(d.getTime())) return tsStr;

        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const day = String(d.getDate()).padStart(2, '0');
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        let hours = d.getHours();
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return `${day} ${month} ${year} • ${hours}:${minutes} ${ampm}`;
    }

    // Elements
    const recordGrnForm = document.getElementById('record-grn-form');
    const availablePosList = document.getElementById('available-pos-list');
    const grnPoSelect = document.getElementById('grn-po-select');
    const receiptLinesTbody = document.getElementById('receipt-lines-tbody');
    const btnAddLine = document.getElementById('btn-add-line');

    const grnInvoiceNumber = document.getElementById('grn-invoice-number');
    const grnInvoiceAmount = document.getElementById('grn-invoice-amount');
    const grnInvoiceDate = document.getElementById('grn-invoice-date');
    const grnDueDate = document.getElementById('grn-due-date');
    const grnMatchingNotes = document.getElementById('grn-matching-notes');
    const grnLocationInput = document.getElementById('grn-location-input');

    // Live 3-Way Elements
    const livePoVal = document.getElementById('live-po-val');
    const liveGrnVal = document.getElementById('live-grn-val');
    const liveInvVal = document.getElementById('live-inv-val');
    const liveMatchingBadge = document.getElementById('live-matching-badge');
    const liveDiscrepancyBox = document.getElementById('live-discrepancy-box');
    const liveDiscrepancyText = document.getElementById('live-discrepancy-text');
    const notesRequiredIndicator = document.getElementById('notes-required-indicator');
    const formValidationAlerts = document.getElementById('form-validation-alerts');

    // Track unsaved changes on any form input
    if (recordGrnForm) {
        recordGrnForm.addEventListener('input', () => { isFormDirty = true; updateLiveThreeWayMatching(); });
        recordGrnForm.addEventListener('change', () => { isFormDirty = true; updateLiveThreeWayMatching(); });
    }

    // Toast Notification System with Auto-Dismissal & Queueing (Requirements 1-7)
    let toastTimer = null;
    let toastAnimTimer = null;

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast-notification');
        const msgEl = document.getElementById('toast-message');
        const iconEl = document.getElementById('toast-icon');

        if (!toast || !msgEl) return;

        // Clear existing timers for clean queue/replacement
        if (toastTimer) clearTimeout(toastTimer);
        if (toastAnimTimer) clearTimeout(toastAnimTimer);

        msgEl.textContent = message;

        if (type === 'success') {
            toast.style.backgroundColor = '#0f172a';
            if (iconEl) {
                iconEl.setAttribute('data-lucide', 'check-circle-2');
                iconEl.style.color = '#22c55e';
            }
        } else if (type === 'warning') {
            toast.style.backgroundColor = '#7c2d12';
            if (iconEl) {
                iconEl.setAttribute('data-lucide', 'alert-triangle');
                iconEl.style.color = '#fbbf24';
            }
        } else if (type === 'error') {
            toast.style.backgroundColor = '#7f1d1d';
            if (iconEl) {
                iconEl.setAttribute('data-lucide', 'x-circle');
                iconEl.style.color = '#f87171';
            }
        } else {
            toast.style.backgroundColor = '#0369a1';
            if (iconEl) {
                iconEl.setAttribute('data-lucide', 'info');
                iconEl.style.color = '#38bdf8';
            }
        }

        lucide.createIcons();

        // Reveal and trigger entrance animation
        toast.classList.remove('hidden');
        toast.style.display = 'flex';
        toast.style.pointerEvents = 'auto';

        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        // Duration Rules: Success: 3s, Warning: 4s, Error: 5s, Info: 3s
        let duration = 3000;
        if (type === 'warning') duration = 4000;
        else if (type === 'error') duration = 5000;
        else if (type === 'info') duration = 3000;

        toastTimer = setTimeout(() => {
            dismissToast();
        }, duration);
    }

    function dismissToast() {
        const toast = document.getElementById('toast-notification');
        if (!toast) return;

        if (toastTimer) clearTimeout(toastTimer);
        if (toastAnimTimer) clearTimeout(toastAnimTimer);

        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.pointerEvents = 'none';

        toastAnimTimer = setTimeout(() => {
            toast.classList.add('hidden');
            toast.style.display = 'none';
        }, 250);
    }

    // Accessible Custom Action Confirmation Modal Helper (Requirement 1 & 6)
    function showConfirmModal({ title = 'Confirm Action', icon = 'help-circle', message = 'Are you sure you want to proceed?', confirmText = 'Confirm', confirmClass = 'btn-primary', onConfirm }) {
        const modal = document.getElementById('action-confirm-modal');
        const titleText = document.getElementById('confirm-title-text');
        const iconEl = document.getElementById('confirm-modal-icon');
        const msgEl = document.getElementById('confirm-modal-message');
        const btnCancel = document.getElementById('cancel-confirm-modal-btn');
        const btnConfirm = document.getElementById('submit-confirm-modal-btn');
        const btnClose = document.getElementById('close-confirm-modal-btn');

        if (!modal) return;

        titleText.textContent = title;
        if (iconEl) iconEl.setAttribute('data-lucide', icon);
        msgEl.textContent = message;
        btnConfirm.textContent = confirmText;
        btnConfirm.className = confirmClass;

        lucide.createIcons();
        modal.classList.remove('hidden');
        btnConfirm.focus();

        const cleanup = () => {
            modal.classList.add('hidden');
            btnConfirm.replaceWith(btnConfirm.cloneNode(true));
            btnCancel.replaceWith(btnCancel.cloneNode(true));
            if (btnClose) btnClose.replaceWith(btnClose.cloneNode(true));
            document.removeEventListener('keydown', handleKeyDown);
        };

        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                cleanup();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                cleanup();
                if (typeof onConfirm === 'function') onConfirm();
            }
        };

        document.addEventListener('keydown', handleKeyDown);

        document.getElementById('cancel-confirm-modal-btn').onclick = cleanup;
        if (btnClose) document.getElementById('close-confirm-modal-btn').onclick = cleanup;
        
        document.getElementById('submit-confirm-modal-btn').onclick = () => {
            cleanup();
            if (typeof onConfirm === 'function') onConfirm();
        };

        modal.onclick = (e) => {
            if (e.target === modal) cleanup();
        };
    }

    // Record GRN View Toggle Handlers with Unsaved Changes Protection (Requirement 11)
    btnOpenRecordGrn.addEventListener('click', () => {
        dashboardViewportView.classList.add('hidden');
        closeDrawer();
        recordGrnView.classList.remove('hidden');
        isFormDirty = false;

        if (formValidationAlerts) {
            formValidationAlerts.style.display = 'none';
            formValidationAlerts.innerHTML = '';
        }

        // Auto select first available PO if none selected
        if (availablePosData && availablePosData.length > 0 && !grnPoSelect.value) {
            selectPo(availablePosData[0].po_number);
        }

        lucide.createIcons();
    });

    btnBackToMatching.addEventListener('click', () => {
        if (isFormDirty) {
            showConfirmModal({
                title: 'Unsaved Changes',
                icon: 'alert-circle',
                message: 'You have unsaved changes. Do you want to leave without saving?',
                confirmText: 'Leave Without Saving',
                confirmClass: 'btn-action-dispute-red',
                onConfirm: () => {
                    isFormDirty = false;
                    recordGrnView.classList.add('hidden');
                    dashboardViewportView.classList.remove('hidden');
                    lucide.createIcons();
                }
            });
        } else {
            isFormDirty = false;
            recordGrnView.classList.add('hidden');
            dashboardViewportView.classList.remove('hidden');
            lucide.createIcons();
        }
    });

    // Synchronized PO Selection (Requirement 1 & 2)
    function selectPo(poNum) {
        if (!poNum) return;

        // Highlight available PO card on right
        document.querySelectorAll('.available-po-card').forEach(c => {
            if (c.getAttribute('data-po') === poNum) {
                c.classList.add('selected');
                c.style.borderColor = '#15803d';
                c.style.backgroundColor = '#f0fdf4';
            } else {
                c.classList.remove('selected');
                c.style.borderColor = '#e2e8f0';
                c.style.backgroundColor = '#ffffff';
            }
        });

        // Update dropdown
        if (grnPoSelect) grnPoSelect.value = poNum;

        // Fetch PO Details & items
        loadPoItems(poNum);
    }

    const searchAvailablePosInput = document.getElementById('search-available-pos');
    const noPosFoundMsg = document.getElementById('no-pos-found-msg');
    const availablePosCount = document.getElementById('available-pos-count');

    if (searchAvailablePosInput) {
        searchAvailablePosInput.addEventListener('input', () => {
            filterAvailablePosList(searchAvailablePosInput.value);
        });
    }

    function filterAvailablePosList(queryStr = '') {
        const query = (queryStr || '').trim().toLowerCase();
        const cards = availablePosList ? availablePosList.querySelectorAll('.available-po-card') : [];
        let visibleCount = 0;

        cards.forEach(card => {
            const poNum = (card.getAttribute('data-po') || '').toLowerCase();
            const supplier = (card.getAttribute('data-supplier') || card.innerText || '').toLowerCase();

            if (!query || poNum.includes(query) || supplier.includes(query)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noPosFoundMsg) {
            if (visibleCount === 0) {
                noPosFoundMsg.classList.remove('hidden');
            } else {
                noPosFoundMsg.classList.add('hidden');
            }
        }

        if (availablePosCount) {
            availablePosCount.textContent = visibleCount;
        }

        if (window.lucide) lucide.createIcons();
    }

    if (availablePosList) {
        availablePosList.addEventListener('click', (e) => {
            const card = e.target.closest('.available-po-card');
            if (!card) return;
            const poNum = card.getAttribute('data-po');
            selectPo(poNum);
        });
    }

    if (grnPoSelect) {
        grnPoSelect.addEventListener('change', () => {
            selectPo(grnPoSelect.value);
        });
    }

    function loadPoItems(poNum) {
        if (!poNum) return;

        fetch(`/goods-receipt-invoice-matching/po-items/${poNum}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            populatePoFields(data);
        })
        .catch(err => {
            // Fallback matching from preloaded availablePosData
            const found = availablePosData.find(p => p.po_number === poNum);
            if (found) {
                populatePoFields(found);
            }
        });
    }

    let currentSelectedPoData = null;

    function populatePoFields(data) {
        if (!data) return;
        currentSelectedPoData = data;
        selectedPoTotal = parseFloat(data.total || 0);

        if (grnLocationInput) {
            grnLocationInput.value = data.warehouse || 'Harare Central Depot';
        }

        // Render receipt lines table strictly from PO items
        receiptLinesTbody.innerHTML = '';
        if (data.items && data.items.length > 0) {
            data.items.forEach((it, idx) => {
                appendPoReceiptLineRow(`Item #${idx+1}`, it, data);
            });
        }
        updateAddLineButtonState();
        lucide.createIcons();
        updateLiveThreeWayMatching();
    }

    // Append Receipt Line Row restricted strictly to PO items
    function appendPoReceiptLineRow(poItemLabel = 'PO Line', poItem = null, poData = null) {
        if (!poData) poData = currentSelectedPoData;
        const availablePoItems = (poData && poData.items) ? poData.items : [];

        // If no specific poItem passed, select first unused item from PO
        if (!poItem && availablePoItems.length > 0) {
            const usedNames = Array.from(receiptLinesTbody.querySelectorAll('.line-po-item-select')).map(s => s.value);
            poItem = availablePoItems.find(it => !usedNames.includes(it.name)) || availablePoItems[0];
        }

        if (!poItem) return;

        const name = poItem.name;
        const qtyOrdered = parseFloat(poItem.qty || 0);
        const qtyRec = qtyOrdered;
        const qtyAcc = qtyOrdered;
        const price = parseFloat(poItem.unit_price || 0);
        const isServices = receiptTypeInput ? (receiptTypeInput.value === 'services') : false;
        const lineTotal = (parseFloat(qtyAcc) * parseFloat(price)).toFixed(2);

        // Build item select options restricted strictly to items on this PO
        let itemSelectOptionsHtml = '';
        availablePoItems.forEach(it => {
            const isSelected = (it.name === name);
            itemSelectOptionsHtml += `<option value="${it.name.replace(/"/g, '&quot;')}" ${isSelected ? 'selected' : ''}>${it.name}</option>`;
        });

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <input type="text" name="lines[][po_item]" class="form-control line-po-item" value="${poItemLabel}" style="padding:4px 8px;font-size:0.75rem; background-color:#f8fafc;" readonly>
            </td>
            <td>
                <select name="lines[][name]" class="form-control line-po-item-select" style="padding:4px 8px;font-size:0.75rem;font-weight:600;color:#0f172a;" required>
                    ${itemSelectOptionsHtml}
                </select>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control line-qty-ordered" value="${qtyOrdered}" style="padding:4px 8px;font-size:0.75rem; background-color:#f8fafc;" readonly>
            </td>
            <td>
                <input type="number" step="0.01" name="lines[][qty_received]" class="form-control line-qty-received" value="${qtyRec}" style="padding:4px 8px;font-size:0.75rem;" required>
            </td>
            <td>
                <input type="number" step="0.01" name="lines[][qty_accepted]" class="form-control line-qty-accepted" value="${qtyAcc}" style="padding:4px 8px;font-size:0.75rem;" required>
            </td>
            <td>
                <input type="number" step="0.01" name="lines[][unit_price]" class="form-control line-unit-price" value="${price}" style="padding:4px 8px;font-size:0.75rem; background-color:#f8fafc;" readonly required>
            </td>
            <td>
                <input type="text" class="form-control line-total-val" value="₱${Number(lineTotal).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}" style="padding:4px 8px;font-size:0.75rem; font-weight:700; background-color:#f8fafc;" readonly>
            </td>
            <td>
                <select name="lines[][condition]" class="form-control line-condition" style="padding:4px 6px;font-size:0.75rem;">
                    <option value="OK" selected>${isServices ? 'Completed' : 'OK'}</option>
                    <option value="Damaged">${isServices ? 'Defective Work' : 'Damaged'}</option>
                    <option value="Partial">${isServices ? 'Partially Done' : 'Partial'}</option>
                </select>
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn-remove-line" style="background:none;border:none;color:#ef4444;cursor:pointer;" title="Remove Line"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
            </td>
        `;

        // Row Remove Listener
        tr.querySelector('.btn-remove-line').addEventListener('click', () => {
            tr.remove();
            isFormDirty = true;
            updatePoItemDropdowns();
            updateAddLineButtonState();
            updateLiveThreeWayMatching();
        });

        // Dropdown Item Change Listener
        const itemSelect = tr.querySelector('.line-po-item-select');
        if (itemSelect) {
            itemSelect.addEventListener('change', () => {
                const selectedName = itemSelect.value;
                const foundItem = availablePoItems.find(it => it.name === selectedName);
                if (foundItem) {
                    tr.querySelector('.line-qty-ordered').value = foundItem.qty || 0;
                    tr.querySelector('.line-qty-received').value = foundItem.qty || 0;
                    tr.querySelector('.line-qty-accepted').value = foundItem.qty || 0;
                    tr.querySelector('.line-unit-price').value = foundItem.unit_price || 0;
                    recalculateRow(tr);
                }
                updatePoItemDropdowns();
                updateAddLineButtonState();
                isFormDirty = true;
                updateLiveThreeWayMatching();
            });
        }

        // Input recalculation & live validation listeners
        const inputs = tr.querySelectorAll('.line-qty-received, .line-qty-accepted, .line-condition');
        inputs.forEach(inp => {
            inp.addEventListener('input', () => {
                recalculateRow(tr);
                isFormDirty = true;
                updateLiveThreeWayMatching();
            });
            inp.addEventListener('change', () => {
                recalculateRow(tr);
                isFormDirty = true;
                updateLiveThreeWayMatching();
            });
        });

        receiptLinesTbody.appendChild(tr);
        recalculateRow(tr);
        updatePoItemDropdowns();
        updateAddLineButtonState();
    }

    // Disable already selected PO items in other rows' dropdowns
    function updatePoItemDropdowns() {
        const selects = Array.from(receiptLinesTbody.querySelectorAll('.line-po-item-select'));
        const selectedValues = selects.map(s => s.value);

        selects.forEach(select => {
            const currentVal = select.value;
            Array.from(select.options).forEach(opt => {
                if (opt.value !== currentVal && selectedValues.includes(opt.value)) {
                    opt.disabled = true;
                    opt.style.color = '#cbd5e1';
                } else {
                    opt.disabled = false;
                    opt.style.color = '#0f172a';
                }
            });
        });
    }

    // Enable / Disable Add Line button based on remaining PO items
    function updateAddLineButtonState() {
        if (!btnAddLine) return;
        const availableItems = (currentSelectedPoData && currentSelectedPoData.items) ? currentSelectedPoData.items : [];
        const usedCount = receiptLinesTbody.querySelectorAll('tr').length;

        if (availableItems.length === 0 || usedCount >= availableItems.length) {
            btnAddLine.disabled = true;
            btnAddLine.style.opacity = '0.5';
            btnAddLine.style.cursor = 'not-allowed';
            btnAddLine.title = 'All items from this Purchase Order have already been added';
        } else {
            btnAddLine.disabled = false;
            btnAddLine.style.opacity = '1';
            btnAddLine.style.cursor = 'pointer';
            btnAddLine.title = 'Add line from Purchase Order';
        }
    }

    // Searchable Autocomplete Engine (Floating Portal)
    function setupAutocomplete(tr) {
        const input = tr.querySelector('.line-name-input');
        const unitPriceInput = tr.querySelector('.line-unit-price');
        if (!input) return;

        // Create floating dropdown element attached to document.body to prevent table clipping
        const dropdown = document.createElement('div');
        dropdown.className = 'autocomplete-dropdown hidden';
        document.body.appendChild(dropdown);

        let activeIndex = -1;

        function positionDropdown() {
            if (dropdown.classList.contains('hidden')) return;
            const rect = input.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            dropdown.style.width = `${Math.max(rect.width, 220)}px`;
            dropdown.style.left = `${rect.left}px`;

            // Auto-position above or below depending on available screen space
            if (spaceBelow < 180 && spaceAbove > spaceBelow) {
                dropdown.style.top = 'auto';
                dropdown.style.bottom = `${window.innerHeight - rect.top + 2}px`;
            } else {
                dropdown.style.bottom = 'auto';
                dropdown.style.top = `${rect.bottom + 2}px`;
            }
        }

        function renderDropdown(filterText = '') {
            const query = (filterText || '').trim().toLowerCase();
            let matches = existingProductsData || [];
            if (query) {
                matches = matches.filter(p => p.name && p.name.toLowerCase().includes(query));
            }

            dropdown.innerHTML = '';
            activeIndex = -1;

            if (matches.length === 0 && !query) {
                dropdown.classList.add('hidden');
                return;
            }

            matches.forEach((prod, idx) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'autocomplete-item';
                itemDiv.dataset.index = idx;
                itemDiv.dataset.name = prod.name;
                itemDiv.dataset.price = prod.unit_price || 0;

                const priceVal = parseFloat(prod.unit_price || 0);
                const priceStr = priceVal > 0 ? `₱${Number(priceVal).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}` : '';

                itemDiv.innerHTML = `
                    <span style="font-weight:600;">${prod.name}</span>
                    ${priceStr ? `<span class="item-price" style="font-weight:600;color:#059669;">${priceStr}</span>` : ''}
                `;

                itemDiv.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectProduct(prod.name, prod.unit_price);
                });

                dropdown.appendChild(itemDiv);
            });

            // Option to add/use custom item if query is entered and not an exact match
            const exactMatch = matches.some(p => p.name && p.name.toLowerCase() === query);
            if (query && !exactMatch) {
                const customDiv = document.createElement('div');
                customDiv.className = 'autocomplete-item autocomplete-custom-item';
                customDiv.innerHTML = `<span style="display:inline-flex;align-items:center;gap:4px;"><i data-lucide="plus-circle" style="width:13px;height:13px;"></i> Add Custom Item: "${filterText.trim()}"</span>`;
                customDiv.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectProduct(filterText.trim(), null);
                });
                dropdown.appendChild(customDiv);
            }

            dropdown.classList.remove('hidden');
            positionDropdown();
            if (window.lucide) lucide.createIcons();
        }

        function selectProduct(name, price) {
            input.value = name;
            if (price !== null && price !== undefined && parseFloat(price) > 0 && unitPriceInput) {
                unitPriceInput.value = price;
            }
            dropdown.classList.add('hidden');
            recalculateRow(tr);
            isFormDirty = true;
            updateLiveThreeWayMatching();
        }

        input.addEventListener('focus', () => {
            renderDropdown(input.value);
        });

        input.addEventListener('input', () => {
            renderDropdown(input.value);
            recalculateRow(tr);
            isFormDirty = true;
            updateLiveThreeWayMatching();
        });

        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('.autocomplete-item');
            if (items.length === 0 || dropdown.classList.contains('hidden')) {
                if (e.key === 'Enter') {
                    dropdown.classList.add('hidden');
                }
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && activeIndex < items.length) {
                    items[activeIndex].dispatchEvent(new MouseEvent('mousedown'));
                } else if (items.length > 0) {
                    items[0].dispatchEvent(new MouseEvent('mousedown'));
                } else {
                    dropdown.classList.add('hidden');
                }
            } else if (e.key === 'Escape') {
                dropdown.classList.add('hidden');
            }
        });

        function updateActiveItem(items) {
            items.forEach((it, idx) => {
                if (idx === activeIndex) {
                    it.classList.add('active');
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.classList.remove('active');
                }
            });
        }

        input.addEventListener('blur', () => {
            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 200);
        });

        // Reposition floating dropdown on scroll or resize
        const handleScrollOrResize = () => {
            if (!dropdown.classList.contains('hidden')) {
                positionDropdown();
            }
        };
        window.addEventListener('scroll', handleScrollOrResize, true);
        window.addEventListener('resize', handleScrollOrResize);

        // Cleanup when row is removed
        const removeBtn = tr.querySelector('.btn-remove-line');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                window.removeEventListener('scroll', handleScrollOrResize, true);
                window.removeEventListener('resize', handleScrollOrResize);
                if (dropdown.parentNode) {
                    dropdown.parentNode.removeChild(dropdown);
                }
            });
        }
    }

    function recalculateRow(tr) {
        const qtyOrd = parseFloat(tr.querySelector('.line-qty-ordered')?.value || 0);
        const qtyRecEl = tr.querySelector('.line-qty-received');
        const qtyAccEl = tr.querySelector('.line-qty-accepted');
        const priceEl = tr.querySelector('.line-unit-price');
        const totalEl = tr.querySelector('.line-total-val');

        const qtyRec = parseFloat(qtyRecEl?.value || 0);
        const qtyAcc = parseFloat(qtyAccEl?.value || 0);
        const price = parseFloat(priceEl?.value || 0);

        // Validation Rules (Requirement 3)
        if (qtyRec > qtyOrd && qtyOrd > 0) {
            qtyRecEl.style.borderColor = '#d97706';
            qtyRecEl.title = 'Warning: Quantity received exceeds ordered quantity';
        } else if (qtyRec < 0) {
            qtyRecEl.style.borderColor = '#dc2626';
            qtyRecEl.title = 'Error: Negative quantities not allowed';
        } else {
            qtyRecEl.style.borderColor = '#cbd5e1';
            qtyRecEl.title = '';
        }

        if (qtyAcc > qtyRec) {
            qtyAccEl.style.borderColor = '#dc2626';
            qtyAccEl.title = 'Error: Quantity accepted cannot exceed quantity received';
        } else if (qtyAcc < 0) {
            qtyAccEl.style.borderColor = '#dc2626';
            qtyAccEl.title = 'Error: Negative quantities not allowed';
        } else {
            qtyAccEl.style.borderColor = '#cbd5e1';
            qtyAccEl.title = '';
        }

        const lTotal = Math.max(0, qtyAcc * price);
        if (totalEl) {
            totalEl.value = '₱' + Number(lTotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }

    if (btnAddLine) {
        btnAddLine.addEventListener('click', () => {
            if (!currentSelectedPoData || !currentSelectedPoData.items || currentSelectedPoData.items.length === 0) {
                showToast("Please select a Purchase Order first.", "warning");
                return;
            }

            const usedNames = Array.from(receiptLinesTbody.querySelectorAll('.line-po-item-select')).map(s => s.value);
            const unusedItem = currentSelectedPoData.items.find(it => !usedNames.includes(it.name));

            if (unusedItem) {
                const lineIndex = receiptLinesTbody.querySelectorAll('tr').length + 1;
                appendPoReceiptLineRow(`Item #${lineIndex}`, unusedItem, currentSelectedPoData);
                lucide.createIcons();
                isFormDirty = true;
                updateLiveThreeWayMatching();
            } else {
                showToast("All items from this Purchase Order have already been added.", "info");
            }
        });
    }

    // Live Three-Way Matching Engine & Discrepancy Detection (Requirement 5 & 6)
    function updateLiveThreeWayMatching() {
        let receivedTotal = 0;
        let hasLineErrors = false;
        let hasShortage = false;

        const rows = receiptLinesTbody.querySelectorAll('tr');
        rows.forEach(tr => {
            const qtyOrd = parseFloat(tr.querySelector('.line-qty-ordered')?.value || 0);
            const qtyRec = parseFloat(tr.querySelector('.line-qty-received')?.value || 0);
            const qtyAcc = parseFloat(tr.querySelector('.line-qty-accepted')?.value || 0);
            const price = parseFloat(tr.querySelector('.line-unit-price')?.value || 0);

            if (qtyAcc > qtyRec || qtyRec < 0 || qtyAcc < 0 || price < 0) {
                hasLineErrors = true;
            }
            if (qtyAcc < qtyOrd && qtyOrd > 0) {
                hasShortage = true;
            }

            receivedTotal += (qtyAcc * price);
        });

        const poTotal = selectedPoTotal > 0 ? selectedPoTotal : receivedTotal;
        const invNumber = grnInvoiceNumber ? grnInvoiceNumber.value.trim() : '';
        const invAmountVal = grnInvoiceAmount ? parseFloat(grnInvoiceAmount.value) : 0;
        const invDateVal = grnInvoiceDate ? grnInvoiceDate.value : '';
        const dueDateVal = grnDueDate ? grnDueDate.value : '';

        const fmt = (num) => '₱' + Number(num || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        if (livePoVal) livePoVal.textContent = fmt(poTotal);
        if (liveGrnVal) liveGrnVal.textContent = fmt(receivedTotal);
        if (liveInvVal) liveInvVal.textContent = invAmountVal > 0 ? fmt(invAmountVal) : '—';

        // Discrepancy Detection Rules
        let liveStatus = 'Matched';
        let discrepancies = [];

        if (!invNumber || !invAmountVal) {
            liveStatus = 'Pending Invoice';
            discrepancies.push('Invoice details pending manual entry.');
        } else {
            const variance = invAmountVal - poTotal;
            if (Math.abs(variance) > 0.01) {
                if (variance > 0) {
                    liveStatus = 'Mismatch';
                    discrepancies.push(`Over-Invoiced: Invoice amount (${fmt(invAmountVal)}) exceeds PO value (${fmt(poTotal)}) by +${fmt(variance)}.`);
                } else {
                    liveStatus = 'Partial Match';
                    discrepancies.push(`Under-Invoiced: Invoice amount (${fmt(invAmountVal)}) is less than PO value (${fmt(poTotal)}) by -${fmt(Math.abs(variance))}.`);
                }
            }

            if (hasShortage) {
                if (liveStatus === 'Matched') liveStatus = 'Partial Match';
                discrepancies.push('Quantity Shortage: Received/accepted items are less than ordered PO quantity.');
            }
        }

        // Live Badge Update
        if (liveMatchingBadge) {
            if (liveStatus === 'Matched') {
                liveMatchingBadge.innerHTML = `
                    <span class="status-badge badge-matched">
                        <i data-lucide="check-circle-2"></i>
                        <span>Matched</span>
                    </span>`;
            } else if (liveStatus === 'Partial Match') {
                liveMatchingBadge.innerHTML = `
                    <span class="status-badge badge-partial">
                        <i data-lucide="alert-circle"></i>
                        <span>Partial Match</span>
                    </span>`;
            } else if (liveStatus === 'Mismatch') {
                liveMatchingBadge.innerHTML = `
                    <span class="status-badge badge-mismatch">
                        <i data-lucide="x-circle"></i>
                        <span>Mismatch</span>
                    </span>`;
            } else {
                liveMatchingBadge.innerHTML = `
                    <span class="status-badge badge-pending">
                        <i data-lucide="clock"></i>
                        <span>Pending Invoice</span>
                    </span>`;
            }
        }

        // Discrepancy Warnings Banner (Requirement 6)
        if (liveDiscrepancyBox && liveDiscrepancyText) {
            if (discrepancies.length === 0 && liveStatus === 'Matched') {
                liveDiscrepancyBox.className = 'recon-alert alert-reconciled';
                liveDiscrepancyText.textContent = '3-Way Match Verified: PO, Goods Receipt, and Invoice amounts fully reconciled.';
            } else if (liveStatus === 'Pending Invoice') {
                liveDiscrepancyBox.className = 'recon-alert alert-variance';
                liveDiscrepancyText.textContent = 'Goods receipt ready. Enter invoice number and invoice amount to complete live 3-way matching.';
            } else {
                liveDiscrepancyBox.className = liveStatus === 'Mismatch' ? 'recon-alert alert-mismatch' : 'recon-alert alert-variance';
                liveDiscrepancyText.textContent = discrepancies.join(' ');
            }
        }

        // Matching Notes Requirement (Requirement 7)
        if (notesRequiredIndicator && grnMatchingNotes) {
            if (liveStatus === 'Mismatch' || liveStatus === 'Partial Match') {
                notesRequiredIndicator.classList.remove('hidden');
                grnMatchingNotes.required = true;
                grnMatchingNotes.style.borderColor = '#ea580c';
            } else {
                notesRequiredIndicator.classList.add('hidden');
                grnMatchingNotes.required = false;
                grnMatchingNotes.style.borderColor = '#cbd5e1';
            }
        }

        lucide.createIcons();
        return { liveStatus, discrepancies, hasLineErrors };
    }

    // Form Validation (Requirement 4)
    function validateGrnForm() {
        const errors = [];

        if (!grnPoSelect || !grnPoSelect.value) {
            errors.push("Purchase Order selection is required.");
        }

        // Invoice Number Uniqueness Validation
        const invNum = grnInvoiceNumber ? grnInvoiceNumber.value.trim() : '';
        if (invNum) {
            const exists = allRecords.some(r => r.invoice_number && r.invoice_number.toLowerCase() === invNum.toLowerCase());
            if (exists) {
                errors.push(`Invoice number "${invNum}" already exists in matching records.`);
            }
        }

        // Invoice Amount Validation
        const invAmt = grnInvoiceAmount ? parseFloat(grnInvoiceAmount.value) : 0;
        if (grnInvoiceAmount && grnInvoiceAmount.value !== '' && (isNaN(invAmt) || invAmt <= 0)) {
            errors.push("Invoice amount must be a positive numeric value greater than ₱0.00.");
        }

        // Date Validations
        const todayStr = new Date().toISOString().split('T')[0];
        if (grnInvoiceDate && grnInvoiceDate.value && grnInvoiceDate.value > todayStr) {
            errors.push("Invoice date cannot be in the future.");
        }

        if (grnInvoiceDate && grnDueDate && grnInvoiceDate.value && grnDueDate.value) {
            if (grnDueDate.value < grnInvoiceDate.value) {
                errors.push("Payment Due Date must be after Invoice Date.");
            }
        }

        // Live Line Validations
        const rows = receiptLinesTbody.querySelectorAll('tr');
        if (rows.length === 0) {
            errors.push("At least one Purchase Order receipt line is required.");
        }

        rows.forEach((tr, index) => {
            const itemSelect = tr.querySelector('.line-po-item-select');
            const qtyOrd = parseFloat(tr.querySelector('.line-qty-ordered')?.value || 0);
            const qtyRec = parseFloat(tr.querySelector('.line-qty-received')?.value || 0);
            const qtyAcc = parseFloat(tr.querySelector('.line-qty-accepted')?.value || 0);
            const price = parseFloat(tr.querySelector('.line-unit-price')?.value || 0);
            const itemName = itemSelect ? itemSelect.value : `Row #${index+1}`;

            if (qtyAcc > qtyRec) {
                errors.push(`${itemName}: Quantity accepted (${qtyAcc}) cannot exceed quantity received (${qtyRec}).`);
            }
            if (qtyRec < 0 || qtyAcc < 0 || price < 0) {
                errors.push(`${itemName}: Negative quantities or unit prices are not allowed.`);
            }
            if (qtyRec > qtyOrd && qtyOrd > 0) {
                const qtyRecEl = tr.querySelector('.line-qty-received');
                if (qtyRecEl) {
                    qtyRecEl.style.borderColor = '#d97706';
                    qtyRecEl.title = 'Warning: Quantity received exceeds ordered PO quantity';
                }
            }
        });

        // Notes Validation for Discrepancy
        const matchState = updateLiveThreeWayMatching();
        if ((matchState.liveStatus === 'Mismatch' || matchState.liveStatus === 'Partial Match') && grnMatchingNotes) {
            if (!grnMatchingNotes.value.trim()) {
                errors.push("Matching Notes are required when a mismatch or quantity discrepancy is detected.");
            }
        }

        if (formValidationAlerts) {
            if (errors.length > 0) {
                formValidationAlerts.innerHTML = errors.map(err => `<div>• ${err}</div>`).join('');
                formValidationAlerts.classList.remove('hidden');
                formValidationAlerts.style.display = 'flex';
            } else {
                formValidationAlerts.innerHTML = '';
                formValidationAlerts.classList.add('hidden');
                formValidationAlerts.style.display = 'none';
            }
        }

        return errors.length === 0;
    }

    // Submit GRN Form AJAX Handler (Requirement 8 & 9)
    if (recordGrnForm) {
        recordGrnForm.addEventListener('submit', (e) => {
            e.preventDefault();

            if (!validateGrnForm()) {
                showToast("Form validation failed. Please correct indicated errors.", "error");
                return;
            }

            const btnSubmit = document.getElementById('btn-submit-grn');
            const btnText = document.getElementById('btn-submit-grn-text');
            if (btnSubmit) btnSubmit.disabled = true;
            if (btnText) btnText.textContent = "Processing 3-Way Match...";

            const formData = new FormData(recordGrnForm);

            fetch("{{ route('matching.store_grn') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (btnSubmit) btnSubmit.disabled = false;
                if (btnText) btnText.textContent = "Submit Goods Receipt & Match";

                if (data.success) {
                    isFormDirty = false;
                    if (grnNumberInput && data.grn_number) {
                        grnNumberInput.value = data.grn_number;
                    }
                    recordGrnView.classList.add('hidden');
                    dashboardViewportView.classList.remove('hidden');

                    let msg = `Receipt ${data.grn_number || ''} recorded successfully.`;
                    if (data.status === 'Matched') {
                        msg = `Receipt ${data.grn_number || ''} recorded successfully. Invoice 3-way matched.`;
                    } else if (data.status === 'Partial Match' || data.status === 'Mismatch') {
                        msg = `Receipt ${data.grn_number || ''} recorded. Discrepancy requires review.`;
                    }
                    showToast(msg, data.status === 'Matched' ? 'success' : 'warning');

                    const newKey = data.record ? (data.record.po_number + '-' + data.record.supplier.replace(/\s+/g, '')) : (data.po_number ? (data.po_number + '-' + (data.supplier || '').replace(/\s+/g, '')) : null);
                    refreshData(newKey);
                } else {
                    showToast(data.message || "Failed to record Goods Receipt.", "error");
                }
            })
            .catch(err => {
                if (btnSubmit) btnSubmit.disabled = false;
                if (btnText) btnText.textContent = "Submit Goods Receipt & Match";
                showToast("Failed to record Goods Receipt. " + (err.message || ""), "error");
            });
        });
    }

    // Goods / Services Type Toggle inside Record Form
    const btnTypeGoods = document.getElementById('btn-type-goods');
    const btnTypeServices = document.getElementById('btn-type-services');
    const receiptTypeInput = document.getElementById('receipt-type-input');
    const lblGrnNumber = document.getElementById('lbl-grn-number');
    const grnNumberInput = document.getElementById('grn-number-input');
    const lblReceivedAt = document.getElementById('lbl-received-at');
    const lblLocation = document.getElementById('lbl-location');
    const lblLinesHeader = document.getElementById('lbl-lines-header');
    const receiptTableHeader = document.getElementById('receipt-table-header');
    const btnSubmitGrnText = document.getElementById('btn-submit-grn-text');

    if (btnTypeGoods && btnTypeServices) {
        btnTypeGoods.addEventListener('click', () => {
            receiptTypeInput.value = 'goods';
            btnTypeGoods.classList.add('active');
            btnTypeGoods.style.backgroundColor = '#ffffff';
            btnTypeGoods.style.color = '#1e7d43';
            btnTypeGoods.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';

            btnTypeServices.classList.remove('active');
            btnTypeServices.style.backgroundColor = 'transparent';
            btnTypeServices.style.color = '#64748b';
            btnTypeServices.style.boxShadow = 'none';

            lblGrnNumber.textContent = 'GRN NUMBER';
            grnNumberInput.value = 'Auto-generated upon submission';
            lblReceivedAt.textContent = 'RECEIVED AT';
            lblLocation.textContent = 'RECEIVING LOCATION';
            grnLocationInput.placeholder = 'Warehouse or receiving bay';
            lblLinesHeader.textContent = 'Goods Receipt Lines';
            btnSubmitGrnText.textContent = 'Submit Goods Receipt & Match';

            receiptTableHeader.innerHTML = `
                <th style="width: 14%;">PO ITEM</th>
                <th style="width: 22%;">ITEM NAME</th>
                <th style="width: 9%;">QTY ORDERED</th>
                <th style="width: 9%;">QTY RECEIVED</th>
                <th style="width: 9%;">QTY ACCEPTED</th>
                <th style="width: 11%;">UNIT PRICE (₱)</th>
                <th style="width: 11%;">LINE TOTAL (₱)</th>
                <th style="width: 9%;">CONDITION</th>
                <th style="width: 6%;"></th>
            `;
            isFormDirty = true;
            updateLiveThreeWayMatching();
        });

        btnTypeServices.addEventListener('click', () => {
            receiptTypeInput.value = 'services';
            btnTypeServices.classList.add('active');
            btnTypeServices.style.backgroundColor = '#ffffff';
            btnTypeServices.style.color = '#0284c7';
            btnTypeServices.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';

            btnTypeGoods.classList.remove('active');
            btnTypeGoods.style.backgroundColor = 'transparent';
            btnTypeGoods.style.color = '#64748b';
            btnTypeGoods.style.boxShadow = 'none';

            lblGrnNumber.textContent = 'SRN / SERVICE RECEIPT #';
            grnNumberInput.value = 'Auto-generated upon submission';
            lblReceivedAt.textContent = 'SERVICE ENTRY DATE';
            lblLocation.textContent = 'SERVICE LOCATION / SITE';
            grnLocationInput.placeholder = 'Service site, department, or workshop';
            lblLinesHeader.textContent = 'Service Entry Lines';
            btnSubmitGrnText.textContent = 'Submit Service Receipt & Match';

            receiptTableHeader.innerHTML = `
                <th style="width: 14%;">PO ITEM</th>
                <th style="width: 22%;">SERVICE TASK</th>
                <th style="width: 9%;">UNITS LOGGED</th>
                <th style="width: 9%;">UNITS REC</th>
                <th style="width: 9%;">UNITS ACC</th>
                <th style="width: 11%;">RATE (₱)</th>
                <th style="width: 11%;">LINE TOTAL (₱)</th>
                <th style="width: 9%;">STATUS</th>
                <th style="width: 6%;"></th>
            `;
            isFormDirty = true;
            updateLiveThreeWayMatching();
        });
    }

    // More Filters Modal Events
    if (btnMoreFilters) {
        btnMoreFilters.addEventListener('click', () => {
            moreFiltersModal.classList.remove('hidden');
            lucide.createIcons();
        });
    }

    if (closeFiltersModal) {
        closeFiltersModal.addEventListener('click', () => moreFiltersModal.classList.add('hidden'));
    }

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', () => {
            filterWarehouse.value = 'All Warehouses';
            filterVariance.value = '';
            filterCommodity.value = '';
            filterMinAmount.value = '';
            filterMaxAmount.value = '';
            applyFilters();
            moreFiltersModal.classList.add('hidden');
        });
    }

    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            applyFilters();
            moreFiltersModal.classList.add('hidden');
        });
    }

    // Manual Dismissal Event Listener
    const toastCloseBtn = document.getElementById('toast-close-btn');
    if (toastCloseBtn) {
        toastCloseBtn.addEventListener('click', dismissToast);
    }

    // Initial render
    applyFilters();

    // Document-level event listener (Guarantees #btn-cancel-transaction works at ANY time without prior actions)
    document.addEventListener('click', function(e) {
        const btnCancelTx = e.target.closest('#btn-cancel-transaction');
        if (btnCancelTx) {
            e.preventDefault();
            e.stopPropagation();
            const targetRecord = currentSelectedRecord || allRecords.find(r => (r.po_number + '-' + r.supplier.replace(/\s+/g, '')) === selectedRecordKey || r.po_number === selectedRecordKey) || (currentFilteredRecords && currentFilteredRecords.length > 0 ? currentFilteredRecords[0] : allRecords[0]);
            if (targetRecord) {
                openCancelModal(targetRecord);
            }
        }
    });

    // Bind summary panel for initial selected record on load
    const initRecord = allRecords.find(r => (r.po_number + '-' + r.supplier.replace(/\s+/g, '')) === selectedRecordKey || r.po_number === selectedRecordKey) || (allRecords && allRecords.length > 0 ? allRecords[0] : null);
    if (initRecord) {
        currentSelectedRecord = initRecord;
        updateSummaryPanel(initRecord);
    }

    lucide.createIcons();
</script>

<!-- Toast Notification Container with Smooth Animation & Manual Dismissal -->
<div id="toast-notification" class="hidden" style="position: fixed; bottom: 24px; right: 24px; z-index: 2000; background-color: #0f172a; color: #ffffff; padding: 12px 18px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); display: none; align-items: center; gap: 12px; font-size: 0.85rem; font-weight: 600; transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; transform: translateY(10px); pointer-events: none;">
    <i id="toast-icon" data-lucide="check-circle-2" style="width:20px;height:20px;color:#22c55e;flex-shrink:0;"></i>
    <span id="toast-message" style="flex:1;">Goods Receipt recorded successfully.</span>
    <button type="button" id="toast-close-btn" style="background:none; border:none; color:#cbd5e1; cursor:pointer; padding:2px; margin-left:6px; display:flex; align-items:center; justify-content:center; border-radius:4px;" title="Dismiss">
        <i data-lucide="x" style="width:16px;height:16px;"></i>
    </button>
</div>
@endsection
