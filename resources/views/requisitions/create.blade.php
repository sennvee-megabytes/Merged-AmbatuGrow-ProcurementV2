@extends('layouts.master')

@section('title', 'Create Purchase Requisition')

@section('content')
<div x-data='{
        items: [],
        submitAction: "continue",
        serviceModalOpen: false,
        serviceSearch: "",
        categoryFilter: "",
        services: @json($services),
        selectedServices: [],
        init() {
            this.addBlankItem();
        },
        addBlankItem() {
            this.items.push({ name: "", qty: 1, unit_price: 0, unit: "service", service_id: "" });
        },
        toggleSelected(service, checked) {
            if (checked) {
                this.selectedServices.push(service);
            } else {
                this.selectedServices = this.selectedServices.filter(s => s.id !== service.id);
            }
        },
        addSelectedServices() {
            this.selectedServices.forEach(service => {
                if (this.items.length === 1 && !this.items[0].name) {
                    this.items = [];
                }

                this.items.push({
                    name: service.name,
                    qty: 1,
                    unit_price: parseFloat(service.price),
                    unit: service.unit,
                    service_id: service.id,
                });
            });

            this.selectedServices = [];
            this.serviceModalOpen = false;
        },
        get categories() {
            return [...new Set(this.services.map(s => s.category).filter(Boolean))];
        },
        get filteredServices() {
            return this.services.filter(s => {
                const matchesSearch = s.name.toLowerCase().includes(this.serviceSearch.toLowerCase());
                const matchesCategory = !this.categoryFilter || s.category === this.categoryFilter;
                return matchesSearch && matchesCategory;
            });
        },
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (Number(item.qty) || 0) * (Number(item.unit_price) || 0), 0);
        },
        formatMoney(value) {
            return "$" + Number(value || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }' x-init="init()">
    <div class="flex items-center justify-between mb-5">
        <div>
            <a href="{{ route('requisitions.tracking') }}" class="text-xs text-blue-600 hover:underline">
                <i class="fa-solid fa-arrow-left mr-1"></i>Create requisition
            </a>
            <h1 class="text-lg font-bold text-slate-800 mt-1">CREATE PURCHASE REQUISITION</h1>
            <p class="text-xs text-slate-400">Fill in the details below to request materials or services</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('requisitions.tracking') }}" class="px-4 py-2 text-sm rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50">Cancel</a>
            <button type="submit" form="requisitionForm" @click="submitAction = 'draft'" class="px-4 py-2 text-sm rounded-md border border-blue-200 text-blue-600 bg-blue-50 hover:bg-blue-100 font-medium">Save draft</button>
        </div>
    </div>

    <form id="requisitionForm" method="POST" action="{{ route('requisitions.store') }}" class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5"
          @submit="if (items.length === 0) { $event.preventDefault(); alert('Add at least one item before submitting.'); }">
        @csrf
        <input type="hidden" name="action" :value="submitAction">

        <div class="space-y-5">
            <div class="card p-5">
                <h2 class="text-sm font-bold text-slate-700 mb-4">Purchase Order Details</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Purchase order title</label>
                        <input type="text" name="title" required placeholder="e.g. Q3 Marketing Software Renewal"
                               class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Department</label>
                        <input type="text" name="department" value="{{ auth()->user()->department }}" placeholder="Department"
                               class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Requestor *</label>
                        <input type="text" value="{{ auth()->user()->name }}" disabled
                               class="w-full px-3 py-2 text-sm rounded-md border border-slate-200 bg-slate-50 text-slate-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Needed by *</label>
                        <input type="date" name="needed_by" required
                               class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Urgency</label>
                        <select name="urgency" class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Purpose / description</label>
                        <textarea name="purpose" rows="3" placeholder="Explain why this purchase is needed..."
                                  class="w-full px-3 py-2 text-sm rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-200"></textarea>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="text-sm font-bold text-slate-700 mb-4">Items</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-400 border-b border-slate-200">
                                <th class="py-2 pr-2 font-medium">Item / service</th>
                                <th class="py-2 px-2 font-medium w-20">Qty</th>
                                <th class="py-2 px-2 font-medium w-28">Unit price</th>
                                <th class="py-2 px-2 font-medium w-28">Total</th>
                                <th class="py-2 pl-2 font-medium w-10">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="border-b border-slate-100">
                                    <td class="py-2 pr-2">
                                        <input type="text" x-model="item.name" :name="`items[${index}][name]`" required placeholder="Item or service name"
                                               class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                        <input type="hidden" :name="`items[${index}][service_id]`" :value="item.service_id">
                                        <input type="hidden" :name="`items[${index}][unit]`" :value="item.unit">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" min="0.01" step="0.01" x-model.number="item.qty" :name="`items[${index}][qty]`" required
                                               class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" min="0" step="0.01" x-model.number="item.unit_price" :name="`items[${index}][unit_price]`" required
                                               class="w-full px-2 py-1.5 text-sm rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                    </td>
                                    <td class="py-2 px-2 text-slate-600" x-text="formatMoney(item.qty * item.unit_price)"></td>
                                    <td class="py-2 pl-2 text-center">
                                        <button type="button" @click="items.splice(index, 1)" class="text-red-400 hover:text-red-600">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="5" class="py-6 text-center text-slate-400 text-sm">No items yet — add one below.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center gap-4 mt-3">
                    <button type="button" @click="addBlankItem()" class="text-sm text-blue-600 hover:underline font-medium">
                        <i class="fa-solid fa-plus mr-1"></i>Add item
                    </button>
                    <button type="button" @click="serviceModalOpen = true" class="text-sm text-slate-500 hover:underline font-medium">
                        <i class="fa-solid fa-list mr-1"></i>Select from catalog
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card p-5">
                <h2 class="text-sm font-bold text-slate-700 mb-4">Purchase order summary</h2>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Subtotal</dt>
                        <dd class="font-medium text-slate-700" x-text="formatMoney(subtotal)"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Tax (0%)</dt>
                        <dd class="font-medium text-slate-700" x-text="formatMoney(0)"></dd>
                    </div>
                    <div class="flex justify-between pt-2.5 border-t border-slate-200">
                        <dt class="text-slate-600 font-semibold">Estimated total</dt>
                        <dd class="font-bold text-slate-800" x-text="formatMoney(subtotal)"></dd>
                    </div>
                </dl>
            </div>

            <button type="submit" form="requisitionForm" @click="submitAction = 'continue'"
                    class="btn-primary w-full text-white text-sm font-semibold py-2.5 rounded-md">
                Submit purchase order
            </button>
            <p class="text-[11px] text-slate-400 text-center -mt-3">You’ll set the approval route on the next step.</p>
        </div>
    </form>

    <div x-show="serviceModalOpen" x-cloak class="fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50 p-4"
         @keydown.escape.window="serviceModalOpen = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[85vh] flex flex-col" @click.outside="serviceModalOpen = false">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-700">Select service</h3>
                <div class="flex gap-2 mt-3">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" x-model="serviceSearch" placeholder="Search product"
                               class="w-full pl-8 pr-3 py-2 text-sm rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>
                    <select x-model="categoryFilter" class="text-sm rounded-md border border-slate-200 px-2">
                        <option value="">Category</option>
                        <template x-for="cat in categories" :key="cat">
                            <option :value="cat" x-text="cat"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-2 py-2">
                <template x-for="service in filteredServices" :key="service.id">
                    <label class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" :value="service.id" @change="toggleSelected(service, $event.target.checked)"
                               class="rounded border-slate-300 text-blue-600">
                        <div class="flex-1">
                            <p class="text-sm text-slate-700 font-medium" x-text="service.name"></p>
                            <p class="text-xs text-slate-400" x-text="service.category"></p>
                        </div>
                        <p class="text-sm text-slate-600" x-text="'$' + Number(service.price).toFixed(2) + ' / ' + service.unit"></p>
                    </label>
                </template>
                <p x-show="filteredServices.length === 0" class="text-center text-sm text-slate-400 py-6">No matching services.</p>
            </div>

            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-400" x-text="selectedServices.length + ' selected'"></p>
                <div class="flex gap-2">
                    <button type="button" @click="serviceModalOpen = false" class="px-4 py-2 text-sm rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="button" @click="addSelectedServices()" class="btn-primary px-4 py-2 text-sm rounded-md text-white font-medium">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
