@extends('vendor.layout')

@section('title', 'Refund Details')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Refund Details</h2>
    <p class="text-gray-400 mt-1">Order #{{ $refund->order->order_number }}</p>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
    <p class="text-gray-400">Customer: {{ $refund->user->email }}</p>
    <p class="text-gray-400">Status: {{ ucfirst($refund->status) }}</p>
    <p class="text-gray-400">Vendor Response: {{ ucfirst($refund->vendor_status ?? 'pending') }}</p>
    <div class="mt-4">
        <h4 class="text-white font-semibold mb-2">Reason</h4>
        <p class="text-gray-300">{{ $refund->reason }}</p>
    </div>

    @if($refund->status === 'pending' && ($refund->vendor_status ?? 'pending') === 'pending')
        <form method="POST" action="{{ route('vendor.refunds.approve', $refund) }}" class="mt-6 space-y-3">
            @csrf
            <label class="block text-sm text-gray-300">Vendor Notes (optional)</label>
            <textarea name="vendor_notes" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white"></textarea>
            <div class="flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Approve Refund</button>
                <button type="submit" formaction="{{ route('vendor.refunds.reject', $refund) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Reject Refund</button>
            </div>
        </form>
    @endif
</div>
@endsection

