@extends('layouts.master')

@section('title', 'Add New Supplier')
@section('subtitle', 'Register a new vendor partner in the directory')

@section('content')

    @if ($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700 mb-2">
            <div class="font-bold flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Submission Error
            </div>
            <ul class="list-disc list-inside space-y-1 pl-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                            <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Enter company name" class="form-input @error('company_name') !border-red-500 @enderror" required>
                            @error('company_name')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Business Type <span class="text-red-500">*</span></label>
                            <select name="business_type" class="form-input @error('business_type') !border-red-500 @enderror" required>
                                <option value="" disabled {{ old('business_type') ? '' : 'selected' }}>Select business type</option>
                                <option value="Farm" {{ old('business_type') === 'Farm' ? 'selected' : '' }}>Farm</option>
                                <option value="Cooperative" {{ old('business_type') === 'Cooperative' ? 'selected' : '' }}>Cooperative</option>
                                <option value="Distributor" {{ old('business_type') === 'Distributor' ? 'selected' : '' }}>Distributor</option>
                                <option value="Wholesaler" {{ old('business_type') === 'Wholesaler' ? 'selected' : '' }}>Wholesaler</option>
                            </select>
                            @error('business_type')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Location (City, Province) <span class="text-red-500">*</span></label>
                            <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Indang, Cavite" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Complete Street Address <span class="text-red-500">*</span></label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="e.g. Barangay Kaytambog, Indang, Cavite" class="form-input" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Enter phone number" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" class="form-input @error('email') !border-red-500 @enderror" required>
                            @error('email')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
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
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="Enter contact person name" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Position / Designation <span class="text-red-500">*</span></label>
                        <input type="text" name="position" value="{{ old('position') }}" placeholder="Enter position" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="Enter phone number" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="Enter email address" class="form-input" required>
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
                            <option {{ old('lead_time') === '2–3 Business Days' ? 'selected' : '' }}>2–3 Business Days</option>
                            <option {{ old('lead_time') === '1 Week' ? 'selected' : '' }}>1 Week</option>
                            <option {{ old('lead_time') === '2 Weeks' ? 'selected' : '' }}>2 Weeks</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Delivery Schedule <span class="text-red-500">*</span></label>
                        <select name="delivery_schedule" class="form-input" required>
                            <option {{ old('delivery_schedule') === 'Monday – Saturday' ? 'selected' : '' }}>Monday – Saturday</option>
                            <option {{ old('delivery_schedule') === 'Weekdays Only' ? 'selected' : '' }}>Weekdays Only</option>
                            <option {{ old('delivery_schedule') === 'Flexible' ? 'selected' : '' }}>Flexible</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Minimum Order Quantity <span class="text-red-500">*</span></label>
                        <select name="moq" class="form-input" required>
                            <option {{ old('moq') === '10 Sacks' ? 'selected' : '' }}>10 Sacks</option>
                            <option {{ old('moq') === '5 Sacks' ? 'selected' : '' }}>5 Sacks</option>
                            <option {{ old('moq') === '20 Sacks' ? 'selected' : '' }}>20 Sacks</option>
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
                                @if($product === 'Others') id="product-checkbox-others" @endif
                                {{ is_array(old('products')) && in_array($product, old('products')) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-[14px] text-gray-700">{{ $product }}</span>
                        </label>
                        @endforeach
                    </div>

                    {{-- Conditional Specified Product Input --}}
                    <div id="specified-product-container" class="mt-3 {{ is_array(old('products')) && in_array('Others', old('products')) ? '' : 'hidden' }}">
                        <label class="form-label">Specify Product <span class="text-red-500">*</span></label>
                        <input type="text" name="specified_product" id="specified_product" value="{{ old('specified_product') }}"
                            placeholder="Specify product type" class="form-input @error('specified_product') !border-red-500 @enderror">
                        @error('specified_product')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    @error('products')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Payment Information --}}
                <div class="card">
                    <h2 class="card-title">Payment Information</h2>
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="form-label">Payment Terms <span class="text-red-500">*</span></label>
                            <select name="payment_terms" class="form-input" required>
                                <option value="" disabled {{ old('payment_terms') ? '' : 'selected' }}>Select payment terms</option>
                                <option {{ old('payment_terms') === 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                <option {{ old('payment_terms') === 'Net 15' ? 'selected' : '' }}>Net 15</option>
                                <option {{ old('payment_terms') === 'COD' ? 'selected' : '' }}>COD</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Payment Method <span class="text-red-500">*</span></label>
                            <select name="payment_method" class="form-input" required>
                                <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>Select payment method</option>
                                <option {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option {{ old('payment_method') === 'Check' ? 'selected' : '' }}>Check</option>
                                <option {{ old('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
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
                class="form-input mt-2 resize-none" maxlength="1000">{{ old('description') }}</textarea>
            <div class="text-right text-[12px] text-gray-400 mt-1">0/1000</div>
        </div>

        {{-- Footer Buttons --}}
        <div class="flex items-center justify-end gap-3 pb-4">
            <a href="{{ route('suppliers.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">Save Supplier</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const othersCheckbox = document.getElementById('product-checkbox-others');
            const specifiedContainer = document.getElementById('specified-product-container');
            const specifiedInput = document.getElementById('specified_product');

            function toggleSpecifiedProduct() {
                if (!othersCheckbox || !specifiedContainer || !specifiedInput) return;

                if (othersCheckbox.checked) {
                    specifiedContainer.classList.remove('hidden');
                    specifiedInput.setAttribute('required', 'required');
                } else {
                    specifiedContainer.classList.add('hidden');
                    specifiedInput.removeAttribute('required');
                    specifiedInput.value = '';
                }
            }

            if (othersCheckbox) {
                othersCheckbox.addEventListener('change', toggleSpecifiedProduct);
                toggleSpecifiedProduct();
            }
        });
    </script>
@endsection
