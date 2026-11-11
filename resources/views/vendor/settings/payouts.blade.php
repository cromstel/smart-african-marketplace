@extends('vendor.layout')

@section('title', 'Payout Settings')
@section('header', 'Payout Settings')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Stripe</h3>
        <p class="text-sm text-gray-400 mb-4">Add your Stripe connected account ID to receive Stripe payouts.</p>
        <form method="POST" action="{{ route('vendor.settings.payouts.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-gray-300 mb-2">Stripe Account ID</label>
                <input type="text" name="stripe_account_id" value="{{ old('stripe_account_id', $vendor?->stripe_account_id) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"
                       placeholder="acct_...">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Save Stripe Settings</button>
        </form>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Paystack</h3>
        <p class="text-sm text-gray-400 mb-4">Provide bank details for Paystack transfers.</p>
        <form method="POST" action="{{ route('vendor.settings.payouts.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-gray-300 mb-2">Bank Account Name</label>
                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $vendor?->bank_account_name) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Bank Account Number</label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $vendor?->bank_account_number) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Bank Code</label>
                <input type="text" name="bank_code" value="{{ old('bank_code', $vendor?->bank_code) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"
                       placeholder="e.g. 058">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Recipient Code (optional)</label>
                <input type="text" name="paystack_recipient_code" value="{{ old('paystack_recipient_code', $vendor?->paystack_recipient_code) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Save Paystack Settings</button>
        </form>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Bank Transfer</h3>
        <p class="text-sm text-gray-400 mb-4">Bank transfer details for manual payouts.</p>
        <form method="POST" action="{{ route('vendor.settings.payouts.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-gray-300 mb-2">Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $vendor?->bank_name) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Bank Account Name</label>
                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $vendor?->bank_account_name) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Bank Account Number</label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $vendor?->bank_account_number) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Save Bank Settings</button>
        </form>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Mobile Money</h3>
        <p class="text-sm text-gray-400 mb-4">Mobile money details for manual payouts.</p>
        <form method="POST" action="{{ route('vendor.settings.payouts.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-gray-300 mb-2">Provider</label>
                <input type="text" name="momo_provider" value="{{ old('momo_provider', $vendor?->momo_provider) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"
                       placeholder="MTN, Airtel">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Number</label>
                <input type="text" name="momo_number" value="{{ old('momo_number', $vendor?->momo_number) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Save Mobile Money Settings</button>
        </form>
    </div>
</div>
@endsection

