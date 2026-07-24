@extends('layouts.procurement', [
    'pageTitle' => 'Side Notifications',
    'workspaceTitle' => 'Side Notifications',
    'workspaceSubtitle' => 'A consistent alert sidebar with the same procurement palette and spacing.',
    'activePage' => 'notif',
])

@section('content')
    <section class="drawer" style="max-width: 420px; margin-left:auto; background: transparent; box-shadow: none; padding: 0;">
        <div class="drawer-body" style="padding: 0;">
            
            <div class="drawer-card" style="background: transparent; border: none; box-shadow: none; margin-bottom: 24px; padding: 0;">
                <div class="drawer-title" style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #4a5568; letter-spacing: 0.05em; margin-bottom: 8px;">Approvals Inbox</div>
                <p class="muted" style="margin: 0; color: #718096; font-size: 14px;">Approval queue is empty.</p>
            </div>

            <div class="drawer-card" style="background: transparent; border: none; box-shadow: none; margin-bottom: 24px; padding: 0;">
                <div class="drawer-title" style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #4a5568; letter-spacing: 0.05em; margin-bottom: 12px;">PO Pipeline</div>
                <div class="stats" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 12px; display: grid; gap: 12px;">
                    <div class="stat-card" style="background: #fff; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div class="stat-label" style="font-size: 11px; text-transform: uppercase; color: #718096; font-weight: 600;">Drafts</div>
                        <div class="stat-value" style="font-size: 18px; font-weight: 700; color: #2d3748;">1</div>
                    </div>
                    <div class="stat-card" style="background: #fff; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div class="stat-label" style="font-size: 11px; text-transform: uppercase; color: #718096; font-weight: 600;">In Flight</div>
                        <div class="stat-value" style="font-size: 18px; font-weight: 700; color: #2d3748;">2</div>
                    </div>
                </div>
                <div class="stat-card" style="background: #fff; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div class="stat-label" style="font-size: 11px; text-transform: uppercase; color: #718096; font-weight: 600;">Total Procurement Value</div>
                    <div class="stat-value" style="font-size: 18px; font-weight: 700; color: #2d3748;">₱227,920</div>
                </div>
            </div>

            <div class="drawer-card" style="background: transparent; border: none; box-shadow: none; margin-bottom: 24px; padding: 0;">
                <div class="drawer-title" style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #4a5568; letter-spacing: 0.05em; margin-bottom: 8px;">Delivery Alerts</div>
                <div style="background: #fff; border: 1px solid #fed7d7; border-left: 4px solid #e53e3e; padding: 12px; border-radius: 6px;">
                    <div class="badge badge-overdue" style="display:inline-flex; font-size: 12px; font-weight: 700; color: #e53e3e; margin-bottom: 4px;">⚠️ PO-2026-001</div>
                    <div style="font-size: 11px; color: #e53e3e; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Overdue Delivery</div>
                    <p style="margin:0; font-weight:700; font-size: 14px; color: #2d3748;">AgriSource PH Inc.</p>
                </div>
            </div>

            <div class="drawer-card" style="background: transparent; border: none; box-shadow: none; margin-bottom: 24px; padding: 0;">
                <div class="drawer-title" style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #4a5568; letter-spacing: 0.05em; margin-bottom: 8px;">Invoice Matching</div>
                <div style="background: #fff; border: 1px solid #feebc8; border-left: 4px solid #dd6b20; padding: 12px; border-radius: 6px;">
                    <div class="badge badge-partial" style="display:inline-flex; font-size: 12px; font-weight: 700; color: #dd6b20; margin-bottom: 4px;">INV-SUP-2026-003</div>
                    <div style="font-size: 11px; color: #dd6b20; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Overdue Delivery / Discrepancy</div>
                    <p style="margin:0; font-weight:700; font-size: 14px; color: #2d3748;">TechVend Solutions</p>
                    <p class="muted" style="margin:2px 0 0; font-size: 13px; color: #718096;">₱67,200</p>
                </div>
            </div>

            <div class="drawer-card" style="background: transparent; border: none; box-shadow: none; margin-bottom: 24px; padding: 0;">
                <div class="drawer-title" style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #4a5568; letter-spacing: 0.05em; margin-bottom: 8px;">Procurement Logs</div>
                <div style="font-size: 13px; color: #4a5568;">
                    <span style="font-weight: 600;">• PR PE-2026-003</span> approved by Agent
                </div>
            </div>

        </div>
    </section>
@endsection