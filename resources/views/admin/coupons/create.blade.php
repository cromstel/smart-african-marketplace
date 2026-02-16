@extends('admin.layout')

@section('title', 'Add Coupon')
@section('header', 'Add New Coupon')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Coupon Information</h2>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Coupon Code *</label>
                        <input type="text" name="code" value="{{ old('code') }}" required
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 uppercase"
                               placeholder="SUMMER2024"
                               style="text-transform: uppercase;">
                        <p class="text-xs text-gray-500 mt-1">Will be automatically converted to uppercase</p>
                        @error('code') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Type *</label>
                        <select name="type" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" onchange="updateValueLabel(this.value)">
                            <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                        @error('type') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <span id="value-label">Value *</span>
                    </label>
                    <input type="number" name="value" value="{{ old('value') }}" required min="0" step="0.01"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="10">
                    <p class="text-xs text-gray-500 mt-1" id="value-hint">Enter discount value</p>
                    @error('value') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                              placeholder="Brief description of this coupon">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Restrictions</h2>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Minimum Purchase Amount</label>
                        <input type="number" name="min_purchase_amount" value="{{ old('min_purchase_amount') }}" min="0" step="0.01"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="0.00">
                        <p class="text-xs text-gray-500 mt-1">Leave empty for no minimum</p>
                        @error('min_purchase_amount') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Maximum Discount Amount</label>
                        <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount') }}" min="0" step="0.01"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="0.00">
                        <p class="text-xs text-gray-500 mt-1">For percentage coupons only</p>
                        @error('max_discount_amount') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Total Usage Limit</label>
                        <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" min="1"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="Unlimited">
                        <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited uses</p>
                        @error('usage_limit') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Per User Limit</label>
                        <input type="number" name="per_user_limit" value="{{ old('per_user_limit') }}" min="1"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="Unlimited">
                        <p class="text-xs text-gray-500 mt-1">Max uses per customer</p>
                        @error('per_user_limit') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Start Date</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to start immediately</p>
                        @error('starts_at') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Expiry Date</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <p class="text-xs text-gray-500 mt-1">Leave empty for no expiry</p>
                        @error('expires_at') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-300">Active (coupon can be used)</label>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Create Coupon
            </button>
            <a href="{{ route('admin.coupons.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
function updateValueLabel(type) {
    const label = document.getElementById('value-label');
    const hint = document.getElementById('value-hint');
    
    if (type === 'percentage') {
        label.textContent = 'Percentage Value * (%)';
        hint.textContent = 'Enter percentage (e.g., 10 for 10% off)';
    } else {
        label.textContent = 'Fixed Amount Value * ($)';
        hint.textContent = 'Enter dollar amount (e.g., 10 for $10 off)';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    updateValueLabel(typeSelect.value);
});
</script>
@endsection
