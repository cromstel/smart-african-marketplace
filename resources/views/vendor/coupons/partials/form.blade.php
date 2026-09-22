<div>
    <label class="block text-sm text-gray-300 mb-2" for="code">Code</label>
    <input type="text" name="code" id="code" value="{{ old('code', $coupon->code ?? '') }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white" required>
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="type">Type</label>
    <select name="type" id="type" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
        <option value="fixed" {{ old('type', $coupon->type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed</option>
        <option value="percentage" {{ old('type', $coupon->type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage</option>
    </select>
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="value">Value</label>
    <input type="number" step="0.01" name="value" id="value" value="{{ old('value', $coupon->value ?? '') }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white" required>
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="min_purchase_amount">Min Purchase Amount</label>
    <input type="number" step="0.01" name="min_purchase_amount" id="min_purchase_amount" value="{{ old('min_purchase_amount', $coupon->min_purchase_amount ?? '') }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="max_discount_amount">Max Discount Amount</label>
    <input type="number" step="0.01" name="max_discount_amount" id="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount ?? '') }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="usage_limit">Total Usage Limit</label>
    <input type="number" min="1" name="usage_limit" id="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" placeholder="Unlimited" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
    <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited uses across all customers.</p>
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="per_user_limit">Per User Limit</label>
    <input type="number" min="1" name="per_user_limit" id="per_user_limit" value="{{ old('per_user_limit', $coupon->per_user_limit ?? '') }}" placeholder="Unlimited" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
    <p class="text-xs text-gray-500 mt-1">Max times a single customer can use this coupon.</p>
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="starts_at">Start Date</label>
    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', isset($coupon->starts_at) ? $coupon->starts_at?->format('Y-m-d\TH:i') : '') }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
    <p class="text-xs text-gray-500 mt-1">Leave empty to start immediately.</p>
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="expires_at">Expiry Date</label>
    <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at?->format('Y-m-d\TH:i') : '') }}" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
    <p class="text-xs text-gray-500 mt-1">Leave empty for no expiry.</p>
</div>
<div>
    <label class="block text-sm text-gray-300 mb-2" for="description">Description</label>
    <textarea name="description" id="description" rows="2" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">{{ old('description', $coupon->description ?? '') }}</textarea>
</div>
<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
    <label class="text-sm text-gray-300">Active</label>
</div>

