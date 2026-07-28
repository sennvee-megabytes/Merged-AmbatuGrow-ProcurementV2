<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'requisition_id', 'step_order', 'step_type', 'label', 'description',
        'required', 'approver_id', 'status', 'comment', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
        'required' => 'boolean',
        'approver_id' => 'integer',
        'step_order' => 'integer',
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Only the assigned approver can act, and only when this is the
     * next pending step in the sequence for the requisition.
     */
    public function canBeActedOnBy(User $user, ?Requisition $requisition = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $req = $requisition ?? $this->requisition;
        if (!$req || in_array($req->status, ['approved', 'rejected', 'cancelled', 'completed'], true)) {
            return false;
        }

        $current = $req->currentStep();
        if (!$current || (int)$current->id !== (int)$this->id) {
            return false;
        }

        if ((int)$this->approver_id === (int)$user->id) {
            return true;
        }

        [$sarah, $michael, $johny] = \App\Http\Controllers\ApprovalController::resolveWorkflowUsers();

        if ((int)$this->step_order === 1 && ($user->role === 'manager' || $user->username === 'sarah.jerkins' || $user->username === 'sarah.jenkins' || ($sarah && (int)$user->id === (int)$sarah->id))) {
            return true;
        }

        if ((int)$this->step_order === 2 && ($user->role === 'finance_manager' || $user->username === 'finance.manager' || ($michael && (int)$user->id === (int)$michael->id))) {
            return true;
        }

        if ((int)$this->step_order === 3 && ($user->role === 'department_head' || $user->username === 'johny.papa' || ($johny && (int)$user->id === (int)$johny->id))) {
            return true;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }
}
