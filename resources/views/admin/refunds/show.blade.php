@extends('admin.layout')

@section('title', 'Refund Details')
@section('header', 'Refund Details')

@section('content')
<div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
    <h3 class="text-xl font-semibold text-white mb-2">Order #{{ $refund->order->order_number }}</h3>
    <p class="text-gray-400">Requested by: {{ $refund->user->email }}</p>
    <p class="text-gray-400">Status: {{ ucfirst($refund->status) }}</p>
    <p class="text-gray-400">Vendor Response: {{ ucfirst($refund->vendor_status ?? 'pending') }}</p>

    <div class="mt-4">
        <h4 class="text-white font-semibold mb-2">Reason</h4>
        <p class="text-gray-300">{{ $refund->reason }}</p>
    </div>

    @if($refund->resolution_notes)
        <div class="mt-4">
            <h4 class="text-white font-semibold mb-2">Resolution Notes</h4>
            <p class="text-gray-300">{{ $refund->resolution_notes }}</p>
        </div>
    @endif

    @if($refund->vendor_notes)
        <div class="mt-4">
            <h4 class="text-white font-semibold mb-2">Vendor Notes</h4>
            <p class="text-gray-300">{{ $refund->vendor_notes }}</p>
        </div>
    @endif

    <div class="mt-6 flex flex-wrap gap-3">
        @if($refund->status === 'pending')
            <form method="POST" action="{{ route('admin.refunds.approve', $refund) }}">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Approve</button>
            </form>
            <form method="POST" action="{{ route('admin.refunds.reject', $refund) }}">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Reject</button>
            </form>
        @endif
        @if(in_array($refund->status, ['approved', 'pending']))
            <form method="POST" action="{{ route('admin.refunds.refunded', $refund) }}">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Mark Refunded</button>
            </form>
        @endif
    </div>
</div>
@endsection

