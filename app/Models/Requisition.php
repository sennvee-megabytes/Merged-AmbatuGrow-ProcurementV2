<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'department', 'requestor_id', 'supplier_id', 'needed_by', 'purpose',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'approval_type',
        'status', 'urgency', 'submitted_at',
    ];

    protected $casts = [
        'needed_by' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function recommendedSupplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_order');
    }

    public function comments()
    {
        return $this->hasMany(RequisitionComment::class)->latest();
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'requisition_id');
    }

    /**
     * The approval step that is currently awaiting action.
     */
    public function currentStep()
    {
        if ($this->relationLoaded('approvalSteps')) {
            return $this->approvalSteps->where('status', 'pending')->sortBy('step_order')->first();
        }
        return $this->approvalSteps()->where('status', 'pending')->orderBy('step_order')->first();
    }

    public function statusLabel(): string
    {
        if ($this->status === 'pending_approval') {
            $current = $this->currentStep();
            if ($current) {
                if ($current->step_type === 'finance_approval' || (int)$current->step_order === 2) {
                    return 'Pending Finance Approval';
                }
                if ($current->step_type === 'manager_approval' || (int)$current->step_order === 1) {
                    return 'Pending Manager Approval';
                }
                if ($current->step_type === 'department_head_approval' || (int)$current->step_order === 3) {
                    return 'Pending Head Approval';
                }
            }
            return 'Pending Approval';
        }

        return match ($this->status) {
            'draft' => 'Draft',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    public function urgencyColor(): string
    {
        return match ($this->urgency) {
            'High' => 'red',
            'Low' => 'green',
            default => 'orange',
        };
    }
}
