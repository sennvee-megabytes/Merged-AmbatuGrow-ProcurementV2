<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <style>
        @page {
            margin: 25px 30px;
            size: letter portrait;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #111827;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #1f5c3d;
            padding-bottom: 12px;
        }
        .logo-cell {
            width: 60px;
            vertical-align: top;
            padding-right: 12px;
        }
        .logo-cell img {
            width: 54px;
            height: 54px;
            border-radius: 50%;
        }
        .company-title {
            font-size: 22px;
            font-weight: 800;
            color: #1f5c3d;
            letter-spacing: -0.5px;
            margin-bottom: 3px;
        }
        .company-info {
            font-size: 10px;
            color: #4b5563;
            line-height: 1.35;
        }
        .company-info a {
            color: #1f5c3d;
            text-decoration: none;
            font-weight: bold;
        }
        .po-title {
            font-size: 22px;
            font-weight: 900;
            color: #1f5c3d;
            text-align: right;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .meta-table {
            border-collapse: collapse;
            margin-left: auto;
            border: 1px solid #1f5c3d;
        }
        .meta-table th {
            background-color: #1f5c3d;
            color: #ffffff;
            border: 1px solid #1f5c3d;
            padding: 4px 10px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .meta-table td {
            border: 1px solid #d1d5db;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            color: #111827;
        }

        .address-section {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .address-box {
            width: 48%;
            vertical-align: top;
        }
        .box-header {
            background-color: #1f5c3d;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 5px 10px;
            letter-spacing: 0.5px;
        }
        .box-content {
            border: 1px solid #1f5c3d;
            border-top: none;
            padding: 8px 10px;
            min-height: 85px;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.45;
            background-color: #fafdfb;
        }

        .shipping-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #1f5c3d;
        }
        .shipping-table th {
            background-color: #235c2b;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            padding: 5px 6px;
            border: 1px solid #235c2b;
        }
        .shipping-table td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: center;
            font-size: 10px;
            font-weight: 500;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
            border: 1px solid #1f5c3d;
        }
        .items-table th {
            background-color: #1f5c3d;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 6px 8px;
            border: 1px solid #1f5c3d;
            text-align: left;
        }
        .items-table th.center { text-align: center; }
        .items-table th.right { text-align: right; }
        .items-table td {
            border-left: 1px solid #d1d5db;
            border-right: 1px solid #d1d5db;
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .items-table tr.filler td {
            border-bottom: none;
            padding: 6px 8px;
        }
        .items-table td.right { text-align: right; }
        .items-table td.center { text-align: center; }

        .bottom-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .comments-box {
            width: 55%;
            vertical-align: top;
        }
        .comments-header {
            background-color: #e8f5e9;
            color: #1f5c3d;
            font-size: 9px;
            font-weight: bold;
            padding: 5px 8px;
            border: 1px solid #1f5c3d;
            text-transform: uppercase;
        }
        .comments-body {
            border: 1px solid #1f5c3d;
            border-top: none;
            padding: 8px;
            min-height: 70px;
            font-size: 10px;
            color: #374151;
            background-color: #fafdfb;
        }

        .totals-box {
            width: 42%;
            vertical-align: top;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-left: auto;
        }
        .totals-table td {
            padding: 4px 8px;
            font-size: 10px;
            border: 1px solid #d1d5db;
        }
        .totals-table td.label {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: left;
            width: 45%;
            color: #374151;
        }
        .totals-table td.val {
            text-align: right;
            width: 55%;
            font-weight: bold;
        }
        .totals-table tr.grand-total td {
            background-color: #1f5c3d;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            border-color: #1f5c3d;
        }

        .po-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @php
                    $logoPath = public_path('images/logo.jpg');
                @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Ambatugrow Logo">
                @endif
            </td>
            <td style="vertical-align: top;">
                <div class="company-title">AMBATUGROW</div>
                <div class="company-info">
                    Indang, Cavite, Philippines<br>
                    Phone: (000) 000-0000 | Fax: (111) 111-1111<br>
                    Website: <a href="http://www.ambatugrow.com">www.ambatugrow.com</a>
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="po-title">PURCHASE ORDER</div>
                <table class="meta-table">
                    <tr>
                        <th>DATE</th>
                        <th>PO #</th>
                    </tr>
                    <tr>
                        <td>{{ optional($po->issued_at)->format('M d, Y') ?? date('M d, Y') }}</td>
                        <td>{{ $po->po_number }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Vendor and Ship To Section -->
    <table class="address-section">
        <tr>
            <td class="address-box">
                <div class="box-header">VENDOR INFORMATION</div>
                <div class="box-content">
                    <strong>{{ $po->supplier->name ?? 'Vendor Company' }}</strong><br>
                    {{ $po->supplier->contact_person ?? 'Sales / Procurement Dept' }}<br>
                    {{ $po->supplier->address ?? 'Supplier Street Address' }}<br>
                    {{ $po->supplier->city ?? 'Cavite' }}, Philippines<br>
                    Phone: {{ $po->supplier->phone ?? '(000) 000-0000' }}<br>
                    Fax: {{ $po->supplier->fax ?? '(000) 000-0000' }}
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td class="address-box">
                <div class="box-header">SHIP TO</div>
                <div class="box-content">
                    <strong>{{ $po->requisition->requestor->name ?? 'Ambatugrow Officer' }}</strong><br>
                    Ambatugrow Main Depot<br>
                    Indang, Cavite, Philippines<br>
                    Phone: (000) 000-0000
                </div>
            </td>
        </tr>
    </table>

    <!-- Shipping Details Table -->
    <table class="shipping-table">
        <tr>
            <th style="width: 25%;">REQUISITIONER</th>
            <th style="width: 25%;">SHIP VIA</th>
            <th style="width: 25%;">F.O.B.</th>
            <th style="width: 25%;">SHIPPING TERMS</th>
        </tr>
        <tr>
            <td>{{ $po->requisition->requestor->name ?? 'Requisitioner' }}</td>
            <td>Standard Logistics</td>
            <td>Destination</td>
            <td>Prepaid</td>
        </tr>
    </table>

    <!-- Item Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 18%;">ITEM CODE</th>
                <th style="width: 38%;">ITEM NAME & DESCRIPTION</th>
                <th style="width: 10%;" class="center">QTY</th>
                <th style="width: 10%;" class="center">UOM</th>
                <th style="width: 12%;" class="right">UNIT PRICE</th>
                <th style="width: 12%;" class="right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $items = $po->items->count() > 0 ? $po->items : ($po->requisition ? $po->requisition->items : []);
                $subtotal = 0;
            @endphp
            @foreach($items as $item)
                @php
                    $qty = $item->quantity ?? $item->qty ?? 1;
                    $unit = $item->unit ?? $item->uom ?? 'Unit';
                    $price = $item->unit_price ?? 0;
                    $lineTotal = $item->line_total ?? ($qty * $price);
                    $subtotal += $lineTotal;
                    $sku = $item->sku ?? ('ITEM-' . sprintf('%05d', $item->id));
                @endphp
                <tr>
                    <td style="font-weight: bold; color: #1f5c3d;">{{ $sku }}</td>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td class="center" style="font-weight: bold;">{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</td>
                    <td class="center">{{ $unit }}</td>
                    <td class="right">&#8369;{{ number_format($price, 2) }}</td>
                    <td class="right" style="font-weight: bold;">&#8369;{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach

            {{-- Filler rows to match reference layout height --}}
            @for($i = count($items); $i < 6; $i++)
                <tr class="filler">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="center">-</td>
                    <td class="center">-</td>
                    <td class="right">-</td>
                    <td class="right">-</td>
                </tr>
            @endfor
        </tbody>
    </table>

    @php
        $tax = $po->requisition->tax_amount ?? round($subtotal * 0.12, 2);
        $shipping = 0.00;
        $other = 0.00;
        $grandTotal = $po->total > 0 ? $po->total : ($subtotal + $tax + $shipping + $other);
    @endphp

    <!-- Bottom Section: Comments & Totals -->
    <table class="bottom-section">
        <tr>
            <td class="comments-box">
                <div class="comments-header">Comments or Special Instructions</div>
                <div class="comments-body">
                    {{ $po->requisition->purpose ?? 'No additional instructions provided.' }}
                </div>
            </td>
            <td style="width: 3%;"></td>
            <td class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td class="label">SUBTOTAL</td>
                        <td class="val">&#8369;{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">TAX (12%)</td>
                        <td class="val">&#8369;{{ number_format($tax, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">SHIPPING</td>
                        <td class="val">{{ $shipping > 0 ? '&#8369;' . number_format($shipping, 2) : '-' }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td class="label" style="color:#ffffff;">GRAND TOTAL</td>
                        <td class="val" style="color:#ffffff;">&#8369;{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="po-footer">
        If you have any questions about this purchase order, please contact<br>
        <strong>Ambatugrow Procurement Department · Phone: (000) 000-0000 · Email: procurement@ambatugrow.com</strong>
    </div>

</body>
</html>
