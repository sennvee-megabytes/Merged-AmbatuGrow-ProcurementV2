@extends('layouts.master')

@section('title', 'Route for Approval')

@php
    $routeUsers = collect($approvers)
        ->flatten(1)
        ->values()
        ->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'job_title' => $u->job_title,
            'role' => $u->role,
        ])
        ->all();

    $routeSteps = collect($steps)
        ->map(fn ($s) => [
            'step_type' => $s->step_type,
            'label' => $s->label,
            'description' => $s->description,
            'required' => (bool) $s->required,
            'approver_id' => $s->approver_id,
        ])
        ->all();
@endphp

@section('content')
<div x-data="routeForm()">

    <div class="mb-5">
        <p class="text-xs text-slate-400">
            <a href="{{ route('requisitions.tracking') }}" class="text-blue-600 hover:underline">Create requisition</a>
            <span class="mx-1">&rsaquo;</span>
            <span class="text-slate-500">{{ $requisition->code }}</span>
            <span class="mx-1">&rsaquo;</span>
            <span class="text-slate-700 font-medium">Route for Approval</span>
        </p>
    </div>

    <form method="POST" action="{{ route('requisitions.route.store', $requisition) }}" class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-5">
        @csrf

        <div class="card p-5">
            <div class="flex items-center justify-between mb-1">
                <h1 class="text-sm font-bold text-slate-700">Route Requisition for Approval</h1>
                <button type="button" @click="addStep()" class="text-xs text-blue-600 hover:underline font-medium">
                    <i class="fa-solid fa-plus mr-1"></i>Add provider
                </button>
            </div>
            <p class="text-xs text-slate-400 mb-5">Select approvers and define the approval flow for this requisition</p>

            <div class="space-y-4">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-start gap-3 pb-4 border-b border-slate-100 last:border-0">
                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center mt-1 shrink-0" x-text="index + 1"></div>

                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-slate-700" x-text="step.label"></p>
                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-600" x-show="step.required">Required</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="step.description"></p>

                            <input type="hidden" :name="`steps[${index}][step_type]`" :value="step.step_type">
                            <input type="hidden" :name="`steps[${index}][label]`" :value="step.label">
                            <input type="hidden" :name="`steps[${index}][description]`" :value="step.description">
                            <input type="hidden" :name="`steps[${index}][required]`" :value="step.required ? 1 : ''" x-show="step.required">
                        </div>

                        <div class="w-52">
                            <select :name="`steps[${index}][approver_id]`" required
                                    class="w-full text-sm rounded-md border border-slate-300 px-2 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">Select approver</option>
                                <template x-for="user in approversFor(step.step_type)" :key="user.id">
                                    <option :value="user.id" :selected="user.id === step.approver_id" x-text="user.name + ' — ' + user.job_title"></option>
                                </template>
                            </select>
                        </div>

                        <button type="button" @click="steps.splice(index, 1)" class="text-red-400 hover:text-red-600 mt-2">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </template>
            </div>

            <button type="button" @click="addStep()" class="text-sm text-blue-600 hover:underline font-medium mt-4">
                <i class="fa-solid fa-plus mr-1"></i>Add Approval Step
            </button>

            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-sm font-bold text-slate-700 mb-3">Additional Options</p>
                <div class="space-y-2">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" name="approval_type" value="sequential" x-model="approvalType" class="mt-0.5">
                        <span>
                            <span class="text-sm font-medium text-slate-700 block">Sequential Type</span>
                            <span class="text-xs text-slate-400">Approvers review one at a time in order</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="radio" name="approval_type" value="parallel" x-model="approvalType" class="mt-0.5">
                        <span>
                            <span class="text-sm font-medium text-slate-700 block">Parallel</span>
                            <span class="text-xs text-slate-400">Approval review simultaneously</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 mt-6 pt-5 border-t border-slate-100">
                <a href="{{ route('requisitions.tracking') }}" class="px-4 py-2 text-sm rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 transition font-medium">Cancel</a>
                <button type="submit" class="px-5 py-2 text-sm rounded-lg bg-[#1f5c3d] hover:bg-[#163f2b] text-white font-semibold shadow-sm transition">Submit for approval</button>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-5">
            <div class="card p-5">
                <h2 class="text-sm font-bold text-slate-700 mb-4">Approval Workflow Preview</h2>
                <div class="space-y-4">
                    <template x-for="(step, index) in steps" :key="'preview-'+index">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0" x-text="index + 1"></div>
                            <div class="flex-1">
                                <p class="text-sm text-slate-700 font-medium" x-text="approverName(step.approver_id, step.step_type)"></p>
                            </div>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-600">Pending</span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="text-sm font-bold text-slate-700 mb-3">Requisition summary</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-slate-400">Requisition ID</dt><dd class="font-medium text-slate-700">{{ $requisition->code }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Requestor</dt><dd class="font-medium text-slate-700">{{ $requisition->requestor->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Department</dt><dd class="font-medium text-slate-700">{{ $requisition->department }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Date created</dt><dd class="font-medium text-slate-700">{{ $requisition->created_at->format('M d, Y') }}</dd></div>
                    <div class="flex justify-between pt-2 border-t border-slate-100"><dt class="text-slate-500 font-semibold">Total Amount</dt><dd class="font-bold text-slate-800">₱{{ number_format($requisition->total, 2) }}</dd></div>
                </dl>
            </div>
        </div>
    </form>
</div>

<script>
    function routeForm() {
        return {
            approvalType: '{{ old('approval_type', $requisition->approval_type ?? 'sequential') }}',
            users: @json($routeUsers),
            steps: @json($routeSteps),

            addStep() {
                this.steps.push({
                    step_type: 'department_head_approval',
                    label: 'Additional Approval',
                    description: 'Extra reviewer for this requisition',
                    required: false,
                    approver_id: '',
                });
            },

            approversFor(stepType) {
                const roleMap = {
                    manager_approval: 'manager',
                    department_head_approval: 'department_head',
                    finance_approval: 'finance_manager',
                };
                return this.users.filter(u => u.role === roleMap[stepType]);
            },

            approverName(id, stepType) {
                const user = this.users.find(u => u.id === id);
                return user ? user.name : 'Select approver';
            }
        }
    }
</script>
@endsection
