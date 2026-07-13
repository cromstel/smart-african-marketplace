@php
    $modalErrors = $errors->getBag('default');
@endphp

<div
    x-data="{ 
        open: false,
        checkboxChecked: {{ old('register_as_vendor') || request()->query('type') === 'vendor' ? 'true' : 'false' }},
        init() {
            if (this.checkboxChecked) {
                setTimeout(() => { this.open = true; }, 100);
            }
            this.$watch('checkboxChecked', value => {
                if (value) { this.open = true; }
            });
            this.$watch('open', value => {
                document.body.classList[value ? 'add' : 'remove']('overflow-hidden');
                if (!value && !this.checkboxChecked) {
                    const el = document.getElementById('register_as_vendor');
                    if (el) { el.checked = false; }
                }
            });
        }
    }"
    x-cloak
>
    {{-- Hidden checkbox that controls the modal trigger - placed outside modal for form integration --}}
    <input 
        type="checkbox" 
        id="register_as_vendor" 
        name="register_as_vendor" 
        value="1" 
        {{ old('register_as_vendor') || request()->query('type') === 'vendor' ? 'checked' : '' }}
        class="hidden"
        @change="checkboxChecked = $event.target.checked; if(checkboxChecked) open = true"
    >

    {{-- Checkbox label shown in form (styled as clickable area) --}}
    <label 
        for="register_as_vendor" 
        class="flex items-start cursor-pointer"
        @click.prevent="checkboxChecked = !checkboxChecked; if(checkboxChecked) open = true"
    >
        <span class="flex items-center">
            <span 
                class="w-4 h-4 mt-1 border-2 rounded {{ old('register_as_vendor') || request()->query('type') === 'vendor' ? 'bg-orange-600 border-orange-600' : 'border-gray-300' }} flex items-center justify-center transition-colors"
                :class="{ 'bg-orange-600 border-orange-600': checkboxChecked, 'border-gray-300': !checkboxChecked }"
            >
                <svg x-show="checkboxChecked" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </span>
        </span>
        <span class="ml-2 text-sm font-medium text-gray-700">
            Register as a Vendor
        </span>
    </label>
    <p class="ml-6 text-xs text-gray-500 mt-1">Check this box if you want to sell products on Smart African Market</p>

    {{-- Modal Backdrop --}}
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"
        aria-hidden="true"
        @click="open = false"
    ></div>

    {{-- Modal Container --}}
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 flex items-center justify-center z-50 px-4 py-8"
        role="dialog"
        aria-modal="true"
        aria-labelledby="vendor-modal-title"
    >
        <div 
            class="w-full max-w-2xl max-h-[90vh] rounded-2xl bg-white py-6 px-6 shadow-2xl border border-gray-200 text-left overflow-hidden flex flex-col"
        >
            {{-- Modal Header --}}
            <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-200 flex-shrink-0">
                <div>
                    <h3 id="vendor-modal-title" class="text-lg font-semibold text-gray-900">Vendor Registration Details</h3>
                    <p class="text-sm text-gray-500">Provide your business information to start selling</p>
                </div>
                <button 
                    type="button" 
                    class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100 transition-colors" 
                    @click.prevent.stop="open = false; checkboxChecked = false" 
                    @keydown.escape.window="open = false; checkboxChecked = false" 
                    aria-label="Close"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body - Scrollable --}}
            <div class="overflow-y-auto flex-1 pr-2">
                <div class="space-y-4">
                    {{-- Business Name --}}
                    <div>
                        <label for="modal-business_name" class="sr-only">Business Name *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <input 
                                id="modal-business_name" 
                                type="text" 
                                name="business_name" 
                                value="{{ old('business_name') }}"
                                required
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" 
                                placeholder="Your Business Name"
                            >
                        </div>
                        <x-form-hint text="Enter the public name customers will see on your store." />
                        @if ($modalErrors->has('business_name'))
                            <p class="mt-2 text-xs text-red-600">{{ $modalErrors->first('business_name') }}</p>
                        @endif
                    </div>

                    {{-- Business Category --}}
                    <div>
                        <label for="modal-vendor_category_id" class="sr-only">Business Category *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <select 
                                id="modal-vendor_category_id" 
                                name="vendor_category_id"
                                required
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                            >
                                <option value="">Select a category</option>
                                @forelse($vendorCategories ?? [] as $category)
                                    <option value="{{ optional($category)->id }}" {{ old('vendor_category_id') == optional($category)->id ? 'selected' : '' }}>
                                        {{ optional($category)->icon }} {{ optional($category)->name }}
                                    </option>
                                @empty
                                    <option value="" disabled>No categories available</option>
                                @endforelse
                            </select>
                        </div>
                        <x-form-hint text="Choose the category that best matches what your business sells." />
                        @if ($modalErrors->has('vendor_category_id'))
                            <p class="mt-2 text-xs text-red-600">{{ $modalErrors->first('vendor_category_id') }}</p>
                        @endif
                    </div>

                    {{-- Business Email & Phone Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="modal-business_email" class="sr-only">Business Email *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input 
                                    id="modal-business_email" 
                                    type="email" 
                                    name="business_email" 
                                    value="{{ old('business_email') }}"
                                    required
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" 
                                    placeholder="business@domain.com"
                                >
                            </div>
                            <x-form-hint text="Use the main email customers should use for business communication." />
                            @if ($modalErrors->has('business_email'))
                                <p class="mt-2 text-xs text-red-600">{{ $modalErrors->first('business_email') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="modal-business_phone" class="sr-only">Business Phone *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <input 
                                    id="modal-business_phone" 
                                    type="tel" 
                                    name="business_phone" 
                                    value="{{ old('business_phone') }}"
                                    required
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" 
                                    placeholder="+1234567890"
                                >
                            </div>
                            <x-form-hint text="Include country code so support and customers can reach you." />
                            @if ($modalErrors->has('business_phone'))
                                <p class="mt-2 text-xs text-red-600">{{ $modalErrors->first('business_phone') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Business Address --}}
                    <div>
                        <label for="modal-business_address" class="sr-only">Business Address *</label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <textarea 
                                id="modal-business_address" 
                                name="business_address" 
                                rows="3"
                                required
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" 
                                placeholder="Full business address"
                            >{{ old('business_address') }}</textarea>
                        </div>
                        <x-form-hint class="top-3 !inset-y-auto items-start" text="Enter the physical business location used for verification and operations." />
                        @if ($modalErrors->has('business_address'))
                            <p class="mt-2 text-xs text-red-600">{{ $modalErrors->first('business_address') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="mt-6 pt-4 border-t border-gray-200 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="vendor_terms" 
                            name="vendor_terms" 
                            value="1"
                            required
                            class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 focus:ring-2"
                        >
                        <label for="vendor_terms" class="ml-2 text-sm text-gray-600">
                            I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-orange-600 hover:text-orange-500">Vendor Terms</a> and <a href="{{ route('privacy') }}" target="_blank" class="text-orange-600 hover:text-orange-500">Privacy Policy</a>
                        </label>
                    </div>
                    <button 
                        type="button"
                        class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200"
                        @click="$el.closest('form').submit()"
                    >
                        Confirm & Continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

