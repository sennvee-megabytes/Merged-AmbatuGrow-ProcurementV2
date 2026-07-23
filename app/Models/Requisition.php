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
        return match ($this->status) {
            'draft' => 'Draft',
            'pending_approval' => 'Pending Approval',
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
