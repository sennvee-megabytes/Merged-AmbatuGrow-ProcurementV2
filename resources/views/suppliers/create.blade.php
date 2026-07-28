@extends('layouts.master')

@section('title', 'Add New Supplier')
@section('subtitle', 'Register a new vendor partner in the directory')

@section('content')

    <form method="POST" action="{{ route('suppliers.store') }}" class="flex flex-col gap-5">
        @csrf

        {{-- Row 1: Company Information + Primary Contact --}}
        <div class="grid grid-cols-2 gap-5">
            {{-- Company Information --}}
            <div class="card">
                <h2 class="card-title">Company Information</h2>
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" placeholder="Enter company name" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Business Type <span class="text-red-500">*</span></label>
                            <select name="business_type" class="form-input" required>
                                <option value="" disabled selected>Select business type</option>
                                <option value="Farm">Farm</option>
                                <option value="Cooperative">Cooperative</option>
                                <option value="Distributor">Distributor</option>
                                <option value="Wholesaler">Wholesaler</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Location (City, Province) <span class="text-red-500">*</span></label>
                            <input type="text" name="location" placeholder="e.g. Indang, Cavite" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Complete Street Address <span class="text-red-500">*</span></label>
                            <input type="text" name="address" placeholder="e.g. Barangay Kaytambog, Indang, Cavite" class="form-input" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" placeholder="Enter phone number" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" placeholder="Enter email address" class="form-input" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Primary Contact --}}
            <div class="card">
                <h2 class="card-title">Primary Contact</h2>
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="form-label">Contact Person <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_person" placeholder="Enter contact person name" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Position / Designation <span class="text-red-500">*</span></label>
                        <input type="text" name="position" placeholder="Enter position" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_phone" placeholder="Enter phone number" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="contact_email" placeholder="Enter email address" class="form-input" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Delivery Information + Product Supplied + Payment Information --}}
        <div class="grid grid-cols-2 gap-5">
            {{-- Delivery Information --}}
            <div class="card">
                <h2 class="card-title">Delivery Information</h2>
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="form-label">Lead Time <span class="text-red-500">*</span></label>
                        <select name="lead_time" class="form-input" required>
                            <option>2–3 Business Days</option>
                            <option>1 Week</option>
                            <option>2 Weeks</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Delivery Schedule <span class="text-red-500">*</span></label>
                        <select name="delivery_schedule" class="form-input" required>
                            <option>Monday – Saturday</option>
                            <option>Weekdays Only</option>
                            <option>Flexible</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Minimum Order Quantity <span class="text-red-500">*</span></label>
                        <select name="moq" class="form-input" required>
                            <option>10 Sacks</option>
                            <option>5 Sacks</option>
                            <option>20 Sacks</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-5">
                {{-- Product Supplied --}}
                <div class="card">
                    <h2 class="card-title">Product Supplied</h2>
                    <p class="form-label !mb-3">Select product supplied <span class="text-red-500">*</span></p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['Rice','Vegetables','Fruits','Others'] as $product)
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="products[]" value="{{ $product }}"
                                class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-[14px] text-gray-700">{{ $product }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Payment Information --}}
                <div class="card">
                    <h2 class="card-title">Payment Information</h2>
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="form-label">Payment Terms <span class="text-red-500">*</span></label>
                            <select name="payment_terms" class="form-input" required>
                                <option value="" disabled selected>Select payment terms</option>
                                <option>Net 30</option>
                                <option>Net 15</option>
                                <option>COD</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Payment Method <span class="text-red-500">*</span></label>
                            <select name="payment_method" class="form-input" required>
                                <option value="" disabled selected>Select payment method</option>
                                <option>Bank Transfer</option>
                                <option>Check</option>
                                <option>Cash</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="card">
            <label class="form-label text-[15px] font-bold text-gray-900">Description</label>
            <textarea name="description" rows="4" placeholder="Add description about your growing business..."
                class="form-input mt-2 resize-none" maxlength="1000"></textarea>
            <div class="text-right text-[12px] text-gray-400 mt-1">0/1000</div>
        </div>

        {{-- Footer Buttons --}}
        <div class="flex items-center justify-end gap-3 pb-4">
            <a href="{{ route('suppliers.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">Save Supplier</button>
        </div>
    </form>
@endsection
