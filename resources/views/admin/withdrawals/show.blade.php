@extends('admin.layout')

@section('title', 'Withdrawal Details - ' . $withdrawal->withdrawal_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.withdrawals.index') }}" class="text-gray-400 hover:text-white transition" aria-label="Back">
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
                'processing' => 'bg-indigo-500/20 text-indigo-500 border-indigo-500',
                'completed' => 'bg-green-500/20 text-green-500 border-green-500',
                'rejected' => 'bg-red-500/20 text-red-500 border-red-500',
            ];
        @endphp
        <span class="px-4 py-2 text-sm font-semibold rounded-lg border {{ $statusColors[$withdrawal->status] ?? '' }}">
            {{ ucfirst($withdrawal->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Financial Details</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-700">
                        <span class="text-gray-400">Withdrawal Amount</span>
                        <span class="text-2xl font-bold text-white">${{ number_format($withdrawal->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-gray-700">
                        <span class="text-gray-400">Processing Fee</span>
                        <span class="text-lg font-semibold text-red-500">-${{ number_format($withdrawal->fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-white font-semibold">Net Amount (Vendor Receives)</span>
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

            @if($withdrawal->notes || $withdrawal->rejection_reason)
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h4 class="text-lg font-semibold text-white mb-4">Notes</h4>
                    @if($withdrawal->notes)
                        <div class="mb-4">
                            <p class="text-gray-400 text-sm mb-2">Vendor Notes</p>
                            <p class="text-gray-300">{{ $withdrawal->notes }}</p>
                        </div>
                    @endif
                    @if($withdrawal->rejection_reason)
                        <div class="bg-red-500/10 border border-red-500 rounded-lg p-4">
                            <p class="text-red-500 font-semibold mb-2">Rejection Reason</p>
                            <p class="text-red-400">{{ $withdrawal->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Vendor Information</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-400 text-sm">Name</p>
                        <p class="text-white">{{ $withdrawal->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Email</p>
                        <p class="text-white">{{ $withdrawal->user->email }}</p>
                    </div>
                    @if($withdrawal->user->phone)
                        <div>
                            <p class="text-gray-400 text-sm">Phone</p>
                            <p class="text-white">{{ $withdrawal->user->phone }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Request Timeline</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Requested</span>
                        <span class="text-white text-sm">{{ $withdrawal->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @if($withdrawal->processed_at)
                        <div class="flex justify-between">
                            <span class="text-gray-400">Processed</span>
                            <span class="text-white text-sm">{{ $withdrawal->processed_at->format('M d, Y h:i A') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-400">Status</span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full border {{ $statusColors[$withdrawal->status] ?? '' }}">
                            {{ ucfirst($withdrawal->status) }}
                        </span>
                    </div>
                </div>
            </div>

            @if($withdrawal->status === 'pending')
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h4 class="text-lg font-semibold text-white mb-4">Actions</h4>
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                            @csrf
                            <textarea name="notes" rows="2" placeholder="Add notes (optional)..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500 mb-3"></textarea>
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                                Approve & Start Processing
                            </button>
                        </form>

                        <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                            Reject Request
                        </button>
                    </div>
                </div>
            @endif

            @if(in_array($withdrawal->status, ['pending', 'processing']))
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h4 class="text-lg font-semibold text-white mb-4">Mark as Completed</h4>
                    <form method="POST" action="{{ route('admin.withdrawals.complete', $withdrawal) }}">
                        @csrf
                        <textarea name="notes" rows="2" placeholder="Confirmation notes (optional)..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500 mb-3"></textarea>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                            Mark as Completed
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<div id="reject-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-lg border border-gray-700 max-w-md w-full p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Reject Withdrawal Request</h4>
        <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Rejection Reason *</label>
                <textarea name="rejection_reason" rows="4" required placeholder="Explain why this request is being rejected..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-orange-500"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                    Confirm Rejection
                </button>
                <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

