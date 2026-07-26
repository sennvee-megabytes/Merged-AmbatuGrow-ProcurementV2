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
        return [];
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
                $grnDiff = $grnAcceptedTotal > 0 ? ($grnAcceptedTotal - $poAmount) : 0;

                if ($variance > 0.01) {
                    $status = 'Mismatch';
                    $paymentApprovable = false;
                    $discrepancies[] = 'Total Amount Over-Invoiced: Invoice Amount (₱' . number_format($invAmount, 2) . ') exceeds PO Value (₱' . number_format($poAmount, 2) . ') by +₱' . number_format($variance, 2) . '.';
                } else if ($variance < -0.01 || $grnDiff < -0.01) {
                    $status = 'Partial Match';
                    $paymentApprovable = false;
                    if ($variance < -0.01) {
                        $discrepancies[] = 'Under-Invoiced: Invoice Amount (₱' . number_format($invAmount, 2) . ') is less than PO Value (₱' . number_format($poAmount, 2) . ') by -₱' . number_format(abs($variance), 2) . '.';
                    }
                    if ($grnDiff < -0.01) {
                        $discrepancies[] = 'Quantity Shortage: Received/accepted total (₱' . number_format($grnAcceptedTotal, 2) . ') is less than ordered PO Value (₱' . number_format($poAmount, 2) . ').';
                    }
                } else {
                    $status = ($po->status === 'approved' || $po->status === 'received') ? 'Matched' : 'Approved for Payment';
                    $paymentApprovable = true;
                    $matchedFields[] = 'Amounts Fully Reconciled';
                    $matchedFields[] = 'Quantities & Prices Matched';
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
                'created_at' => $dr->created_at ? $dr->created_at->format('Y-m-d H:i:s') : ($dr->received_at ? Carbon::parse($dr->received_at)->format('Y-m-d H:i:s') : ($po->created_at ? $po->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'))),
                'updated_at' => $dr->updated_at ? $dr->updated_at->format('Y-m-d H:i:s') : ($dr->created_at ? $dr->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')),
                'approved_by' => $itemsData['approved_by'] ?? null,
                'approved_at' => $itemsData['approved_at'] ?? null,
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

        // Filter for eligible receiving statuses (Open, Pending Receipt, Awaiting Delivery, Partial)
        $ineligible = ['fully received', 'received', 'closed', 'cancelled'];
        $eligibleList = array_filter($list, function ($po) use ($ineligible) {
            $st = strtolower(trim($po['status'] ?? ''));
            return !in_array($st, $ineligible, true);
        });

        return array_values($eligibleList);
    }

    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'All');
        $searchQuery = $request->input('search');
        $supplierFilter = $request->input('supplier');
        $sortBy = $request->input('sort_by', 'created_desc');

        $warehouseFilter = $request->input('warehouse');
        $commodityFilter = $request->input('commodity');
        $varianceFilter = $request->input('variance_type');
        $minAmount = $request->input('min_amount');
        $maxAmount = $request->input('max_amount');

        $dbRecords = $this->getDatabaseRecords();
        $allData = $dbRecords;

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
                case 'created_asc':
                    return strtotime($a['created_at'] ?? $a['po_date']) <=> strtotime($b['created_at'] ?? $b['po_date']);
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
                case 'created_desc':
                case 'date_desc':
                default:
                    return strtotime($b['created_at'] ?? $b['po_date']) <=> strtotime($a['created_at'] ?? $a['po_date']);
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
        $dbProducts = \App\Models\Product::all(['name', 'base_price', 'sku'])->map(function ($p) {
            return [
                'name' => $p->name,
                'unit_price' => (float)$p->base_price,
                'sku' => $p->sku,
            ];
        })->toArray();

        $mockProducts = [
            ['name' => 'RICE', 'unit_price' => 2500.00],
            ['name' => 'White Maize', 'unit_price' => 2845.00],
            ['name' => 'Soybeans', 'unit_price' => 5200.00],
            ['name' => 'Wheat', 'unit_price' => 1937.50],
            ['name' => 'Cotton Seed', 'unit_price' => 3472.00],
            ['name' => 'Sunflower', 'unit_price' => 4120.00],
            ['name' => 'Yellow Maize', 'unit_price' => 1704.00],
            ['name' => 'Fertilizers & Nutrients', 'unit_price' => 3172.32],
            ['name' => 'Hybrid Crop Seeds', 'unit_price' => 5011.66],
            ['name' => 'Organic Pesticides', 'unit_price' => 941.68],
            ['name' => 'Storage Crates', 'unit_price' => 1164.84],
            ['name' => 'Tractor Fleet Servicing', 'unit_price' => 17700.00],
            ['name' => 'Soil Analysis & Audit', 'unit_price' => 25000.00],
        ];

        $productsList = [];
        $seenNames = [];
        foreach (array_merge($dbProducts, $mockProducts) as $prod) {
            $n = trim($prod['name'] ?? '');
            if ($n && !in_array(strtolower($n), $seenNames, true)) {
                $seenNames[] = strtolower($n);
                $productsList[] = [
                    'name' => $n,
                    'unit_price' => (float)($prod['unit_price'] ?? 0),
                    'sku' => $prod['sku'] ?? null,
                ];
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'records' => array_values($filteredData),
                'count' => count($filteredData),
                'selected' => $selectedRecord,
                'availablePos' => $availablePos,
                'productsList' => $productsList,
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
            'productsList' => $productsList,
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
            'warehouse' => 'Harare Central Depot',
            'items' => $items,
        ]);
    }

    // Record Receipt of Goods or Services & Run 3-Way Match
    public function storeGrn(Request $request)
    {
        $validated = $request->validate([
            'receipt_type' => 'nullable|string', // 'goods' or 'services'
            'po_number' => 'required|string',
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

        // Auto-generate unique sequential GRN or SRN number upon submission
        $grnNumber = $this->generateSequentialGrnNumber($receiptType);

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
            'dr_number' => $grnNumber,
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
            if ($variance > 0.01) {
                $status = 'Mismatch';
                $discrepancies[] = 'Total Amount Over-Invoiced: Invoice Amount (₱' . number_format($inv->amount, 2) . ') exceeds PO Value (₱' . number_format($poTotal, 2) . ') by +₱' . number_format($variance, 2) . '.';
            } else if ($variance < -0.01) {
                $status = 'Partial Match';
                $discrepancies[] = 'Under-Invoiced: Invoice Amount (₱' . number_format($inv->amount, 2) . ') is less than PO Value (₱' . number_format($poTotal, 2) . ') by -₱' . number_format(abs($variance), 2) . '.';
            } else {
                $status = 'Matched';
                $po->status = 'received';
                $po->save();
            }
        } else {
            $status = 'Pending Invoice';
            $discrepancies[] = 'Supplier Invoice not found. Matching cannot be completed until all required documents are available.';
        }

        $receiptLabel = $receiptType === 'services' ? 'Service Entry Sheet' : 'Goods Receipt Note';

        $supplierName = $po->supplier ? ($po->supplier->supplier_name ?? $po->supplier->name ?? 'Vendor') : 'Vendor';
        $words = explode(' ', $supplierName);
        $initials = strtoupper(substr($words[0] ?? 'V', 0, 1) . substr($words[1] ?? '', 0, 1));
        $commodityLabel = !empty($lines) ? ($lines[0]['name'] ?? 'Procured Items') : ($receiptType === 'services' ? 'Service Receipt' : 'Goods Receipt');

        $recordData = [
            'id' => $dr->id,
            'po_number' => $po->po_number,
            'po_id' => $po->id,
            'po_date' => $po->created_at ? $po->created_at->format('d M Y') : now()->format('d M Y'),
            'supplier' => $supplierName,
            'commodity' => $commodityLabel,
            'receipt_type' => $receiptType,
            'payment_terms' => 'Net 30',
            'warehouse' => $validated['receiving_location'] ?? ($receiptType === 'services' ? 'Service Site' : 'Harare Central Depot'),
            'grn_number' => $dr->dr_number,
            'grn_date' => $dr->received_at ? Carbon::parse($dr->received_at)->format('d M Y') : now()->format('d M Y'),
            'invoice_number' => $inv ? $inv->invoice_number : null,
            'invoice_date' => $inv && $inv->received_at ? Carbon::parse($inv->received_at)->format('d M Y') : null,
            'po_amount' => $poTotal,
            'invoice_amount' => $inv ? (float)$inv->amount : 0.0,
            'variance' => $variance,
            'status' => $status,
            'cancellation_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'supplier_initials' => $initials ?: 'GR',
            'payment_approvable' => ($status === 'Matched'),
            'discrepancies' => array_values(array_unique($discrepancies)),
            'matched_fields' => ($status === 'Matched') ? ['PO Number: ' . $po->po_number, 'Supplier: ' . $supplierName, 'Amounts Reconciled'] : ['PO Number: ' . $po->po_number, 'Supplier: ' . $supplierName],
            'received_by' => $receivedBy,
            'received_at' => $dr->received_at ? Carbon::parse($dr->received_at)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'created_at' => $dr->created_at ? $dr->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'updated_at' => $dr->updated_at ? $dr->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'approved_by' => null,
            'approved_at' => null,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$receiptLabel} recorded and 3-way matched successfully!",
                'record' => $recordData,
                'grn_number' => $dr->dr_number,
                'po_number' => $po->po_number,
                'supplier' => $supplierName,
                'status' => $status,
                'variance' => $variance,
                'discrepancies' => array_values(array_unique($discrepancies)),
                'received_by' => $receivedBy,
                'created_at' => $dr->created_at ? $dr->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'updated_at' => $dr->updated_at ? $dr->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'po_amount' => $poTotal,
                'po_date' => $po->created_at ? $po->created_at->format('d M Y') : now()->format('d M Y'),
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

        $allData = $this->getDatabaseRecords();
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
                $matchedFields[] = 'Amounts Fully Reconciled (₱' . number_format($record['po_amount'], 2) . ')';
                $matchedFields[] = 'Supplier & Document IDs Matched';
            } else {
                $status = $record['variance'] < 0 ? 'Partial Match' : 'Mismatch';
                $paymentApprovable = false;
                if ($record['variance'] > 0 && !collect($discrepancies)->contains(fn($d) => str_contains($d, 'exceeds'))) {
                    $discrepancies[] = 'Total Amount Over-Invoiced: Invoice Amount (₱' . number_format($record['invoice_amount'], 2) . ') exceeds PO Value (₱' . number_format($record['po_amount'], 2) . ') by ₱' . number_format($record['variance'], 2) . '.';
                } else if ($record['variance'] < 0 && !collect($discrepancies)->contains(fn($d) => str_contains($d, 'shortage') || str_contains($d, 'Under-received'))) {
                    $discrepancies[] = 'Quantity Shortage: Received value is less than PO Value by ₱' . number_format(abs($record['variance']), 2) . '.';
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
        $allData = $this->getDatabaseRecords();
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
        $allData = $this->getDatabaseRecords();
        
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
            $dr->touch();
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
            'updated_at' => $dr && $dr->updated_at ? $dr->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
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
            $dr->touch();
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
            'updated_at' => $cancelledAt,
            'status' => 'Cancelled'
        ]);
    }

    private function generateSequentialGrnNumber($receiptType = 'goods')
    {
        $prefix = ($receiptType === 'services') ? 'SRN' : 'GRN';
        $year = date('Y');
        $pattern = "{$prefix}-{$year}-%";

        $latest = DeliveryReceipt::where('dr_number', 'LIKE', $pattern)
            ->orderBy('id', 'desc')
            ->pluck('dr_number')
            ->first();

        $nextSeq = 1;
        if ($latest) {
            $parts = explode('-', $latest);
            if (count($parts) >= 3 && is_numeric(end($parts))) {
                $nextSeq = ((int)end($parts)) + 1;
            }
        }

        $grnNumber = sprintf('%s-%s-%05d', $prefix, $year, $nextSeq);

        while (DeliveryReceipt::where('dr_number', $grnNumber)->exists()) {
            $nextSeq++;
            $grnNumber = sprintf('%s-%s-%05d', $prefix, $year, $nextSeq);
        }

        return $grnNumber;
    }
}
