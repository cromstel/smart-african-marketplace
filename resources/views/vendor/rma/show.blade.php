@extends('vendor.layout')

@section('title', 'RMA ' . $rma->rma_number)
@section('header', 'RMA Request')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.rma.index') }}" class="text-gray-400 hover:text-white transition" aria-label="Back">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h3 class="text-2xl font-bold text-white font-mono">{{ $rma->rma_number }}</h3>
            <p class="text-gray-400 mt-1 text-sm">Submitted {{ $rma->created_at->format('M d, Y g:i A') }}</p>
        </div>
        <span class="ml-auto px-3 py-1 text-xs rounded {{
            $rma->status === 'completed' ? 'bg-green-900 text-green-300' :
            ($rma->status === 'rejected' ? 'bg-red-900 text-red-300' :
            ($rma->status === 'approved' ? 'bg-blue-900 text-blue-300' :
            ($rma->status === 'processing' ? 'bg-purple-900 text-purple-300' : 'bg-yellow-900 text-yellow-300')))
        }}">
            {{ ucfirst($rma->status) }}
        </span>
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

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Order</h4>
            <dl class="space-y-2">
                <div>
                    <dt class="text-sm text-gray-400">Order Number</dt>
                    <dd>
                        <a href="{{ route('vendor.orders.show', $rma->order) }}" class="text-indigo-400 hover:text-indigo-300 font-medium">
                            {{ $rma->order->order_number }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-400">Order Date</dt>
                    <dd class="text-white">{{ $rma->order->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Customer</h4>
            <dl class="space-y-2">
                <div>
                    <dt class="text-sm text-gray-400">Name</dt>
                    <dd class="text-white">{{ $rma->user->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-400">Email</dt>
                    <dd class="text-white break-all">{{ $rma->user->email }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Item</h4>
        <div class="flex items-center gap-4">
            @if($rma->orderItem->product->featured_image)
                <img src="{{ '/storage/' . ltrim($rma->orderItem->product->featured_image ?? '', '/') }}"
                     alt="{{ $rma->orderItem->product->name }}"
                     class="w-16 h-16 object-cover rounded">
            @else
                <div class="w-16 h-16 bg-gray-700 rounded flex items-center justify-center">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
            <div>
                <p class="text-white font-medium">{{ $rma->orderItem->product->name }}</p>
                <p class="text-gray-400 text-sm">Qty: {{ $rma->orderItem->quantity }} &middot; ${{ number_format($rma->orderItem->price, 2) }} each</p>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Request Details</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <dt class="text-sm text-gray-400">Type</dt>
                <dd class="text-white">{{ ucfirst($rma->type) }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Reason</dt>
                <dd class="text-white">{{ ucfirst(str_replace('_', ' ', $rma->reason)) }}</dd>
            </div>
        </dl>
        <dt class="text-sm text-gray-400 mb-1">Customer Description</dt>
        <dd class="text-gray-300 whitespace-pre-line">{{ $rma->description }}</dd>

        @if(!empty($rma->images))
            <div class="mt-4">
                <dt class="text-sm text-gray-400 mb-2">Attached Photos</dt>
                <div class="flex flex-wrap gap-3">
                    @foreach($rma->images as $image)
                        <a href="{{ '/storage/' . ltrim($image, '/') }}" target="_blank" rel="noopener">
                            <img src="{{ '/storage/' . ltrim($image, '/') }}" alt="RMA photo" class="w-20 h-20 object-cover rounded border border-gray-600">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($rma->vendor_response)
        <div class="bg-blue-500/10 border border-blue-500 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-blue-300 mb-2">Your Decision</h4>
            <p class="text-gray-200 whitespace-pre-line">{{ $rma->vendor_response }}</p>
            @if($rma->refund_issued)
                <p class="text-green-400 text-sm mt-3">Refund issued: ${{ number_format($rma->refund_amount, 2) }}</p>
            @endif
        </div>
    @endif

    @if(!in_array($rma->status, ['completed', 'rejected']))
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">
                {{ $rma->vendor_response ? 'Update Decision' : 'Decide on This Request' }}
            </h4>
            <form method="POST" action="{{ route('vendor.rma.updateStatus', $rma) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Decision *</label>
                    <select id="status" name="status" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                        <option value="">Select a decision...</option>
                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approve</option>
                        <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Mark as Processing</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Mark as Completed</option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Reject</option>
                    </select>
                </div>
                <div>
                    <label for="response" class="block text-sm font-medium text-gray-300 mb-2">Response to Customer *</label>
                    <textarea
                        id="response"
                        name="response"
                        rows="4"
                        required
                        maxlength="1000"
                        placeholder="Explain your decision to the customer..."
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500"
                    >{{ old('response', $rma->vendor_response) }}</textarea>
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                    Submit Decision
                </button>
            </form>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <p class="text-gray-400">This request has been {{ $rma->status }} and can no longer be changed.</p>
        </div>
    @endif
</div>
@endsection
