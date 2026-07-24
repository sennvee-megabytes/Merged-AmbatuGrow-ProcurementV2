<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MatchingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ErDiagramController;
use App\Http\Controllers\Api\ErDiagramApiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {
    return view('dashboard.dashboard');
})->middleware(['auth'])->name('dashboard');

// Web Database Schema Explorer
Route::get('/database-schema', [ErDiagramController::class, 'index'])->middleware(['auth'])->name('erd.schema');

// JSON API Endpoints for ER Diagram Entities
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/suppliers', [ErDiagramApiController::class, 'suppliers']);
    Route::get('/suppliers/{id}', [ErDiagramApiController::class, 'showSupplier']);
    Route::get('/products', [ErDiagramApiController::class, 'products']);
    Route::get('/products/{id}/suppliers', [ErDiagramApiController::class, 'productSuppliers']);
    Route::get('/purchase-orders', [ErDiagramApiController::class, 'purchaseOrders']);
    Route::get('/purchase-orders/{id}', [ErDiagramApiController::class, 'showPurchaseOrder']);
    Route::get('/purchase-orders/{id}/items', [ErDiagramApiController::class, 'purchaseOrderItems']);
    Route::get('/supplier-invoices', [ErDiagramApiController::class, 'supplierInvoices']);
    
    // Lookups
    Route::get('/currencies', [ErDiagramApiController::class, 'currencies']);
    Route::get('/payment-terms', [ErDiagramApiController::class, 'paymentTerms']);
    Route::get('/units-of-measure', [ErDiagramApiController::class, 'uom']);
    Route::get('/addresses', [ErDiagramApiController::class, 'addresses']);
});

Route::prefix('goods-receipt-invoice-matching')->middleware(['auth'])->group(function () {
    Route::get('/', [MatchingController::class, 'index'])->name('matching.index');
    Route::post('/record-grn', [MatchingController::class, 'storeGrn'])->name('matching.store_grn');
    Route::get('/po-items/{purchaseOrder}', [MatchingController::class, 'getPoItems'])->name('matching.po_items');
    Route::post('/approve/{id}', [MatchingController::class, 'approvePayment'])->name('matching.approve');
    Route::post('/cancel/{id}', [MatchingController::class, 'cancelTransaction'])->name('matching.cancel');
    Route::post('/run-matching', [MatchingController::class, 'runThreeWayMatching'])->name('matching.run_matching');
    Route::get('/details/{po_number}', [MatchingController::class, 'getMatchingDetails'])->name('matching.details');
    Route::post('/receive-goods', [MatchingController::class, 'storeGrn'])->name('matching.receive_goods');
});

Route::prefix('order-management')->middleware(['auth'])->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index'])->name('procurement.home');
    Route::get('/purchase', [PurchaseOrderController::class, 'index'])->name('procurement.purchase');
    Route::get('/createpo', [PurchaseOrderController::class, 'create'])->name('procurement.create');

    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase_orders.store');
    Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('purchase_orders.pdf');
    Route::get('/purchase-orders/{purchaseOrder}/view-pdf', [PurchaseOrderController::class, 'streamPdf'])->name('purchase_orders.view_pdf');
    Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase_orders.send');
    Route::post('/purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase_orders.status');
    Route::post('/match-invoice', [PurchaseOrderController::class, 'matchInvoice'])->name('purchase_orders.match_invoice');
});

Route::prefix('purchase-and-requisition')->middleware(['auth', 'approver'])->group(function () {
    Route::get('/', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/{requisition}', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('/approvals/{requisition}/act', [ApprovalController::class, 'act'])->name('approvals.act');

    Route::prefix('requisitions')->group(function () {
        Route::get('/', [RequisitionController::class, 'tracking'])->name('requisitions.tracking');
        Route::get('/create', [RequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('/', [RequisitionController::class, 'store'])->name('requisitions.store');
        Route::get('/{requisition}/receipt', [RequisitionController::class, 'showReceipt'])->name('requisitions.receipt');
        Route::get('/{requisition}/receipt-pdf', [RequisitionController::class, 'downloadReceiptPdf'])->name('requisitions.receipt_pdf');
        Route::get('/{requisition}/route', [RequisitionController::class, 'showRoute'])->name('requisitions.route');
        Route::post('/{requisition}/route', [RequisitionController::class, 'storeRoute'])->name('requisitions.route.store');
    });

    Route::get('/services/search', [RequisitionController::class, 'searchServices'])->name('services.search');
});

Route::prefix('supplier-management')->middleware(['auth'])->group(function () {
    Route::get('/', [SupplierController::class, 'dashboard'])->name('suppliers.dashboard');
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/active', [SupplierController::class, 'activeIndex'])->name('suppliers.active');
    Route::get('/suppliers/pending', [SupplierController::class, 'pendingIndex'])->name('suppliers.pending');
    Route::get('/suppliers/blacklisted', [SupplierController::class, 'blacklistedIndex'])->name('suppliers.blacklisted');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    Route::prefix('/suppliers/{supplier}')->group(function () {
        Route::get('/', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('/products', [SupplierController::class, 'products'])->name('suppliers.products');
        Route::get('/purchase-history', [SupplierController::class, 'purchaseHistory'])->name('suppliers.purchase-history');
        Route::get('/contract', [SupplierController::class, 'contract'])->name('suppliers.contract');
        Route::get('/performance', [SupplierController::class, 'performance'])->name('suppliers.performance');
        Route::post('/block', [SupplierController::class, 'block'])->name('suppliers.block');
        Route::post('/unblock', [SupplierController::class, 'unblock'])->name('suppliers.unblock');
    });
});

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
