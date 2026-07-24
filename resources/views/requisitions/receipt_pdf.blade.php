<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PR Receipt - {{ $requisition->code }}</title>
    <style>
        @page {
            margin: 25px 30px;
            size: letter portrait;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 1px solid #707070;
            padding-bottom: 10px;
        }
        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 4px;
        }
        .company-info {
            font-size: 10px;
            color: #4a4a4a;
            line-height: 1.35;
        }
        .receipt-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e5c3d;
            text-align: right;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .receipt-code {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            text-align: right;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #707070;
            background-color: #f9f9f9;
        }
        .meta-table td {
            padding: 6px 10px;
            font-size: 10px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #555;
            font-size: 9px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #707070;
        }
        .items-table th {
            background-color: #1e5c3d;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 5px 8px;
            border: 1px solid #1e5c3d;
            text-align: left;
        }
        .items-table th.center { text-align: center; }
        .items-table th.right { text-align: right; }
        .items-table td {
            border: 1px solid #e0e0e0;
            padding: 6px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .items-table td.center { text-align: center; }
        .items-table td.right { text-align: right; }

        .totals-table {
            width: 40%;
            border-collapse: collapse;
            margin-left: auto;
            margin-bottom: 20px;
        }
        .totals-table td {
            padding: 4px 8px;
            font-size: 10px;
            border: 1px solid #707070;
        }
        .totals-table td.label {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .totals-table td.val {
            text-align: right;
        }

        .workflow-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 1px solid #707070;
        }
        .workflow-table th {
            background-color: #f0f0f0;
            font-size: 9px;
            font-weight: bold;
            padding: 4px 8px;
            border: 1px solid #707070;
            text-align: left;
        }
        .workflow-table td {
            padding: 6px 8px;
            font-size: 10px;
            border: 1px solid #e0e0e0;
        }
        .status-badge {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }

        .receipt-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="company-title">Ambatugrow</div>
                <div class="company-info">
                    Indang, Cavite<br>
                    Phone: (000) 000-0000 | Fax: (111) 111-1111<br>
                    Website: www.ambatugrow.com
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="receipt-title">REQUISITION RECEIPT</div>
                <div class="receipt-code">{{ $requisition->code }}</div>
                <div style="font-size: 9px; color: #666; margin-top: 4px;">Status: {{ $requisition->statusLabel() }}</div>
            </td>
        </tr>
    </table>

    <!-- Requisition Metadata -->
    <table class="meta-table">
        <tr>
            <td style="width: 25%;">
                <span class="meta-label">Submission Date & Time</span>
                <strong>{{ $requisition->submitted_at?->format('M d, Y · h:i A') ?? $requisition->created_at->format('M d, Y · h:i A') }}</strong>
            </td>
            <td style="width: 25%;">
                <span class="meta-label">Requester Name</span>
                <strong>{{ $requisition->requestor->name }}</strong>
            </td>
            <td style="width: 25%;">
                <span class="meta-label">Department</span>
                <strong>{{ $requisition->department }}</strong>
            </td>
            <td style="width: 25%;">
                <span class="meta-label">Recommended Supplier</span>
                <strong>{{ $requisition->recommendedSupplier->name ?? 'None Recommended' }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="meta-label">Purpose of Request</span>
                {{ $requisition->purpose ?? 'N/A' }}
            </td>
            <td>
                <span class="meta-label">Date Needed</span>
                <strong>{{ optional($requisition->needed_by)->format('M d, Y') ?? 'N/A' }}</strong>
            </td>
            <td>
                <span class="meta-label">Priority</span>
                <strong>{{ $requisition->urgency }}</strong>
            </td>
        </tr>
    </table>

    <!-- Requested Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 18%;">ITEM CODE</th>
                <th style="width: 32%;">ITEM NAME</th>
                <th style="width: 20%;">DESCRIPTION</th>
                <th style="width: 8%;" class="center">QTY</th>
                <th style="width: 7%;" class="center">UNIT</th>
                <th style="width: 15%;" class="right">EST. UNIT COST</th>
                <th style="width: 15%;" class="right">EST. TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisition->items as $item)
                <tr>
                    <td>{{ $item->sku ?? ('ITEM-' . sprintf('%05d', $item->id)) }}</td>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td>{{ $item->justification ?? $item->name }}</td>
                    <td class="center">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}</td>
                    <td class="center">{{ $item->unit }}</td>
                    <td class="right">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">₱{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals-table">
        <tr>
            <td class="label">Estimated Subtotal</td>
            <td class="val">₱{{ number_format($requisition->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Estimated Tax</td>
            <td class="val">₱{{ number_format($requisition->tax_amount, 2) }}</td>
        </tr>
        <tr style="background-color: #1e5c3d; color: #ffffff; font-weight: bold;">
            <td style="color:#ffffff; font-weight:bold;">Estimated Grand Total</td>
            <td style="color:#ffffff; font-weight:bold;" class="val">₱{{ number_format($requisition->total, 2) }}</td>
        </tr>
    </table>

    <!-- Approval Workflow -->
    @php
        $steps = $requisition->approvalSteps;
        $approver1 = $steps->firstWhere('step_order', 1);
        $approver2 = $steps->firstWhere('step_order', 2);
        $approver3 = $steps->firstWhere('step_order', 3);
    @endphp
    <table class="workflow-table">
        <thead>
            <tr>
                <th style="width: 15%;">LEVEL</th>
                <th style="width: 35%;">APPROVER NAME</th>
                <th style="width: 30%;">ROLE</th>
                <th style="width: 20%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Level 1</td>
                <td><strong>Sarah Jenkins</strong></td>
                <td>Project Manager</td>
                <td><span class="status-badge">{{ strtoupper($approver1?->status ?? 'pending') }}</span></td>
            </tr>
            <tr>
                <td>Level 2</td>
                <td><strong>Michael Finn</strong></td>
                <td>Finance Manager</td>
                <td><span class="status-badge">{{ strtoupper($approver2?->status ?? 'pending') }}</span></td>
            </tr>
            <tr>
                <td>Level 3</td>
                <td><strong>Johny Papa</strong></td>
                <td>Director of Marketing / Head</td>
                <td><span class="status-badge">{{ strtoupper($approver3?->status ?? 'pending') }}</span></td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Notice -->
    <div class="receipt-footer">
        This receipt is proof of submission for your Purchase Requisition. It is <strong>NOT</strong> a Purchase Order.<br>
        If you have any questions, please contact <strong>procurement@ambatugrow.com</strong>
    </div>

</body>
</html>
