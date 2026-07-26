@extends('layouts.master')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('styles')
<style>
    .page-shell { min-height: 100vh; padding: 20px 28px; }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .brand h1 {
        margin: 0 0 6px;
        font-size: 32px;
        line-height: 1.05;
        color: #0f1724;
        letter-spacing: -0.01em;
    }

    .brand p {
        margin: 0;
        color: #657467;
        font-size: 14px;
    }

    .action-link,
    .btn,
    .chip-link {
        text-decoration: none;
        border-radius: 999px;
        font-weight: 700;
        transition: transform 0.15s ease, background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .action-link:hover,
    .btn:hover,
    .chip-link:hover { transform: translateY(-1px); }

    .content-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 520px;
        gap: 24px;
        align-items: start;
    }

    .dashboard-layout {
        display: grid;
        grid-template-columns: minmax(0, 4fr) minmax(320px, 1fr);
        gap: 24px;
        align-items: stretch;
    }

    .landing-grid {
        display: grid;
        gap: 24px;
    }

    .panel,
    .drawer,
    .table-card {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(220, 228, 218, 0.8);
        border-radius: 18px;
        box-shadow: 0 12px 34px rgba(25, 40, 30, 0.05);
        overflow: hidden;
    }

    .panel-header,
    .drawer-header {
        padding: 22px 24px;
        border-bottom: 1px solid rgba(232, 238, 230, 0.6);
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .panel-header h2,
    .drawer-header h2 {
        margin: 0 0 6px;
        color: #0f1724;
        font-size: 28px;
        font-weight: 800;
    }

    .panel-header p,
    .drawer-header p {
        margin: 0;
        color: #657467;
        font-size: 14px;
    }

    .card-section { padding: 24px; }

    .stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .stat-card {
        background: #f9fbf9;
        border: 1px solid #e3e8e1;
        border-radius: 14px;
        padding: 16px;
    }

    .stat-label {
        font-size: 12px;
        color: #657467;
        letter-spacing: .04em;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 900;
        color: #0f1724;
        font-variant-numeric: tabular-nums;
    }

    .tabs { display: flex; gap: 8px; flex-wrap: wrap; }

    .tab {
        background: #f6f7f8;
        border: 1px solid #e3e8e1;
        padding: 10px 14px;
        border-radius: 999px;
        font-size: 13px;
        color: #4b5563;
        font-weight: 600;
    }

    .tab.active {
        background: rgba(30, 125, 67, 0.08);
        border-color: #d5e9d9;
        color: #1e7d43;
    }

    .badge,
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-sent { background: #dbe6fd; color: #2354c9; }
    .badge-received { background: #d9f2e2; color: #1e7d43; }
    .badge-overdue { background: #dc2626; color: #fff; }
    .badge-partial { background: #fdf1c7; color: #92680b; }
    .badge-draft { background: #eef0f2; color: #4b5563; }

    .table-wrap {
        border: 1px solid #e4e8e2;
        border-radius: 14px;
        overflow: hidden;
    }

    .table-wrap table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .table-wrap thead th {
        background: #f8faf8;
        padding: 10px 12px;
        text-align: left;
        color: #657467;
        font-size: 12px;
        border-bottom: 1px solid rgba(232, 238, 230, 0.6);
        position: sticky;
        top: 0;
        z-index: 10;
        backdrop-filter: blur(4px);
    }

    .table-wrap tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #eef1ed;
        vertical-align: middle;
    }

    .table-wrap tbody td:nth-child(5),
    .table-wrap thead th:nth-child(5) {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .table-wrap tbody tr:last-child td { border-bottom: none; }

    .action-row { display: flex; gap: 8px; flex-wrap: wrap; }

    .btn,
    .action-link,
    .chip-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d8dfd4;
        background: #fff;
        color: #12502a;
        padding: 10px 14px;
        font-size: 13px;
    }

    .btn-primary { background: #1e7d43; color: #fff; border-color: #1e7d43; padding: 12px 18px; box-shadow: 0 8px 20px rgba(30,125,67,0.18); }
    .btn-ghost { background: rgba(30, 125, 67, 0.08); color: #12502a; border-color: #d5e9d9; }
    .btn-soft { background: #f4f5f7; color: #334155; border-color: #e2e8f0; }

    .drawer { display: flex; flex-direction: column; }

    .drawer-body {
        padding: 18px 20px 22px;
        display: grid;
        gap: 18px;
    }

    .drawer-card {
        background: #fff;
        border: 1px solid #e4e8e2;
        border-radius: 14px;
        padding: 16px;
    }

    .drawer-title {
        font-size: 12px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
        padding: 6px 0;
        color: #334155;
    }

    .summary-total {
        border-top: 1px solid #e5e7eb;
        margin-top: 8px;
        padding-top: 10px;
        font-weight: 800;
    }

    .form-group {
        display: grid;
        gap: 6px;
        margin-bottom: 12px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }

    .form-group select,
    .form-group input,
    .form-group textarea {
        width: 100%;
        border: 1px solid #d8dfd4;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 14px;
        color: #111827;
        background: #fff;
    }

    /* Modal & Extra Form Grid Styles */
    .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr .8fr 1fr;
        gap: 14px;
    }

    .drawer-footer {
        border-top: 1px solid #e4e8e2;
        padding: 20px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #fff;
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

    .modal-backdrop.is-open {
        display: flex;
    }

    .modal {
        width: min(100%, 980px);
        max-height: calc(100vh - 48px);
        overflow: auto;
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .modal .drawer-body {
        max-height: calc(100vh - 220px);
        overflow: auto;
    }

    body.modal-open .page-shell {
        pointer-events: none;
        user-select: none;
    }

    body.modal-open {
        overflow: hidden;
    }

    .section-anchor {
        scroll-margin-top: 24px;
    }

    .modal-close {
        cursor: pointer;
    }

    /* Override with block 2 styles for modal custom look */
    .drawer-card {
        padding: 18px;
    }

    .drawer-title {
        font-size: 14px;
        font-weight: 800;
        color: #2f5d34;
        margin-bottom: 14px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #4b5563;
    }

    .muted {
        color: #6b7280;
    }

    /* Dark Mode Overrides for Order Management Layout */
    .dark .panel,
    .dark .drawer,
    .dark .table-card,
    .dark .page-section,
    .dark #purchase.page-section,
    .dark #sidenotif.page-section,
    .dark .section-body {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
        box-shadow: 0 12px 34px rgba(0, 0, 0, 0.3) !important;
    }

    .dark .mini-card,
    .dark .log-item,
    .dark .chart-card {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }

    .dark .panel-header,
    .dark .drawer-header {
        border-bottom-color: #334155 !important;
    }

    .dark .panel-header h2,
    .dark .drawer-header h2,
    .dark .brand h1 {
        color: #f8fafc !important;
    }

    .dark .panel-header p,
    .dark .drawer-header p,
    .dark .brand p {
        color: #94a3b8 !important;
    }

    .dark .stat-card {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    .dark .stat-label {
        color: #94a3b8 !important;
    }

    .dark .stat-value {
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
        border-color: #334155 !important;
    }

    .dark .table-wrap thead th {
        background: #0f172a !important;
        color: #cbd5e1 !important;
        border-bottom-color: #334155 !important;
    }

    .dark .table-wrap tbody td {
        color: #e2e8f0 !important;
        border-bottom-color: #334155 !important;
    }

    .dark .btn,
    .dark .action-link,
    .dark .chip-link {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    .dark .btn-primary {
        background: #1e7d43 !important;
        color: #ffffff !important;
        border-color: #1e7d43 !important;
    }

    .dark .btn-soft {
        background: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    .dark .drawer-card {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    .dark .drawer-title {
        color: #34d399 !important;
    }

    .dark .summary-row {
        color: #cbd5e1 !important;
    }

    .dark .summary-total {
        border-top-color: #334155 !important;
    }

    .dark .form-group label {
        color: #cbd5e1 !important;
    }

    .dark .form-group select,
    .dark .form-group input,
    .dark .form-group textarea {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    .dark .drawer-footer {
        border-top-color: #334155 !important;
        background: #1e293b !important;
    }

    .dark .modal {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    .dark .muted {
        color: #94a3b8 !important;
    }
</style>
@endpush

@section('content')
    @yield('content')
@endsection
