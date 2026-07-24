@extends('layouts.procurement', [
    'pageTitle' => 'Create Purchase Order',
    'workspaceTitle' => 'Create Purchase Order',
    'workspaceSubtitle' => 'Purchase Orders stay visible on the left while you create a new PO on the right.',
    'activePage' => 'create',
])

@section('content')
    <div class="content-layout">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Purchase Orders</h2>
                    <p>The list on the left matches the same visual language as the main purchase page.</p>
                </div>
                <a class="action-link" href="{{ route('procurement.purchase') }}">Open full purchase page</a>
            </div>

            <div class="card-section">
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-label">TOTAL POS</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">PENDING / DRAFT</div>
                        <div class="stat-value">{{ $stats['draft'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">ACTIVE / SENT</div>
                        <div class="stat-value">{{ $stats['sent'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">OVERDUE</div>
                        <div class="stat-value" style="{{ $stats['overdue'] > 0 ? 'color: #dc2626;' : '' }}">{{ $stats['overdue'] }}</div>
                    </div>
                </div>

                

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>PO ID</th>
                                <th>Supplier</th>
                                <th>Expected Delivery</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $po)
                                @php
                                    $isOverdue = $po->status !== 'received' && $po->expected_delivery && $po->expected_delivery->isPast();
                                @endphp
                                <tr>
                                    <td style="font-weight: 700;">{{ $po->po_number }}</td>
                                    <td>{{ $po->supplier->name ?? '—' }}</td>
                                    <td style="{{ $isOverdue ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                        {{ optional($po->expected_delivery)->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td>
                                        @if($po->status === 'sent')
                                            <span class="badge badge-sent">Sent to Supplier</span>
                                        @elseif($po->status === 'received')
                                            <span class="badge badge-received">Fully Received</span>
                                        @elseif($po->status === 'draft')
                                            <span class="badge badge-draft">Draft</span>
                                        @else
                                            <span class="badge">{{ ucfirst($po->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px; color: var(--muted);">
                                        No purchase orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="drawer">
            <div class="drawer-header">
                <div>
                    <h2>Create Purchase Order</h2>
                    <p>Use one consistent card and form system for the PO creation drawer.</p>
                </div>
                <a class="action-link" href="{{ route('procurement.purchase') }}">Back to Purchase</a>
            </div>

            <div class="drawer-body">
                <form method="POST" action="{{ route('purchase_orders.store') }}" id="create-po-form">
                    @csrf
                    <div class="drawer-card">
                        <div class="drawer-title">Purchase Summary</div>
                        <div class="summary-row"><span>Subtotal</span><strong id="subtotal_display">₱0.00</strong></div>
                        <div class="summary-row"><span>VAT (12%)</span><strong id="vat_display">₱0.00</strong></div>
                        <div class="summary-row summary-total"><span>Total</span><strong id="total_display">₱0.00</strong></div>
                    </div>

                    <div class="drawer-card">
                        <div class="drawer-title">Supplier Information</div>
                        <div class="form-group">
                            <label>Supplier *</label>
                            <select name="supplier_id" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Expected Delivery</label>
                            <input type="date" name="expected_delivery">
                        </div>
                        <div class="form-group">
                            <label>Payment Terms</label>
                            <select name="payment_terms">
                                <option>Net 30</option>
                                <option>Net 15</option>
                                <option>COD</option>
                            </select>
                        </div>
                    </div>

                    <div class="drawer-card">
                        <div class="drawer-title">Line Items</div>
                        <div id="items-list">
                            <div class="item-row" data-index="0" style="display:grid; grid-template-columns: 1fr 1fr .8fr .8fr 40px; gap:8px; align-items:center; margin-bottom:8px;">
                                <input type="text" name="items[0][sku]" placeholder="SKU" />
                                <input type="text" name="items[0][name]" placeholder="Item name" required />
                                <input type="number" name="items[0][quantity]" value="1" min="1" class="item-qty" required />
                                <input type="number" step="0.01" name="items[0][unit_price]" value="0.00" class="item-price" required />
                                <button type="button" class="btn btn-soft remove-item" title="Remove">−</button>
                            </div>
                        </div>
                        <div style="margin-top:8px;">
                            <button type="button" id="add-item" class="btn btn-ghost">+ Add item</button>
                        </div>
                    </div>

                    <div class="drawer-card">
                        <div class="drawer-title">Notes / Instructions</div>
                        <div class="form-group" style="margin-bottom:0;">
                            <textarea name="notes" rows="4" placeholder="Delivery instructions or warehouse directions..."></textarea>
                        </div>
                    </div>

                    <div class="drawer-footer">
                        <a class="btn btn-ghost" href="{{ route('procurement.purchase') }}">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Draft</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        (function(){
            const addBtn = document.getElementById('add-item');
            const itemsList = document.getElementById('items-list');
            let index = 1;

            function recalc(){
                const rows = itemsList.querySelectorAll('.item-row');
                let subtotal = 0;
                rows.forEach(r=>{
                    const qty = parseFloat(r.querySelector('.item-qty').value) || 0;
                    const price = parseFloat(r.querySelector('.item-price').value) || 0;
                    subtotal += qty * price;
                });
                const vat = subtotal * 0.12;
                const total = subtotal + vat;
                document.getElementById('subtotal_display').textContent = '₱' + subtotal.toFixed(2);
                document.getElementById('vat_display').textContent = '₱' + vat.toFixed(2);
                document.getElementById('total_display').textContent = '₱' + total.toFixed(2);
            }

            addBtn.addEventListener('click', function(){
                const row = document.createElement('div');
                row.className = 'item-row';
                row.dataset.index = index;
                row.style = 'display:grid; grid-template-columns: 1fr 1fr .8fr .8fr 40px; gap:8px; align-items:center; margin-bottom:8px;';
                row.innerHTML = `
                    <input type="text" name="items[${index}][sku]" placeholder="SKU" />
                    <input type="text" name="items[${index}][name]" placeholder="Item name" required />
                    <input type="number" name="items[${index}][quantity]" value="1" min="1" class="item-qty" required />
                    <input type="number" step="0.01" name="items[${index}][unit_price]" value="0.00" class="item-price" required />
                    <button type="button" class="btn btn-soft remove-item" title="Remove">−</button>
                `;
                itemsList.appendChild(row);
                index++;
                row.querySelectorAll('.item-qty, .item-price').forEach(el=>el.addEventListener('input', recalc));
                row.querySelector('.remove-item').addEventListener('click', function(){ row.remove(); recalc(); });
                recalc();
            });

            // attach handlers for initial row
            itemsList.querySelectorAll('.item-qty, .item-price').forEach(el=>el.addEventListener('input', recalc));
            itemsList.querySelectorAll('.remove-item').forEach(btn=>btn.addEventListener('click', function(e){ e.target.closest('.item-row').remove(); recalc(); }));
            recalc();
        })();
    </script>
@endsection
