@extends('vendor.layout')

@section('title', 'Request Payout')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Request Payout</h2>
</div>

<form method="POST" action="{{ route('vendor.payouts.store') }}" class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
    @csrf
    <div>
        <label class="block text-sm text-gray-300 mb-2" for="amount">Amount</label>
        <input type="number" step="0.01" min="1" name="amount" id="amount" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white" required>
    </div>
    <div>
        <label class="block text-sm text-gray-300 mb-2" for="method">Method</label>
        <select name="method" id="method" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white" required>
            <option value="stripe">Stripe</option>
            <option value="paystack">Paystack</option>
            <option value="momo">Mobile Money</option>
            <option value="bank">Bank Transfer</option>
        </select>
    </div>
    <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 text-sm text-gray-300">
        Manage payout destination details in
        <a href="{{ route('vendor.settings.payouts.edit') }}" class="text-indigo-400 hover:text-indigo-300">Payout Settings</a>.
    </div>
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Submit</button>
</form>
@endsection

