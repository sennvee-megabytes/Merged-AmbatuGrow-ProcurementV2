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
        $purchaseOrders = PurchaseOrder::with('supplier', 'items', 'requisition')
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
                return $po->status !== 'received' && $po->expected_delivery && $po->expected_delivery->isPast();
            })->count(),
        ];

        // Fetch matched invoices
        $invoices = \App\Models\Invoice::with('supplier', 'purchaseOrder')
            ->orderBy('received_at', 'desc')
            ->get();

        // Calculate spend data per supplier
        $spendData = $purchaseOrders->groupBy('supplier_id')->map(function ($pos) {
            return [
                'supplier' => $pos->first()->supplier->name ?? 'Unknown',
                'total' => (float)$pos->sum('total'),
            ];
        })->values();

        return view('purchase_orders.procurement', compact('purchaseOrders', 'suppliers', 'stats', 'invoices', 'spendData'));
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
            $po->items()->create([
                'sku' => $it['sku'] ?? null,
                'name' => $it['name'],
                'quantity' => $it['quantity'],
                'unit' => $it['unit'] ?? $it['uom'] ?? 'Unit',
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

    public function matchInvoice(Request $request)
    {
        // naive match: find PO by po_number and associate invoice
        $request->validate(['po_number'=>'required','invoice_number'=>'required','amount'=>'required|numeric']);
        $po = PurchaseOrder::where('po_number',$request->po_number)->first();
        if(!$po) return back()->with('error','PO not found');

        // create invoice
        $inv = \App\Models\Invoice::create([
            'invoice_number' => $request->invoice_number,
            'supplier_id' => $po->supplier_id,
            'purchase_order_id' => $po->id,
            'amount' => $request->amount,
            'received_at' => now(),
        ]);

        return back()->with('status','Invoice matched to PO');
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
