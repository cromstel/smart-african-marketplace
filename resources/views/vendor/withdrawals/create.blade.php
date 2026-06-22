@extends('vendor.layout')

@section('title', 'Request Withdrawal')
@section('header', 'Request Withdrawal')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.withdrawals.index') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h3 class="text-2xl font-bold text-white">Request Withdrawal</h3>
            <p class="text-gray-400 mt-1">Withdraw your earnings to your account</p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500 rounded-lg p-4">
            <p class="text-red-500 font-semibold mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-red-400">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Available Balance</h4>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-700/30 rounded-lg p-4">
                <p class="text-gray-400 text-sm">Total Earnings</p>
                <p class="text-2xl font-bold text-white mt-1">${{ number_format($stats['total_earnings'], 2) }}</p>
            </div>
            <div class="bg-gray-700/30 rounded-lg p-4">
                <p class="text-gray-400 text-sm">Platform Commission</p>
                <p class="text-2xl font-bold text-red-500 mt-1">${{ number_format($stats['platform_commission'], 2) }}</p>
            </div>
            <div class="bg-gray-700/30 rounded-lg p-4">
                <p class="text-gray-400 text-sm">Already Withdrawn</p>
                <p class="text-2xl font-bold text-yellow-500 mt-1">${{ number_format($stats['total_withdrawn'], 2) }}</p>
            </div>
            <div class="bg-green-500/20 rounded-lg p-4 border border-green-500">
                <p class="text-green-400 text-sm">Available to Withdraw</p>
                <p class="text-3xl font-bold text-green-500 mt-1">${{ number_format($stats['available_balance'], 2) }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('vendor.withdrawals.store') }}" class="space-y-6">
        @csrf

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Withdrawal Details</h4>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Withdrawal Amount *
                        <span class="text-gray-400 font-normal">(Min: ${{ number_format($minimumAmount, 2) }})</span>
                    </label>
                    <input 
                        type="number" 
                        name="amount" 
                        step="0.01" 
                        min="{{ $minimumAmount }}" 
                        max="{{ $stats['available_balance'] }}"
                        value="{{ old('amount') }}" 
                        required 
                        placeholder="Enter amount to withdraw"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                    >
                    <p class="text-gray-400 text-sm mt-1">Maximum available: ${{ number_format($stats['available_balance'], 2) }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Payment Method *</label>
                    <select name="method" id="payment-method" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-orange-500">
                        <option value="">Select payment method...</option>
                        <option value="bank_transfer" {{ old('method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="stripe" {{ old('method') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                        <option value="paystack" {{ old('method') == 'paystack' ? 'selected' : '' }}>Paystack</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Account Information</h4>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Account Name *</label>
                    <input 
                        type="text" 
                        name="account_name" 
                        value="{{ old('account_name') }}" 
                        required 
                        placeholder="Full name on account"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Account Number / Email *</label>
                    <input 
                        type="text" 
                        name="account_number" 
                        value="{{ old('account_number') }}" 
                        required 
                        placeholder="Account number or email"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                    >
                </div>

                <div id="bank-fields" style="display: none;">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bank Name *</label>
                    <input 
                        type="text" 
                        name="bank_name" 
                        value="{{ old('bank_name') }}" 
                        placeholder="Name of your bank"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Additional Notes (Optional)</label>
                    <textarea 
                        name="notes" 
                        rows="3" 
                        placeholder="Any special instructions or notes..."
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-blue-500/10 border border-blue-500 text-blue-400 px-4 py-3 rounded-lg">
            <p class="text-sm">
                <strong>Note:</strong> A withdrawal fee of {{ App\Models\Setting::where('key', 'withdrawal_fee_percentage')->value('value') ?? 2 }}% 
                will be deducted from your withdrawal amount. Processing typically takes 
                {{ App\Models\Setting::where('key', 'withdrawal_processing_days')->value('value') ?? 3 }} business days.
            </p>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-lg transition font-semibold">
                Submit Withdrawal Request
            </button>
            <a href="{{ route('vendor.withdrawals.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('payment-method').addEventListener('change', function() {
    const bankFields = document.getElementById('bank-fields');
    const bankNameInput = document.querySelector('input[name="bank_name"]');
    
    if (this.value === 'bank_transfer') {
        bankFields.style.display = 'block';
        bankNameInput.required = true;
    } else {
        bankFields.style.display = 'none';
        bankNameInput.required = false;
    }
});

if (document.getElementById('payment-method').value === 'bank_transfer') {
    document.getElementById('bank-fields').style.display = 'block';
    document.querySelector('input[name="bank_name"]').required = true;
}
</script>
@endsection

