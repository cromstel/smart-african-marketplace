@extends('admin.layout')

@section('title', 'Edit Subscription Plan')
@section('header', 'Edit Subscription Plan')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.subscriptions.plans.update', $plan) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Plan Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Plan Name *</label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('description', $plan->description) }}</textarea>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Price *</label>
                    <input type="number" name="price" value="{{ old('price', $plan->price) }}" step="0.01" min="0" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Currency *</label>
                    <select name="currency" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="USD" {{ old('currency', $plan->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="EUR" {{ old('currency', $plan->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                        <option value="GBP" {{ old('currency', $plan->currency) == 'GBP' ? 'selected' : '' }}>GBP</option>
                        <option value="NGN" {{ old('currency', $plan->currency) == 'NGN' ? 'selected' : '' }}>NGN</option>
                        <option value="GHS" {{ old('currency', $plan->currency) == 'GHS' ? 'selected' : '' }}>GHS</option>
                        <option value="KES" {{ old('currency', $plan->currency) == 'KES' ? 'selected' : '' }}>KES</option>
                    </select>
                    @error('currency') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Billing Cycle *</label>
                    <select name="billing_cycle" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="lifetime" {{ old('billing_cycle', $plan->billing_cycle) == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                    </select>
                    @error('billing_cycle') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Trial Days *</label>
                    <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" min="0" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">Number of free trial days (0 = no trial)</p>
                    @error('trial_days') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Limits & Restrictions</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Max Products *</label>
                    <input type="number" name="max_products" value="{{ old('max_products', $plan->max_products) }}" min="-1" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">-1 for unlimited products</p>
                    @error('max_products') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Max Orders per Month *</label>
                    <input type="number" name="max_orders" value="{{ old('max_orders', $plan->max_orders) }}" min="-1" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">-1 for unlimited orders</p>
                    @error('max_orders') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Commission Rate (%) *</label>
                    <input type="number" name="commission_rate" value="{{ old('commission_rate', $plan->commission_rate) }}" step="0.01" min="0" max="100" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">Platform commission on sales</p>
                    @error('commission_rate') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">Display order (lower numbers first)</p>
                    @error('sort_order') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Features (Optional)</h2>
            <p class="text-sm text-gray-400 mb-4">Add features included in this plan</p>

            <div id="features-container" class="space-y-2 mb-4">
                @if($plan->features && count($plan->features) > 0)
                    @foreach($plan->features as $index => $feature)
                        <div class="feature-item flex gap-2">
                            <input type="text" name="features[]" value="{{ old('features.'.$index, $feature) }}" 
                                   class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <button type="button" onclick="removeFeature(this)" class="text-red-400 hover:text-red-300 px-3" aria-label="Remove feature">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @else
                    <div class="feature-item flex gap-2">
                        <input type="text" name="features[]" 
                               class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="e.g., Priority Support">
                        <button type="button" onclick="removeFeature(this)" class="text-red-400 hover:text-red-300 px-3" aria-label="Remove feature">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>

            <button type="button" onclick="addFeature()" class="text-indigo-400 hover:text-indigo-300 text-sm">
                + Add Feature
            </button>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Feature Keys (RBAC)</h2>
            <p class="text-sm text-gray-400 mb-4">Select canonical module keys used for access control.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($modulesCatalog as $module)
                    <label class="flex items-center space-x-2 text-sm text-gray-300">
                        <input type="checkbox" name="feature_keys[]" value="{{ $module['key'] }}"
                               {{ in_array($module['key'], old('feature_keys', $plan->feature_keys ?? []), true) ? 'checked' : '' }}
                               class="w-4 h-4 bg-gray-700 border-gray-600 rounded text-indigo-600">
                        <span>{{ $module['name'] }} <span class="text-gray-400">({{ $module['key'] }})</span></span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Settings</h2>

            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_popular" value="1" {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }}
                           class="w-4 h-4 bg-gray-700 border-gray-600 rounded text-indigo-600">
                    <label class="ml-2 text-sm text-gray-300">Mark as Popular Plan</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 bg-gray-700 border-gray-600 rounded text-indigo-600">
                    <label class="ml-2 text-sm text-gray-300">Active (visible to users)</label>
                </div>
            </div>
        </div>

        @if($plan->subscriptions()->count() > 0)
            <div class="bg-amber-900 border border-amber-700 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-amber-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm text-amber-200 font-medium">Active Subscriptions Warning</p>
                        <p class="text-xs text-amber-300 mt-1">This plan has {{ $plan->subscriptions()->count() }} active subscribers. Changes will affect their access limits and commission rates.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Update Plan
            </button>
            <a href="{{ route('admin.subscriptions.plans.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
function addFeature() {
    const container = document.getElementById('features-container');
    const div = document.createElement('div');
    div.className = 'feature-item flex gap-2';
    div.innerHTML = `
        <input type="text" name="features[]" 
               class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
               placeholder="e.g., Advanced Analytics">
        <button type="button" onclick="removeFeature(this)" class="text-red-400 hover:text-red-300 px-3" aria-label="Remove feature">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(div);
}

function removeFeature(button) {
    const container = document.getElementById('features-container');
    if (container.children.length > 1) {
        button.closest('.feature-item').remove();
    }
}
</script>
@endsection

