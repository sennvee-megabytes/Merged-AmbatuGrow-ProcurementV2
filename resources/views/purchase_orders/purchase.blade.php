@extends('layouts.procurement', [
    'pageTitle' => 'Purchase Orders',
    'workspaceTitle' => 'Purchase Orders',
    'workspaceSubtitle' => 'Manage, track, and receive all purchase orders.',
    'activePage' => 'purchase',
])

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2>Purchase Orders</h2>
                <p>Use the same visual system across the procurement flow.</p>
            </div>
            <div class="action-row">
                <a class="btn btn-primary" href="{{ route('procurement.create') }}">+ Create PO</a>
            </div>
        </div>

        <div class="card-section">
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-label">TOTAL POS</div>
                    <div class="stat-value">4</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">PENDING / DRAFT</div>
                    <div class="stat-value">1</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">ACTIVE / SENT</div>
                    <div class="stat-value">1</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">OVERDUE</div>
                    <div class="stat-value">2</div>
                </div>
            </div>

            <div class="tabs" style="margin: 18px 0 20px;">
                <div class="tab active">All <span class="chip">4</span></div>
                <div class="tab">Draft <span class="chip">1</span></div>
                <div class="tab">Approved <span class="chip">0</span></div>
                <div class="tab">Sent to Supplier <span class="chip">1</span></div>
                <div class="tab">Fully Received <span class="chip">1</span></div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>PO ID</th>
                            <th>Supplier</th>
                            <th>Date Issued</th>
                            <th>Expected Delivery</th>
                            <th>Total (₱)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>PO-2026-004</td>
                            <td>OfficeMax PH</td>
                            <td>Jun 28, 2026</td>
                            <td>Jul 1, 2026</td>
                            <td>15,680.00</td>
                            <td><span class="badge badge-overdue">Draft / Overdue</span></td>
                            <td>
                                <div class="action-row">
                                    <a class="chip-link" href="{{ route('procurement.create') }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>PO-2026-002</td>
                            <td>TechVend Solutions</td>
                            <td>Jun 24, 2026</td>
                            <td>Jul 8, 2026</td>
                            <td>67,200.00</td>
                            <td><span class="badge badge-sent">Sent to Supplier</span></td>
                            <td><div class="action-row"><a class="chip-link" href="{{ route('procurement.notifications') }}">Review</a></div></td>
                        </tr>
                        <tr>
                            <td>PO-2026-003</td>
                            <td>Green Harvest Co.</td>
                            <td>Jun 18, 2026</td>
                            <td>Jun 23, 2026</td>
                            <td>22,400.00</td>
                            <td><span class="badge badge-received">Fully Received</span></td>
                            <td><div class="action-row"><a class="chip-link" href="{{ route('procurement.notifications') }}">Logs</a></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
