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
    <label class="block text-sm text-gray-300 mb-2" for="description">Description</label>
    <textarea name="description" id="description" rows="2" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">{{ old('description', $coupon->description ?? '') }}</textarea>
</div>
<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
    <label class="text-sm text-gray-300">Active</label>
</div>

