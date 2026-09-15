@csrf
@if($address ?? null)
    @method('PUT')
@endif

@if($errors->any())
    <div class="mb-6 bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label for="type" class="block text-sm font-medium text-gray-300 mb-1">Address Type</label>
        <select name="type" id="type" required
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            @foreach(['shipping' => 'Shipping', 'billing' => 'Billing', 'both' => 'Both'] as $value => $label)
                <option value="{{ $value }}" {{ old('type', $address->type ?? '') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex items-end pb-2">
        <label class="flex items-center gap-2 text-gray-300">
            <input type="checkbox" name="is_default" value="1"
                   {{ old('is_default', $address->is_default ?? false) ? 'checked' : '' }}
                   class="rounded border-gray-600 bg-gray-700 text-indigo-600 focus:ring-indigo-500">
            Set as default address
        </label>
    </div>

    <div>
        <label for="first_name" class="block text-sm font-medium text-gray-300 mb-1">First Name</label>
        <input type="text" name="first_name" id="first_name" required
               value="{{ old('first_name', $address->first_name ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div>
        <label for="last_name" class="block text-sm font-medium text-gray-300 mb-1">Last Name</label>
        <input type="text" name="last_name" id="last_name" required
               value="{{ old('last_name', $address->last_name ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div class="md:col-span-2">
        <label for="company" class="block text-sm font-medium text-gray-300 mb-1">Company <span class="text-gray-500">(optional)</span></label>
        <input type="text" name="company" id="company"
               value="{{ old('company', $address->company ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div class="md:col-span-2">
        <label for="address_line1" class="block text-sm font-medium text-gray-300 mb-1">Address Line 1</label>
        <input type="text" name="address_line1" id="address_line1" required
               value="{{ old('address_line1', $address->address_line1 ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div class="md:col-span-2">
        <label for="address_line2" class="block text-sm font-medium text-gray-300 mb-1">Address Line 2 <span class="text-gray-500">(optional)</span></label>
        <input type="text" name="address_line2" id="address_line2"
               value="{{ old('address_line2', $address->address_line2 ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div>
        <label for="city" class="block text-sm font-medium text-gray-300 mb-1">City</label>
        <input type="text" name="city" id="city" required
               value="{{ old('city', $address->city ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div>
        <label for="state" class="block text-sm font-medium text-gray-300 mb-1">State / Region</label>
        <input type="text" name="state" id="state" required
               value="{{ old('state', $address->state ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div>
        <label for="postal_code" class="block text-sm font-medium text-gray-300 mb-1">Postal Code</label>
        <input type="text" name="postal_code" id="postal_code" required
               value="{{ old('postal_code', $address->postal_code ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div>
        <label for="country" class="block text-sm font-medium text-gray-300 mb-1">Country</label>
        <input type="text" name="country" id="country" required
               value="{{ old('country', $address->country ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-300 mb-1">Phone</label>
        <input type="tel" name="phone" id="phone" required
               value="{{ old('phone', $address->phone ?? '') }}"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-medium">
        {{ ($address ?? null) ? 'Save Changes' : 'Add Address' }}
    </button>
    <a href="{{ route('addresses.index') }}" class="text-gray-400 hover:text-gray-300 px-4 py-2">
        Cancel
    </a>
</div>
