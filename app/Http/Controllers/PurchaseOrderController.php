<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        // Sync rejected requisitions into purchase_orders table to ensure all rejected approvals exist in order management
        $rejectedReqs = \App\Models\Requisition::where('status', 'rejected')->get();
        foreach ($rejectedReqs as $rReq) {
            $po = PurchaseOrder::where('requisition_id', $rReq->id)->first();
            if (!$po) {
                $supplier = $rReq->supplier_id ? Supplier::find($rReq->supplier_id) : null;
                if (!$supplier) {
                    $supplier = Supplier::where('status', 'active')->first() ?? Supplier::first();
                }
                if (!$supplier) {
                    $supplier = Supplier::create([
                        'name' => 'Ambatugrow General Supplier',
                        'status' => 'active',
                        'contact_person' => 'Sales Department',
                        'email' => 'sales@ambatugrow.test',
                        'phone' => '(000) 000-0000',
                        'address' => 'Indang, Cavite',
                        'city' => 'Cavite',
                    ]);
                }
                $year = date('Y');
                $latest = PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")
                    ->orderByRaw('length(po_number) desc, po_number desc')
                    ->first();
                $nextNumber = 1;
                if ($latest) {
                    $parts = explode('-', $latest->po_number);
                    $lastNumber = (int) end($parts);
                    $nextNumber = $lastNumber + 1;
                }
                $poNumber = 'PO-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

                PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'supplier_id' => $supplier->id,
                    'requisition_id' => $rReq->id,
                    'status' => 'rejected',
                    'total' => $rReq->total,
                    'issued_at' => $rReq->updated_at ?? now(),
                    'created_by' => $rReq->requestor_id,
                ]);
            } else if (!in_array($po->status, ['rejected', 'cancelled'])) {
                $po->update(['status' => 'rejected']);
            }
        }

        $purchaseOrders = PurchaseOrder::with(['supplier', 'items', 'requisition.approvalSteps.approver', 'creator'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        $suppliers = Supplier::orderBy('name')->get();

        // Calculate dynamic stats
        $stats = [
            'total' => $purchaseOrders->count(),
            'draft' => $purchaseOrders->where('status', 'draft')->count(),
            'sent' => $purchaseOrders->where('status', 'sent')->count(),
            'overdue' => $purchaseOrders->filter(function ($po) {
                return !in_array($po->status, ['received', 'rejected', 'cancelled']) && $po->expected_delivery && $po->expected_delivery->isPast();
            })->count(),
            'rejected' => $purchaseOrders->whereIn('status', ['rejected', 'cancelled'])->count(),
        ];

        // Calculate spend data per supplier
        $spendData = $purchaseOrders->groupBy('supplier_id')->map(function ($pos) {
            return [
                'supplier' => $pos->first()->supplier->name ?? 'Unknown',
                'total' => (float)$pos->sum('total'),
            ];
        })->values();

        return view('purchase_orders.procurement', compact('purchaseOrders', 'suppliers', 'stats', 'spendData'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::with('supplier', 'requisition')->orderBy('created_at', 'desc')->get();

        // Calculate dynamic stats
        $stats = [
            'total' => $purchaseOrders->count(),
            'draft' => $purchaseOrders->where('status', 'draft')->count(),
            'sent' => $purchaseOrders->where('status', 'sent')->count(),
            'overdue' => $purchaseOrders->filter(function ($po) {
                return $po->status !== 'received' && $po->expected_delivery && $po->expected_delivery->isPast();
            })->count(),
        ];

        return view('purchase_orders.createpo', compact('suppliers', 'purchaseOrders', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // create PO
        $year = date('Y');
        $latest = PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")
            ->orderByRaw('length(po_number) desc, po_number desc')
            ->first();
        $nextNumber = 1;
        if ($latest) {
            $parts = explode('-', $latest->po_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }
        $poNumber = 'PO-'.$year.'-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $data['supplier_id'],
            'status' => 'draft',
            'expected_delivery' => $data['expected_delivery'] ?? null,
            'issued_at' => now(),
        ]);

        $total = 0;
        foreach($data['items'] as $it){
            $line = $it['quantity'] * $it['unit_price'];

            $unit = $it['unit'] ?? $it['uom'] ?? null;
            if (!$unit || $unit === 'No UOM Assigned') {
                $productMatch = \App\Models\Product::with('uom')
                    ->where('sku', $it['sku'] ?? '')
                    ->orWhere('name', $it['name'])
                    ->first();
                if ($productMatch && $productMatch->uom) {
                    $unit = $productMatch->uom->uom_name ?: $productMatch->uom->uom_code;
                }
            }

            $po->items()->create([
                'sku' => $it['sku'] ?? null,
                'name' => $it['name'],
                'quantity' => $it['quantity'],
                'unit' => $unit ?: 'Unit',
                'unit_price' => $it['unit_price'],
                'line_total' => $line,
            ]);

            // Synchronize with Supplier Management po_items table
            $product = \App\Models\Product::where('sku', $it['sku'] ?? '')
                ->orWhere('name', $it['name'])
                ->first();
            
            if (!$product) {
                $cat = \App\Models\Category::first();
                if (!$cat) {
                    $cat = \App\Models\Category::create(['category_name' => 'Uncategorized']);
                }
                $uom = \App\Models\UnitOfMeasure::first();
                if (!$uom) {
                    $uom = \App\Models\UnitOfMeasure::create(['uom_code' => 'Unit', 'uom_name' => 'Unit']);
                }
                $curr = \App\Models\Currency::first();
                if (!$curr) {
                    $curr = \App\Models\Currency::create(['currency_code' => 'PHP', 'currency_name' => 'Philippine Peso', 'exchange_rate' => 1.0000]);
                }
                
                $product = \App\Models\Product::create([
                    'sku' => $it['sku'] ?? ('SKU-' . strtoupper(Str::random(6))),
                    'name' => $it['name'],
                    'category_id' => $cat->id,
                    'uom_id' => $uom->id,
                    'currency_id' => $curr->id,
                    'base_price' => $it['unit_price'],
                    'min_quantity_threshold' => 10,
                    'lead_time_days' => 3,
                ]);
            }

            \App\Models\POItem::create([
                'po_id' => $po->id,
                'product_id' => $product->id,
                'quantity' => $it['quantity'],
                'uom_id' => $product->uom_id,
                'unit_price' => $it['unit_price'],
            ]);

            $total += $line;
        }

        $po->total = $total;
        $po->save();

        return redirect()->route('procurement.home')->with('status','PO created');
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        // mark as sent and set issued_at
        $purchaseOrder->status = 'sent';
        $purchaseOrder->issued_at = now();
        $purchaseOrder->save();

        // For now, just log/send an email stub (Mail config required to actually send)
        \Log::info('PO sent', ['po' => $purchaseOrder->po_number, 'supplier' => $purchaseOrder->supplier->name]);

        return back()->with('status','PO marked as sent');
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate(['status' => 'required|string']);
        $purchaseOrder->status = $request->input('status');
        $purchaseOrder->save();
        return back()->with('status','Status updated');
    }

    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'requisition.requestor', 'requisition.items', 'items']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchase_orders.pdf', ['po' => $purchaseOrder]);
        return $pdf->download($purchaseOrder->po_number . '.pdf');
    }

    public function streamPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'requisition.requestor', 'requisition.items', 'items']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchase_orders.pdf', ['po' => $purchaseOrder]);
        return $pdf->stream($purchaseOrder->po_number . '.pdf');
    }
}
