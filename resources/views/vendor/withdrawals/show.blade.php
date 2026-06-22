@extends('vendor.layout')

@section('title', 'Withdrawal Details - ' . $withdrawal->withdrawal_number)
@section('header', 'Withdrawal Details')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('vendor.withdrawals.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h3 class="text-2xl font-bold text-white">{{ $withdrawal->withdrawal_number }}</h3>
                <p class="text-gray-400 mt-1">Withdrawal request details</p>
            </div>
        </div>
        @php
            $statusColors = [
                'pending' => 'bg-yellow-500/20 text-yellow-500 border-yellow-500',
                'processing' => 'bg-blue-500/20 text-blue-500 border-blue-500',
                'completed' => 'bg-green-500/20 text-green-500 border-green-500',
                'rejected' => 'bg-red-500/20 text-red-500 border-red-500',
            ];
        @endphp
        <span class="px-4 py-2 text-sm font-semibold rounded-lg border {{ $statusColors[$withdrawal->status] ?? '' }}">
            {{ ucfirst($withdrawal->status) }}
        </span>
    </div>

    @if($withdrawal->status === 'rejected' && $withdrawal->rejection_reason)
        <div class="bg-red-500/10 border border-red-500 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-red-500 font-semibold">Rejection Reason</p>
                    <p class="text-red-400 mt-1">{{ $withdrawal->rejection_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($withdrawal->status === 'completed')
        <div class="bg-green-500/10 border border-green-500 text-green-500 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>This withdrawal was completed on {{ $withdrawal->processed_at->format('F d, Y \a\t h:i A') }}</span>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Financial Summary</h4>
        <div class="space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-gray-700">
                <span class="text-gray-400">Withdrawal Amount</span>
                <span class="text-2xl font-bold text-white">${{ number_format($withdrawal->amount, 2) }}</span>
            </div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-700">
                <span class="text-gray-400">Processing Fee ({{ App\Models\Setting::where('key', 'withdrawal_fee_percentage')->value('value') ?? 2 }}%)</span>
                <span class="text-lg font-semibold text-red-500">-${{ number_format($withdrawal->fee, 2) }}</span>
            </div>
            <div class="flex justify-between items-center pt-2">
                <span class="text-white font-semibold">Net Amount (You Receive)</span>
                <span class="text-3xl font-bold text-green-500">${{ number_format($withdrawal->net_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Payment Information</h4>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-400">Payment Method</span>
                <span class="text-white font-semibold">{{ ucfirst(str_replace('_', ' ', $withdrawal->method)) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Account Name</span>
                <span class="text-white">{{ $withdrawal->account_details['account_name'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Account Number</span>
                <span class="text-white font-mono">{{ $withdrawal->account_details['account_number'] ?? 'N/A' }}</span>
            </div>
            @if(isset($withdrawal->account_details['bank_name']))
                <div class="flex justify-between">
                    <span class="text-gray-400">Bank Name</span>
                    <span class="text-white">{{ $withdrawal->account_details['bank_name'] }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Request Details</h4>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-400">Request Date</span>
                <span class="text-white">{{ $withdrawal->created_at->format('F d, Y \a\t h:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Status</span>
                <span class="px-3 py-1 text-xs font-semibold rounded-full border {{ $statusColors[$withdrawal->status] ?? '' }}">
                    {{ ucfirst($withdrawal->status) }}
                </span>
            </div>
            @if($withdrawal->processed_at)
                <div class="flex justify-between">
                    <span class="text-gray-400">Processed Date</span>
                    <span class="text-white">{{ $withdrawal->processed_at->format('F d, Y \a\t h:i A') }}</span>
                </div>
            @endif
            @if($withdrawal->notes)
                <div class="pt-3 mt-3 border-t border-gray-700">
                    <p class="text-gray-400 text-sm mb-2">Your Notes</p>
                    <p class="text-gray-300">{{ $withdrawal->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($withdrawal->status === 'pending')
        <div class="bg-blue-500/10 border border-blue-500 text-blue-400 px-4 py-3 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold">Withdrawal Pending</p>
                    <p class="text-sm mt-1">Your withdrawal request is being reviewed by our team. Processing typically takes {{ App\Models\Setting::where('key', 'withdrawal_processing_days')->value('value') ?? 3 }} business days.</p>
                </div>
            </div>
        </div>
    @endif

    @if($withdrawal->status === 'processing')
        <div class="bg-indigo-500/10 border border-indigo-500 text-indigo-400 px-4 py-3 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <div>
                    <p class="font-semibold">Processing in Progress</p>
                    <p class="text-sm mt-1">Your withdrawal is currently being processed. Funds will be transferred to your account soon.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

