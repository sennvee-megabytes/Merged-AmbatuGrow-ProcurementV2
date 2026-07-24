<?php

namespace App\Http\Controllers;

use App\Models\DeliveryReceipt;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatchingController extends Controller
{
    private function getMockData()
    {
        return [
            [
                'id' => 'mock-1',
                'po_number' => 'PO-2024-00841',
                'po_date' => '14 Jun 2024',
                'supplier' => 'Savanna Grain Co.',
                'commodity' => 'White Maize',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Harare Central Depot',
                'grn_number' => 'GRN-2024-03291',
                'grn_date' => '18 Jun 2024',
                'invoice_number' => 'INV-SG-8821',
                'invoice_date' => '19 Jun 2024',
                'po_amount' => 284500.00,
                'invoice_amount' => 284500.00,
                'variance' => 0.0,
                'status' => 'Matched',
                'supplier_initials' => 'SA',
                'payment_approvable' => true,
                'discrepancies' => [],
                'matched_fields' => ['PO Number: PO-2024-00841', 'Supplier: Savanna Grain Co.', 'Quantities: 100/100 Accepted', 'Amounts Reconciled'],
                'received_by' => 'Central Depot Receiving',
                'received_at' => '2024-06-18 14:30:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-2',
                'po_number' => 'PO-2024-00856',
                'po_date' => '15 Jun 2024',
                'supplier' => 'Highveld Agri Traders',
                'commodity' => 'Soybeans',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 45',
                'warehouse' => 'Bulawayo Silo Complex',
                'grn_number' => 'GRN-2024-03304',
                'grn_date' => '20 Jun 2024',
                'invoice_number' => 'INV-HA-4412',
                'invoice_date' => '21 Jun 2024',
                'po_amount' => 520000.00,
                'invoice_amount' => 520000.00,
                'variance' => -52000.00,
                'status' => 'Partial Match',
                'supplier_initials' => 'HI',
                'payment_approvable' => false,
                'discrepancies' => [
                    'Quantity Shortage: Received value (₱468,000.00) is less than PO ordered value (₱520,000.00) by ₱52,000.00.',
                    'Delivery Issue: 100 bags rejected due to moisture damage upon receipt inspection.'
                ],
                'matched_fields' => ['PO Number: PO-2024-00856', 'Supplier: Highveld Agri Traders'],
                'received_by' => 'Bulawayo Receiving Bay',
                'received_at' => '2024-06-20 09:15:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-3',
                'po_number' => 'PO-2024-00872',
                'po_date' => '17 Jun 2024',
                'supplier' => 'Delta Farm Supplies',
                'commodity' => 'Wheat',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Gweru Storage Facility',
                'grn_number' => 'GRN-2024-03318',
                'grn_date' => '22 Jun 2024',
                'invoice_number' => null,
                'invoice_date' => null,
                'po_amount' => 193750.00,
                'invoice_amount' => 0.0,
                'variance' => 0.0,
                'status' => 'Pending Invoice',
                'supplier_initials' => 'DE',
                'payment_approvable' => false,
                'discrepancies' => ['Supplier Invoice not found. Matching cannot be completed until all required documents are available.'],
                'matched_fields' => ['PO Number: PO-2024-00872', 'Supplier: Delta Farm Supplies', 'Goods Received'],
                'received_by' => 'Harare Receiving Bay',
                'received_at' => '2024-06-22 11:00:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-4',
                'po_number' => 'PO-2024-00833',
                'po_date' => '18 Jun 2024',
                'supplier' => 'Zambezi Valley Farms',
                'commodity' => 'Cotton Seed',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 15',
                'warehouse' => 'Gweru Depot',
                'grn_number' => null,
                'grn_date' => null,
                'invoice_number' => 'INV-ZV-1109',
                'invoice_date' => '23 Jun 2024',
                'po_amount' => 347200.00,
                'invoice_amount' => 347200.00,
                'variance' => 0.0,
                'status' => 'Pending Receipt',
                'supplier_initials' => 'ZA',
                'payment_approvable' => false,
                'discrepancies' => ['Delivery Receipt (GRN) not found. Matching cannot be completed until all required documents are available.'],
                'matched_fields' => ['PO Number: PO-2024-00833', 'Supplier: Zambezi Valley Farms', 'Invoice Received'],
                'received_by' => null,
                'received_at' => null,
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-5',
                'po_number' => 'PO-2024-00881',
                'po_date' => '19 Jun 2024',
                'supplier' => 'Pioneer Seeds Ltd.',
                'commodity' => 'Sunflower',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Mutare Logistics Hub',
                'grn_number' => 'GRN-2024-03337',
                'grn_date' => '24 Jun 2024',
                'invoice_number' => 'INV-PS-7734',
                'invoice_date' => '24 Jun 2024',
                'po_amount' => 412000.00,
                'invoice_amount' => 432500.00,
                'variance' => 20500.00,
                'status' => 'Mismatch',
                'supplier_initials' => 'PI',
                'payment_approvable' => false,
                'discrepancies' => [
                    'Total Amount Over-Invoiced: Invoice Amount (₱432,500.00) exceeds PO Value (₱412,000.00) by ₱20,500.00.',
                    'Unit Price Mismatch: Invoice unit price differs from approved PO price.'
                ],
                'matched_fields' => ['PO Number: PO-2024-00881', 'Supplier: Pioneer Seeds Ltd.'],
                'received_by' => 'Mutare Logistics Team',
                'received_at' => '2024-06-24 16:20:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-6',
                'po_number' => 'PO-2024-00802',
                'po_date' => '20 Jun 2024',
                'supplier' => 'Savanna Grain Co.',
                'commodity' => 'Yellow Maize',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Harare Central Depot',
                'grn_number' => 'GRN-2024-03261',
                'grn_date' => '25 Jun 2024',
                'invoice_number' => 'INV-SG-8839',
                'invoice_date' => '25 Jun 2024',
                'po_amount' => 170400.00,
                'invoice_amount' => 170400.00,
                'variance' => 0.0,
                'status' => 'Approved for Payment',
                'supplier_initials' => 'SA',
                'payment_approvable' => true,
                'discrepancies' => [],
                'matched_fields' => ['PO Number: PO-2024-00802', 'Supplier: Savanna Grain Co.', 'Amounts Reconciled'],
                'received_by' => 'Harare Receiving Bay',
                'received_at' => '2024-06-25 10:00:00',
                'approved_by' => 'System Admin',
                'approved_at' => '2024-06-26 09:30:00',
            ],
            [
                'id' => 'mock-7',
                'po_number' => 'PO-2024-00895',
                'po_date' => '22 Jun 2024',
                'supplier' => 'Apex Agri Machinery Services',
                'commodity' => 'Tractor Fleet Servicing',
                'receipt_type' => 'services',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Harare Central Workshop',
                'grn_number' => 'SRN-2024-0089',
                'grn_date' => '26 Jun 2024',
                'invoice_number' => 'INV-AA-5521',
                'invoice_date' => '27 Jun 2024',
                'po_amount' => 88500.00,
                'invoice_amount' => 88500.00,
                'variance' => 0.0,
                'status' => 'Matched',
                'supplier_initials' => 'AP',
                'payment_approvable' => true,
                'discrepancies' => [],
                'matched_fields' => ['PO Number: PO-2024-00895', 'Supplier: Apex Agri Machinery', 'Service Entry Verified', 'Amounts Reconciled'],
                'received_by' => 'Fleet Maintenance Supervisor',
                'received_at' => '2024-06-26 15:00:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-8',
                'po_number' => 'PO-2024-00908',
                'po_date' => '24 Jun 2024',
                'supplier' => 'Biolab Soil Testing Ltd.',
                'commodity' => 'Soil Analysis & Audit',
                'receipt_type' => 'services',
                'payment_terms' => 'Net 15',
                'warehouse' => 'Masvingo Regional Field Office',
                'grn_number' => 'SRN-2024-0102',
                'grn_date' => '28 Jun 2024',
                'invoice_number' => 'INV-BS-3310',
                'invoice_date' => '29 Jun 2024',
                'po_amount' => 125000.00,
                'invoice_amount' => 125000.00,
                'variance' => 0.0,
                'status' => 'Mismatch',
                'supplier_initials' => 'BI',
                'payment_approvable' => false,
                'discrepancies' => [
                    'Service Delivery Issue: Irrigation audit service incomplete (only 3 of 5 field sites surveyed).',
                    'Service Entry Discrepancy: Sign-off sheet pending agronomist verification.'
                ],
                'matched_fields' => ['PO Number: PO-2024-00908', 'Supplier: Biolab Soil Testing Ltd.'],
                'received_by' => 'Regional Agronomist',
                'received_at' => '2024-06-28 11:30:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-9',
                'po_number' => 'PO-2024-00912',
                'po_date' => '26 Jun 2024',
                'supplier' => 'Green Harvest',
                'commodity' => 'Fertilizers & Nutrients',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Mutare Logistics Hub',
                'grn_number' => 'GRN-2024-03412',
                'grn_date' => '29 Jun 2024',
                'invoice_number' => 'INV-GH-9012',
                'invoice_date' => '30 Jun 2024',
                'po_amount' => 317232.00,
                'invoice_amount' => 317232.00,
                'variance' => 0.0,
                'status' => 'Matched',
                'supplier_initials' => 'GR',
                'payment_approvable' => true,
                'discrepancies' => [],
                'matched_fields' => ['PO Number: PO-2024-00912', 'Supplier: Green Harvest', 'Amounts Reconciled'],
                'received_by' => 'Mutare Logistics Team',
                'received_at' => '2024-06-29 10:00:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-10',
                'po_number' => 'PO-2024-00925',
                'po_date' => '28 Jun 2024',
                'supplier' => 'ABC Farms',
                'commodity' => 'Hybrid Crop Seeds',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Harare Central Depot',
                'grn_number' => 'GRN-2024-03425',
                'grn_date' => '01 Jul 2024',
                'invoice_number' => 'INV-ABC-1025',
                'invoice_date' => '02 Jul 2024',
                'po_amount' => 501166.00,
                'invoice_amount' => 501166.00,
                'variance' => -50116.60,
                'status' => 'Partial Match',
                'supplier_initials' => 'AB',
                'payment_approvable' => false,
                'discrepancies' => ['Quantity Shortage: Accepted quantity is less than ordered PO quantity by 10%.'],
                'matched_fields' => ['PO Number: PO-2024-00925', 'Supplier: ABC Farms'],
                'received_by' => 'Central Depot Receiving',
                'received_at' => '2024-07-01 14:00:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-11',
                'po_number' => 'PO-2024-00938',
                'po_date' => '30 Jun 2024',
                'supplier' => 'Fresh Mango Co.',
                'commodity' => 'Storage Crates',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 15',
                'warehouse' => 'Bulawayo Silo Complex',
                'grn_number' => 'GRN-2024-03438',
                'grn_date' => '03 Jul 2024',
                'invoice_number' => null,
                'invoice_date' => null,
                'po_amount' => 116484.48,
                'invoice_amount' => 0.0,
                'variance' => 0.0,
                'status' => 'Pending Invoice',
                'supplier_initials' => 'FR',
                'payment_approvable' => false,
                'discrepancies' => ['Supplier Invoice not found. Matching cannot be completed until all required documents are available.'],
                'matched_fields' => ['PO Number: PO-2024-00938', 'Supplier: Fresh Mango Co.'],
                'received_by' => 'Bulawayo Receiving Bay',
                'received_at' => '2024-07-03 09:00:00',
                'approved_by' => null,
                'approved_at' => null,
            ],
            [
                'id' => 'mock-12',
                'po_number' => 'PO-2024-00944',
                'po_date' => '02 Jul 2024',
                'supplier' => 'Green Harvest',
                'commodity' => 'Organic Pesticides',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Harare Central Depot',
                'grn_number' => 'GRN-2024-03444',
                'grn_date' => '04 Jul 2024',
                'invoice_number' => 'INV-GH-9044',
                'invoice_date' => '05 Jul 2024',
                'po_amount' => 94168.48,
                'invoice_amount' => 94168.48,
                'variance' => 0.0,
                'status' => 'Approved for Payment',
                'supplier_initials' => 'GR',
                'payment_approvable' => true,
                'discrepancies' => [],
                'matched_fields' => ['PO Number: PO-2024-00944', 'Supplier: Green Harvest'],
                'received_by' => 'Harare Receiving Bay',
                'received_at' => '2024-07-04 11:00:00',
                'approved_by' => 'Finance Director',
                'approved_at' => '2024-07-05 16:00:00',
            ],
        ];
    }

    private function getDatabaseRecords()
    {
        $dbRecords = [];

        $drs = DeliveryReceipt::with(['purchaseOrder.supplier', 'purchaseOrder.items'])->get();
        foreach ($drs as $dr) {
            $po = $dr->purchaseOrder;
            if (!$po) continue;

            $inv = Invoice::where('purchase_order_id', $po->id)->first();
            $poAmount = (float) $po->total;
            $invAmount = $inv ? (float) $inv->amount : 0.0;

            $itemsData = is_array($dr->items) ? $dr->items : json_decode($dr->items ?? '[]', true);
            $lines = $itemsData['lines'] ?? [];
            $receiptType = $itemsData['receipt_type'] ?? 'goods';
            $grnAcceptedTotal = $itemsData['accepted_total'] ?? 0;
            
            if (!$grnAcceptedTotal && !empty($lines)) {
                foreach ($lines as $l) {
                    $grnAcceptedTotal += ((float)($l['qty_accepted'] ?? 0) * (float)($l['unit_price'] ?? 0));
                }
            }

            $discrepancies = [];
            $matchedFields = ['PO Number: ' . $po->po_number];
            
            $supplierName = $po->supplier ? ($po->supplier->supplier_name ?? $po->supplier->name ?? 'Vendor') : 'Vendor';
            $matchedFields[] = 'Supplier: ' . $supplierName;

            // Check line item delivery or service issues
            if (!empty($lines)) {
                foreach ($lines as $l) {
                    $cond = $l['condition'] ?? 'OK';
                    if ($cond !== 'OK') {
                        $discrepancies[] = ($receiptType === 'services' ? 'Service Issue' : 'Delivery Issue') . " ({$l['name']}): Status/Condition marked as {$cond}.";
                    }
                }
            }

            if ($inv) {
                $variance = $invAmount - $poAmount;
                if (abs($variance) < 0.01 && abs($grnAcceptedTotal - $poAmount) < 0.01 && empty($discrepancies)) {
                    $status = ($po->status === 'approved' || $po->status === 'received') ? 'Matched' : 'Approved for Payment';
                    $paymentApprovable = true;
                    $matchedFields[] = 'Amounts Fully Reconciled';
                    $matchedFields[] = 'Quantities & Prices Matched';
                } else if ($variance > 0) {
                    $status = 'Mismatch';
                    $paymentApprovable = false;
                    $discrepancies[] = 'Total Amount Over-Invoiced: Invoice Amount (₱' . number_format($invAmount, 2) . ') exceeds PO Value (₱' . number_format($poAmount, 2) . ') by ₱' . number_format($variance, 2) . '.';
                } else {
                    $status = 'Partial Match';
                    $paymentApprovable = false;
                    $discrepancies[] = 'Quantity Shortage / Partial Match: Delivered total (₱' . number_format($grnAcceptedTotal, 2) . ') differs from PO Value (₱' . number_format($poAmount, 2) . ').';
                }
            } else {
                $status = 'Pending Invoice';
                $paymentApprovable = false;
                $discrepancies[] = 'Supplier Invoice not found. Matching cannot be completed until all required documents are available.';
            }

            if (($po && $po->status === 'cancelled') || (isset($itemsData['status']) && $itemsData['status'] === 'Cancelled')) {
                $status = 'Cancelled';
                $paymentApprovable = false;
            }

            $words = explode(' ', $supplierName);
            $initials = strtoupper(substr($words[0] ?? 'V', 0, 1) . substr($words[1] ?? '', 0, 1));

            $commodityLabel = !empty($lines) ? ($lines[0]['name'] ?? 'Procured Items') : ($receiptType === 'services' ? 'Service Receipt' : 'Goods Receipt');

            $dbRecords[] = [
                'id' => $dr->id,
                'po_number' => $po->po_number,
                'po_id' => $po->id,
                'po_date' => $po->created_at ? $po->created_at->format('d M Y') : now()->format('d M Y'),
                'supplier' => $supplierName,
                'commodity' => $commodityLabel,
                'receipt_type' => $receiptType,
                'payment_terms' => 'Net 30',
                'warehouse' => $itemsData['location'] ?? ($receiptType === 'services' ? 'Service Site' : 'Harare Central Depot'),
                'grn_number' => $dr->dr_number,
                'grn_date' => $dr->received_at ? Carbon::parse($dr->received_at)->format('d M Y') : now()->format('d M Y'),
                'invoice_number' => $inv ? $inv->invoice_number : null,
                'invoice_date' => $inv && $inv->received_at ? Carbon::parse($inv->received_at)->format('d M Y') : null,
                'po_amount' => $poAmount,
                'invoice_amount' => $invAmount,
                'variance' => $inv ? ($invAmount - $poAmount) : 0.0,
                'status' => $status,
                'cancellation_reason' => $itemsData['cancellation_reason'] ?? null,
                'cancelled_by' => $itemsData['cancelled_by'] ?? null,
                'cancelled_at' => $itemsData['cancelled_at'] ?? null,
                'supplier_initials' => $initials ?: 'GR',
                'payment_approvable' => $paymentApprovable,
                'discrepancies' => array_values(array_unique($discrepancies)),
                'matched_fields' => $matchedFields,
                'received_by' => $itemsData['received_by'] ?? 'Receiving Agent',
                'received_at' => $dr->received_at ? Carbon::parse($dr->received_at)->format('Y-m-d H:i:s') : null,
                'approved_by' => $itemsData['approved_by'] ?? null,
                'approved_at' => $itemsData['approved_at'] ?? null,
            ];
        }

        // Standalone Invoices linked to POs
        $invoicesWithoutDr = Invoice::with(['purchaseOrder.supplier'])
            ->whereNotNull('purchase_order_id')
            ->whereNotIn('purchase_order_id', array_column($dbRecords, 'po_id'))
            ->get();

        foreach ($invoicesWithoutDr as $inv) {
            $po = $inv->purchaseOrder;
            if (!$po) continue;

            $supplierName = $po->supplier ? ($po->supplier->supplier_name ?? $po->supplier->name ?? 'Vendor') : 'Vendor';
            $words = explode(' ', $supplierName);
            $initials = strtoupper(substr($words[0] ?? 'V', 0, 1) . substr($words[1] ?? '', 0, 1));

            $dbRecords[] = [
                'id' => 'inv-' . $inv->id,
                'po_number' => $po->po_number,
                'po_id' => $po->id,
                'po_date' => $po->created_at ? $po->created_at->format('d M Y') : now()->format('d M Y'),
                'supplier' => $supplierName,
                'commodity' => 'Vendor Invoice',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Harare Central Depot',
                'grn_number' => null,
                'grn_date' => null,
                'invoice_number' => $inv->invoice_number,
                'invoice_date' => $inv->received_at ? Carbon::parse($inv->received_at)->format('d M Y') : now()->format('d M Y'),
                'po_amount' => (float)$po->total,
                'invoice_amount' => (float)$inv->amount,
                'variance' => (float)($inv->amount - $po->total),
                'status' => 'Pending Receipt',
                'supplier_initials' => $initials ?: 'IN',
                'payment_approvable' => false,
                'discrepancies' => ['Delivery Receipt (GRN / SRN) not found. Matching cannot be completed until all required documents are available.'],
                'matched_fields' => ['PO Number: ' . $po->po_number, 'Supplier: ' . $supplierName],
                'received_by' => null,
                'received_at' => null,
                'approved_by' => null,
                'approved_at' => null,
            ];
        }

        // Standalone POs without DR or Invoice yet
        $existingPoIds = array_column($dbRecords, 'po_id');
        $posWithoutDocs = PurchaseOrder::with(['supplier'])
            ->whereNotIn('id', array_filter($existingPoIds))
            ->get();

        foreach ($posWithoutDocs as $po) {
            $supplierName = $po->supplier ? ($po->supplier->supplier_name ?? $po->supplier->name ?? 'Vendor') : 'Vendor';
            $words = explode(' ', $supplierName);
            $initials = strtoupper(substr($words[0] ?? 'V', 0, 1) . substr($words[1] ?? '', 0, 1));

            $dbRecords[] = [
                'id' => 'po-' . $po->id,
                'po_number' => $po->po_number,
                'po_id' => $po->id,
                'po_date' => $po->created_at ? $po->created_at->format('d M Y') : now()->format('d M Y'),
                'supplier' => $supplierName,
                'commodity' => 'Purchase Order',
                'receipt_type' => 'goods',
                'payment_terms' => 'Net 30',
                'warehouse' => 'Harare Central Depot',
                'grn_number' => null,
                'grn_date' => null,
                'invoice_number' => null,
                'invoice_date' => null,
                'po_amount' => (float)$po->total,
                'invoice_amount' => 0.0,
                'variance' => 0.0,
                'status' => 'Awaiting Delivery',
                'supplier_initials' => $initials ?: 'PO',
                'payment_approvable' => false,
                'discrepancies' => [
                    'Delivery Receipt (GRN / SRN) not found.',
                    'Supplier Invoice not found.',
                    'Matching cannot be completed until all required documents are available.'
                ],
                'matched_fields' => ['PO Number: ' . $po->po_number, 'Supplier: ' . $supplierName],
                'received_by' => null,
                'received_at' => null,
                'approved_by' => null,
                'approved_at' => null,
            ];
        }

        return $dbRecords;
    }

    private function getAvailablePos()
    {
        $pos = PurchaseOrder::with(['supplier', 'items'])->orderBy('created_at', 'desc')->get();
        $list = [];

        foreach ($pos as $po) {
        $supplierName = $po->supplier ? ($po->supplier->supplier_name ?? $po->supplier->name ?? 'Supplier') : 'Supplier';
            $list[] = [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'supplier' => $supplierName,
                'status' => $po->status ?? 'Awaiting Delivery',
                'total' => (float)$po->total,
                'po_date' => $po->created_at ? $po->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'warehouse' => 'Harare Central Depot',
                'payment_terms' => 'Net 30',
                'items' => $po->items->map(function ($it) {
                    return [
                        'id' => $it->id,
                        'name' => $it->name,
                        'qty' => (float)$it->quantity,
                        'unit_price' => (float)$it->unit_price,
                        'line_total' => (float)$it->line_total,
                    ];
                })->toArray(),
            ];
        }

        $samplePos = [
            [
                'id' => 1024,
                'po_number' => 'PO-2024-00841',
                'supplier' => 'Savanna Grain Co.',
                'total' => 284500.00,
                'status' => 'Pending Receipt',
                'po_date' => '2024-06-14',
                'warehouse' => 'Harare Central Depot',
                'payment_terms' => 'Net 30',
                'items' => [
                    ['id' => 1, 'name' => 'White Maize (Grade A Grain)', 'qty' => 1000, 'unit_price' => 284.50, 'line_total' => 284500.00]
                ]
            ],
            [
                'id' => 1025,
                'po_number' => 'PO-2024-00856',
                'supplier' => 'Highveld Agri Traders',
                'total' => 520000.00,
                'status' => 'Pending Receipt',
                'po_date' => '2024-06-15',
                'warehouse' => 'Bulawayo Silo Complex',
                'payment_terms' => 'Net 45',
                'items' => [
                    ['id' => 1, 'name' => 'Soybean Seed Bags (50kg)', 'qty' => 2000, 'unit_price' => 260.00, 'line_total' => 520000.00]
                ]
            ],
            [
                'id' => 1026,
                'po_number' => 'PO-2024-00872',
                'supplier' => 'Delta Farm Supplies',
                'total' => 193750.00,
                'status' => 'Awaiting Delivery',
                'po_date' => '2024-06-17',
                'warehouse' => 'Gweru Storage Facility',
                'payment_terms' => 'Net 30',
                'items' => [
                    ['id' => 1, 'name' => 'Wheat Seed Bulk Shipments', 'qty' => 775, 'unit_price' => 250.00, 'line_total' => 193750.00]
                ]
            ],
            [
                'id' => 1027,
                'po_number' => 'PO-2024-00881',
                'supplier' => 'Pioneer Seeds Ltd.',
                'total' => 412000.00,
                'status' => 'Pending Receipt',
                'po_date' => '2024-06-19',
                'warehouse' => 'Mutare Logistics Hub',
                'payment_terms' => 'Net 30',
                'items' => [
                    ['id' => 1, 'name' => 'Sunflower Seed Hybrid Hybrid-7', 'qty' => 1030, 'unit_price' => 400.00, 'line_total' => 412000.00]
                ]
            ],
            [
                'id' => 1028,
                'po_number' => 'PO-2024-00895',
                'supplier' => 'Apex Agri Machinery Services',
                'total' => 88500.00,
                'status' => 'Awaiting Delivery',
                'po_date' => '2024-06-22',
                'warehouse' => 'Harare Central Workshop',
                'payment_terms' => 'Net 30',
                'items' => [
                    ['id' => 1, 'name' => 'Tractor Engine Overhaul & Servicing', 'qty' => 5, 'unit_price' => 17700.00, 'line_total' => 88500.00]
                ]
            ],
        ];

        foreach ($samplePos as $sample) {
            if (!collect($list)->pluck('po_number')->contains($sample['po_number'])) {
                $list[] = $sample;
            }
        }

        return $list;
    }

    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'All');
        $searchQuery = $request->input('search');
        $supplierFilter = $request->input('supplier');
        $sortBy = $request->input('sort_by', 'date_desc');

        $warehouseFilter = $request->input('warehouse');
        $commodityFilter = $request->input('commodity');
        $varianceFilter = $request->input('variance_type');
        $minAmount = $request->input('min_amount');
        $maxAmount = $request->input('max_amount');

        $dbRecords = $this->getDatabaseRecords();
        $mockRecords = $this->getMockData();

        $allData = array_merge($dbRecords, $mockRecords);

        // Fetch DB & Session Cancelled Records
        $cancelledPosMap = PurchaseOrder::where('status', 'cancelled')->pluck('status', 'po_number')->toArray();
        $cancelledDrs = DeliveryReceipt::all();
        $sessionCancelled = session('cancelled_transactions', []);

        $cancellationInfoMap = [];
        foreach ($cancelledDrs as $cdr) {
            $cItems = is_array($cdr->items) ? $cdr->items : json_decode($cdr->items ?? '[]', true);
            if (isset($cItems['status']) && $cItems['status'] === 'Cancelled') {
                $cPoNum = $cdr->purchaseOrder ? $cdr->purchaseOrder->po_number : null;
                if ($cPoNum) $cancellationInfoMap[$cPoNum] = $cItems;
                if ($cdr->dr_number) $cancellationInfoMap[$cdr->dr_number] = $cItems;
            }
        }

        foreach ($allData as &$item) {
            $pNum = $item['po_number'] ?? '';
            $drNum = $item['grn_number'] ?? '';
            $itemId = (string)($item['id'] ?? '');

            if (isset($cancelledPosMap[$pNum]) || isset($cancellationInfoMap[$pNum]) || ($drNum && isset($cancellationInfoMap[$drNum])) || isset($sessionCancelled[$pNum]) || isset($sessionCancelled[$itemId])) {
                $cData = $cancellationInfoMap[$pNum] ?? ($drNum ? ($cancellationInfoMap[$drNum] ?? []) : ($sessionCancelled[$pNum] ?? ($sessionCancelled[$itemId] ?? [])));
                $item['status'] = 'Cancelled';
                $item['payment_approvable'] = false;
                $item['cancellation_reason'] = $cData['cancellation_reason'] ?? 'Cancelled transaction';
                $item['cancelled_by'] = $cData['cancelled_by'] ?? 'Procurement Officer';
                $item['cancelled_at'] = $cData['cancelled_at'] ?? now()->format('Y-m-d H:i:s');
            }
        }
        unset($item);

        $filteredData = $allData;

        if ($statusFilter && $statusFilter !== 'All') {
            $filteredData = array_filter($filteredData, function ($item) use ($statusFilter) {
                $st = strtolower($item['status']);
                $targetSt = strtolower($statusFilter);
                if ($targetSt === 'mismatch' || $targetSt === 'mismatch detected') {
                    return $st === 'mismatch' || $st === 'mismatch detected';
                }
                return $st === $targetSt;
            });
        }

        if ($supplierFilter && $supplierFilter !== 'All Suppliers') {
            $filteredData = array_filter($filteredData, function ($item) use ($supplierFilter) {
                return $item['supplier'] === $supplierFilter;
            });
        }

        if ($searchQuery) {
            $searchQuery = strtolower(trim($searchQuery));
            $filteredData = array_filter($filteredData, function ($item) use ($searchQuery) {
                return str_contains(strtolower($item['po_number']), $searchQuery) ||
                       str_contains(strtolower($item['supplier']), $searchQuery) ||
                       str_contains(strtolower($item['commodity']), $searchQuery) ||
                       str_contains(strtolower($item['grn_number'] ?? ''), $searchQuery) ||
                       str_contains(strtolower($item['invoice_number'] ?? ''), $searchQuery);
            });
        }

        if ($warehouseFilter && $warehouseFilter !== 'All Warehouses') {
            $filteredData = array_filter($filteredData, function ($item) use ($warehouseFilter) {
                return strtolower($item['warehouse'] ?? '') === strtolower($warehouseFilter);
            });
        }

        if ($commodityFilter) {
            $commodityFilter = strtolower($commodityFilter);
            $filteredData = array_filter($filteredData, function ($item) use ($commodityFilter) {
                return str_contains(strtolower($item['commodity'] ?? ''), $commodityFilter);
            });
        }

        if ($varianceFilter) {
            if ($varianceFilter === 'has_variance') {
                $filteredData = array_filter($filteredData, fn($item) => abs($item['variance']) > 0);
            } else if ($varianceFilter === 'no_variance') {
                $filteredData = array_filter($filteredData, fn($item) => abs($item['variance']) == 0);
            }
        }

        if ($minAmount !== null && $minAmount !== '') {
            $filteredData = array_filter($filteredData, fn($item) => $item['po_amount'] >= (float)$minAmount);
        }
        if ($maxAmount !== null && $maxAmount !== '') {
            $filteredData = array_filter($filteredData, fn($item) => $item['po_amount'] <= (float)$maxAmount);
        }

        usort($filteredData, function ($a, $b) use ($sortBy) {
            switch ($sortBy) {
                case 'date_asc':
                    return strtotime($a['po_date']) <=> strtotime($b['po_date']);
                case 'po_asc':
                    return strcmp($a['po_number'], $b['po_number']);
                case 'po_desc':
                    return strcmp($b['po_number'], $a['po_number']);
                case 'amount_desc':
                    return $b['po_amount'] <=> $a['po_amount'];
                case 'amount_asc':
                    return $a['po_amount'] <=> $b['po_amount'];
                case 'variance_desc':
                    return abs($b['variance']) <=> abs($a['variance']);
                case 'status':
                    return strcmp($a['status'], $b['status']);
                case 'date_desc':
                default:
                    return strtotime($b['po_date']) <=> strtotime($a['po_date']);
            }
        });

        $suppliersList = array_unique(array_column($allData, 'supplier'));
        sort($suppliersList);

        $selectedPo = $request->input('selected_po');
        $selectedRecord = null;
        if ($selectedPo) {
            foreach ($allData as $item) {
                $itemKey = $item['po_number'] . '-' . str_replace(' ', '', $item['supplier']);
                if ($itemKey === $selectedPo || $item['po_number'] === $selectedPo) {
                    $selectedRecord = $item;
                    break;
                }
            }
        }
        
        if (!$selectedRecord && !empty($filteredData)) {
            $selectedRecord = reset($filteredData);
        }

        $availablePos = $this->getAvailablePos();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'records' => array_values($filteredData),
                'count' => count($filteredData),
                'selected' => $selectedRecord,
                'availablePos' => $availablePos,
            ]);
        }

        return view('matching.dashboard', [
            'records' => array_values($filteredData),
            'allRecords' => $allData,
            'suppliers' => $suppliersList,
            'selectedRecord' => $selectedRecord,
            'currentStatus' => $statusFilter,
            'currentSearch' => $searchQuery,
            'currentSupplier' => $supplierFilter,
            'currentSort' => $sortBy,
            'availablePos' => $availablePos,
        ]);
    }

    public function getPoItems($purchaseOrder)
    {
        $po = PurchaseOrder::with(['items', 'supplier'])->find($purchaseOrder);
        if (!$po) {
            $po = PurchaseOrder::with(['items', 'supplier'])->where('po_number', $purchaseOrder)->first();
        }

        if (!$po) {
            $available = collect($this->getAvailablePos())->firstWhere('po_number', $purchaseOrder);
            if ($available) {
                return response()->json([
                    'id' => $available['id'],
                    'po_number' => $available['po_number'],
                    'supplier' => $available['supplier'],
                    'total' => $available['total'],
                    'items' => $available['items'],
                ]);
            }
            return response()->json(['error' => 'Purchase Order not found'], 404);
        }

        $items = $po->items->map(function ($it) {
            return [
                'id' => $it->id,
                'name' => $it->name,
                'qty' => (float)$it->quantity,
                'unit_price' => (float)$it->unit_price,
                'line_total' => (float)$it->line_total,
            ];
        });

        $supplierName = $po->supplier ? ($po->supplier->supplier_name ?? $po->supplier->name ?? 'Supplier') : 'Supplier';

        return response()->json([
            'id' => $po->id,
            'po_number' => $po->po_number,
            'supplier' => $supplierName,
            'total' => (float)$po->total,
            'items' => $items,
        ]);
    }

    // Record Receipt of Goods or Services & Run 3-Way Match
    public function storeGrn(Request $request)
    {
        $validated = $request->validate([
            'receipt_type' => 'nullable|string', // 'goods' or 'services'
            'po_number' => 'required|string',
            'grn_number' => 'required|string',
            'received_at' => 'required',
            'receiving_location' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'invoice_amount' => 'nullable|numeric',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'matching_notes' => 'nullable|string',
            'lines' => 'nullable|array',
        ]);

        $receiptType = $validated['receipt_type'] ?? 'goods';

        // Prevent duplicate GRN / SRN number
        if (DeliveryReceipt::where('dr_number', $validated['grn_number'])->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate Receipt Record: Receipt number ' . $validated['grn_number'] . ' has already been recorded.',
                ], 422);
            }
        }

        $po = PurchaseOrder::where('po_number', $validated['po_number'])->first();
        if (!$po) {
            $supplier = Supplier::first();
            $po = PurchaseOrder::create([
                'po_number' => $validated['po_number'],
                'supplier_id' => $supplier ? $supplier->id : 1,
                'status' => 'received',
                'total' => 0,
                'issued_at' => now(),
            ]);
        }

        $lines = $validated['lines'] ?? [];
        $acceptedTotal = 0;
        $discrepancies = [];

        foreach ($lines as $line) {
            $qtyRec = (float)($line['qty_received'] ?? 0);
            $qtyAcc = (float)($line['qty_accepted'] ?? $qtyRec);
            $price = (float)($line['unit_price'] ?? 0);
            $acceptedTotal += ($qtyAcc * $price);
            $condition = $line['condition'] ?? 'OK';

            if ($qtyAcc < $qtyRec) {
                $discrepancies[] = "Item {$line['name']}: Accepted {$qtyAcc} of {$qtyRec} " . ($receiptType === 'services' ? 'hours/units completed' : 'received') . " ({$condition}).";
            } else if ($condition !== 'OK') {
                $discrepancies[] = "Item {$line['name']}: Marked with status/condition '{$condition}'.";
            }
        }

        $receivedBy = Auth::check() ? Auth::user()->name : ($receiptType === 'services' ? 'Service Inspector' : 'Receiving Agent');

        $dr = DeliveryReceipt::create([
            'dr_number' => $validated['grn_number'],
            'purchase_order_id' => $po->id,
            'received_at' => Carbon::parse($validated['received_at']),
            'items' => [
                'receipt_type' => $receiptType,
                'location' => $validated['receiving_location'] ?? ($receiptType === 'services' ? 'Service Site' : 'Harare Central Depot'),
                'notes' => $validated['matching_notes'] ?? '',
                'lines' => $lines,
                'accepted_total' => $acceptedTotal,
                'received_by' => $receivedBy,
            ],
        ]);

        $inv = null;
        if (!empty($validated['invoice_number'])) {
            $invAmount = (float)($validated['invoice_amount'] ?? $acceptedTotal);
            $inv = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'supplier_id' => $po->supplier_id,
                'purchase_order_id' => $po->id,
                'amount' => $invAmount,
                'received_at' => !empty($validated['invoice_date']) ? Carbon::parse($validated['invoice_date']) : now(),
            ]);
        }

        $poTotal = (float)$po->total;
        if ($poTotal == 0 && $acceptedTotal > 0) {
            $poTotal = $acceptedTotal;
            $po->total = $poTotal;
            $po->save();
        }

        $variance = 0;
        if ($inv) {
            $variance = (float)$inv->amount - $poTotal;
            if (abs($variance) < 0.01 && empty($discrepancies)) {
                $status = 'Matched';
                $po->status = 'received';
                $po->save();
            } else if ($variance > 0) {
                $status = 'Mismatch';
                $discrepancies[] = 'Total Amount Over-Invoiced: Invoice Amount (₱' . number_format($inv->amount, 2) . ') exceeds PO Value (₱' . number_format($poTotal, 2) . ') by ₱' . number_format($variance, 2) . '.';
            } else {
                $status = 'Partial Match';
                $discrepancies[] = 'Quantity Shortage / Partial Match: Invoice Amount (₱' . number_format($inv->amount, 2) . ') differs from PO Value (₱' . number_format($poTotal, 2) . ').';
            }
        } else {
            $status = 'Pending Invoice';
            $discrepancies[] = 'Supplier Invoice not found. Matching cannot be completed until all required documents are available.';
        }

        $receiptLabel = $receiptType === 'services' ? 'Service Entry Sheet' : 'Goods Receipt Note';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$receiptLabel} recorded and 3-way matched successfully!",
                'grn_number' => $dr->dr_number,
                'status' => $status,
                'variance' => $variance,
                'discrepancies' => array_values(array_unique($discrepancies)),
                'received_by' => $receivedBy,
            ]);
        }

        return redirect()->route('matching.index')->with('status', "{$receiptLabel} recorded successfully! Status: {$status}");
    }

    // Run 3-Way Matching Action & Flag Mismatches
    public function runThreeWayMatching(Request $request)
    {
        $poNumber = $request->input('po_number');
        if (!$poNumber) {
            return response()->json(['success' => false, 'message' => 'Purchase Order number is required.'], 422);
        }

        $allData = array_merge($this->getDatabaseRecords(), $this->getMockData());
        $record = collect($allData)->firstWhere('po_number', $poNumber);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Order ' . $poNumber . ' not found.',
                'discrepancies' => ['Purchase Order not found in matching database.']
            ], 404);
        }

        $discrepancies = [];
        $matchedFields = ['PO Number: ' . $record['po_number'], 'Supplier: ' . $record['supplier']];

        if (!$record['grn_number']) {
            $discrepancies[] = 'Delivery Receipt (GRN / SRN) not found. Matching cannot be completed until all required documents are available.';
        } else {
            $matchedFields[] = ($record['receipt_type'] === 'services' ? 'Service Entry Sheet: ' : 'Goods Receipt Note: ') . $record['grn_number'];
        }

        if (!$record['invoice_number']) {
            $discrepancies[] = 'Supplier Invoice not found. Matching cannot be completed until all required documents are available.';
        } else {
            $matchedFields[] = 'Supplier Invoice: ' . $record['invoice_number'];
        }

        if (!empty($record['discrepancies'])) {
            foreach ($record['discrepancies'] as $d) {
                if (!in_array($d, $discrepancies, true)) {
                    $discrepancies[] = $d;
                }
            }
        }

        if ($record['invoice_number'] && $record['grn_number']) {
            if (abs($record['variance']) < 0.01 && empty($discrepancies)) {
                $status = 'Matched';
                $paymentApprovable = true;
                $matchedFields[] = 'Amounts Fully Reconciled ($' . number_format($record['po_amount'], 2) . ')';
                $matchedFields[] = 'Supplier & Document IDs Matched';
            } else {
                $status = $record['variance'] < 0 ? 'Partial Match' : 'Mismatch';
                $paymentApprovable = false;
                if ($record['variance'] > 0 && !collect($discrepancies)->contains(fn($d) => str_contains($d, 'exceeds'))) {
                    $discrepancies[] = 'Total Amount Over-Invoiced: Invoice Amount ($' . number_format($record['invoice_amount'], 2) . ') exceeds PO Value ($' . number_format($record['po_amount'], 2) . ') by $' . number_format($record['variance'], 2) . '.';
                } else if ($record['variance'] < 0 && !collect($discrepancies)->contains(fn($d) => str_contains($d, 'shortage') || str_contains($d, 'Under-received'))) {
                    $discrepancies[] = 'Quantity Shortage: Received value is less than PO Value by $' . number_format(abs($record['variance']), 2) . '.';
                }
            }
        } else {
            $status = $record['status'];
            $paymentApprovable = false;
        }

        return response()->json([
            'success' => true,
            'po_number' => $record['po_number'],
            'status' => $status,
            'payment_approvable' => $paymentApprovable,
            'discrepancies' => array_values(array_unique($discrepancies)),
            'matched_fields' => $matchedFields,
            'message' => empty($discrepancies) 
                ? '3-Way Matching Successful! All documents, quantities, and amounts match cleanly.' 
                : '3-Way Matching Executed: Mismatches or delivery issues flagged.'
        ]);
    }

    // Get Matching Details for Modal
    public function getMatchingDetails($poNumber)
    {
        $allData = array_merge($this->getDatabaseRecords(), $this->getMockData());
        $record = collect($allData)->firstWhere('po_number', $poNumber);

        if (!$record) {
            return response()->json(['error' => 'Matching record not found'], 404);
        }

        return response()->json([
            'po_number' => $record['po_number'],
            'po_date' => $record['po_date'],
            'supplier' => $record['supplier'],
            'commodity' => $record['commodity'],
            'receipt_type' => $record['receipt_type'] ?? 'goods',
            'warehouse' => $record['warehouse'],
            'payment_terms' => $record['payment_terms'],
            'po_amount' => $record['po_amount'],
            'grn_number' => $record['grn_number'],
            'grn_date' => $record['grn_date'],
            'invoice_number' => $record['invoice_number'],
            'invoice_date' => $record['invoice_date'],
            'invoice_amount' => $record['invoice_amount'],
            'variance' => $record['variance'],
            'status' => $record['status'],
            'payment_approvable' => $record['payment_approvable'] ?? ($record['status'] === 'Matched'),
            'discrepancies' => $record['discrepancies'] ?? [],
            'matched_fields' => $record['matched_fields'] ?? [],
            'received_by' => $record['received_by'] ?? 'Warehouse Receiver',
            'approved_by' => $record['approved_by'] ?? null,
            'approved_at' => $record['approved_at'] ?? null,
        ]);
    }

    // Approve Payments ONLY for Validated Transactions
    public function approvePayment($id)
    {
        $allData = array_merge($this->getDatabaseRecords(), $this->getMockData());
        
        $targetRecord = null;
        foreach ($allData as $r) {
            if ((string)$r['id'] === (string)$id || $r['po_number'] === $id) {
                $targetRecord = $r;
                break;
            }
        }

        // Strictly enforce validation before allowing payment approval
        if ($targetRecord && !in_array($targetRecord['status'], ['Matched', 'Approved for Payment'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Payment cannot be approved: Transaction contains unresolved 3-way matching issues, quantity/amount mismatches, or missing required documents.',
                'discrepancies' => $targetRecord['discrepancies'] ?? ['Transaction status is not fully Matched.'],
            ], 422);
        }

        $dr = DeliveryReceipt::find($id);
        $approverName = Auth::check() ? Auth::user()->name : 'System Approver';
        $approvedAt = now()->format('Y-m-d H:i:s');

        if ($dr) {
            $itemsData = is_array($dr->items) ? $dr->items : json_decode($dr->items ?? '[]', true);
            $itemsData['approved_by'] = $approverName;
            $itemsData['approved_at'] = $approvedAt;
            $dr->items = $itemsData;
            $dr->save();

            if ($dr->purchaseOrder) {
                $dr->purchaseOrder->status = 'received';
                $dr->purchaseOrder->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment validated and approved successfully!',
            'approved_by' => $approverName,
            'approved_at' => $approvedAt,
            'status' => 'Approved for Payment'
        ]);
    }

    // Cancel Transaction (Audit Preserved Cancellation Workflow)
    public function cancelTransaction(Request $request, $id)
    {
        $reason = $request->input('cancellation_reason');
        $notes = $request->input('cancellation_notes');

        if (!$reason) {
            return response()->json([
                'success' => false,
                'message' => 'A cancellation reason is required to cancel a transaction.'
            ], 422);
        }

        $cancelledBy = Auth::check() ? Auth::user()->name : 'Procurement Officer';
        $cancelledAt = now()->format('Y-m-d H:i:s');
        $fullReason = $reason . ($notes ? " - {$notes}" : '');

        // 1. Find or create PurchaseOrder
        $po = PurchaseOrder::find($id);
        if (!$po) {
            $po = PurchaseOrder::where('po_number', $id)->first();
        }

        if (!$po) {
            $supplier = Supplier::first();
            $po = PurchaseOrder::create([
                'po_number' => (string)$id,
                'supplier_id' => $supplier ? $supplier->id : 1,
                'status' => 'cancelled',
                'total' => 0,
                'issued_at' => now(),
            ]);
        } else {
            $po->status = 'cancelled';
            $po->save();
        }

        // 2. Find or create DeliveryReceipt
        $dr = DeliveryReceipt::find($id);
        if (!$dr) {
            $dr = DeliveryReceipt::where('dr_number', $id)->first();
        }
        if (!$dr && $po) {
            $dr = DeliveryReceipt::where('purchase_order_id', $po->id)->first();
        }

        if (!$dr && $po) {
            $dr = DeliveryReceipt::create([
                'dr_number' => 'GRN-CANCELLED-' . substr(md5($po->po_number), 0, 6),
                'purchase_order_id' => $po->id,
                'received_at' => now(),
                'items' => [
                    'status' => 'Cancelled',
                    'cancellation_reason' => $fullReason,
                    'cancelled_by' => $cancelledBy,
                    'cancelled_at' => $cancelledAt,
                ],
            ]);
        } else if ($dr) {
            $itemsData = is_array($dr->items) ? $dr->items : json_decode($dr->items ?? '[]', true);
            $itemsData['status'] = 'Cancelled';
            $itemsData['cancellation_reason'] = $fullReason;
            $itemsData['cancelled_by'] = $cancelledBy;
            $itemsData['cancelled_at'] = $cancelledAt;
            $dr->items = $itemsData;
            $dr->save();
        }

        // 3. Persist to session store for extra persistence guarantee across mock data
        $cancelledStore = session('cancelled_transactions', []);
        $cancelledStore[$id] = [
            'status' => 'Cancelled',
            'cancellation_reason' => $fullReason,
            'cancelled_by' => $cancelledBy,
            'cancelled_at' => $cancelledAt,
        ];
        if ($po) {
            $cancelledStore[$po->po_number] = $cancelledStore[$id];
        }
        session(['cancelled_transactions' => $cancelledStore]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction cancelled successfully.',
            'cancelled_by' => $cancelledBy,
            'cancelled_at' => $cancelledAt,
            'cancellation_reason' => $fullReason,
            'status' => 'Cancelled'
        ]);
    }
}
