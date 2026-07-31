<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number', 'supplier_id', 'requisition_id', 'status', 'total', 'expected_delivery', 'issued_at',
        'payment_term_id', 'currency_id', 'order_date', 'created_by'
    ];

    protected $casts = [
        'expected_delivery' => 'date',
        'issued_at' => 'datetime',
        'total' => 'decimal:2',
        'order_date' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function poItems()
    {
        return $this->hasMany(POItem::class, 'po_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supplierInvoices()
    {
        return $this->hasMany(SupplierInvoice::class, 'po_id');
    }

    public function scopeRejected($query)
    {
        return $query->whereIn('status', ['rejected', 'cancelled']);
    }

    public function getRejectedStepAttribute()
    {
        if ($this->requisition) {
            return $this->requisition->approvalSteps()->where('status', 'rejected')->latest()->first();
        }
        return null;
    }

    public function getRejectedByNameAttribute()
    {
        $step = $this->rejected_step;
        if ($step && $step->approver) {
            return $step->approver->name;
        }
        return $this->creator?->name ?? 'System Admin';
    }

    public function getRejectedDateFormattedAttribute()
    {
        $step = $this->rejected_step;
        if ($step && $step->acted_at) {
            return $step->acted_at->format('M d, Y');
        }
        return $this->updated_at ? $this->updated_at->format('M d, Y') : '—';
    }

    public function getRejectionReasonTextAttribute()
    {
        $step = $this->rejected_step;
        if ($step && !empty($step->comment)) {
            return $step->comment;
        }
        return 'Budget Exceeded / Policy Non-compliance';
    }
}

