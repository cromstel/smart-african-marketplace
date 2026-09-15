@extends('vendor.layout')

@section('title', 'Enquiry from ' . ($enquiry->user->name ?? $enquiry->name))
@section('header', 'Product Enquiry')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.enquiries.index') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h3 class="text-2xl font-bold text-white">{{ Str::limit($enquiry->subject, 60) }}</h3>
            <p class="text-gray-400 mt-1 text-sm">Received {{ $enquiry->created_at->format('M d, Y g:i A') }}</p>
        </div>
        <span class="ml-auto px-3 py-1 text-xs rounded {{
            $enquiry->status === 'closed' ? 'bg-gray-700 text-gray-400' :
            ($enquiry->status === 'responded' ? 'bg-blue-900 text-blue-300' : 'bg-yellow-900 text-yellow-300')
        }}">
            {{ ucfirst($enquiry->status) }}
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

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Product</h4>
        <div class="flex items-center gap-4">
            @if($enquiry->product->featured_image)
                <img src="{{ '/storage/' . ltrim($enquiry->product->featured_image ?? '', '/') }}"
                     alt="{{ $enquiry->product->name }}"
                     class="w-16 h-16 object-cover rounded">
            @else
                <div class="w-16 h-16 bg-gray-700 rounded flex items-center justify-center">
                    <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
            <div>
                <p class="text-white font-medium">{{ $enquiry->product->name }}</p>
                <p class="text-gray-400 text-sm">SKU: {{ $enquiry->product->sku ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Customer</h4>
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm text-gray-400">Name</dt>
                <dd class="text-white">{{ $enquiry->user->name ?? $enquiry->name }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Email</dt>
                <dd class="text-white break-all">{{ $enquiry->user->email ?? $enquiry->email }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Phone</dt>
                <dd class="text-white">{{ $enquiry->phone ?? 'N/A' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Message</h4>
        <p class="text-gray-300 whitespace-pre-line">{{ $enquiry->message }}</p>
    </div>

    @if($enquiry->response)
        <div class="bg-blue-500/10 border border-blue-500 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-blue-300 mb-2">Your Response</h4>
            <p class="text-gray-200 whitespace-pre-line">{{ $enquiry->response }}</p>
            <p class="text-blue-400/80 text-xs mt-3">
                Sent {{ $enquiry->responded_at?->format('M d, Y g:i A') }}
                @if($enquiry->respondedBy)
                    by {{ $enquiry->respondedBy->name }}
                @endif
            </p>
        </div>
    @endif

    @if($enquiry->status !== 'closed')
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">
                {{ $enquiry->response ? 'Update Your Response' : 'Respond to Customer' }}
            </h4>
            <form method="POST" action="{{ route('vendor.enquiries.respond', $enquiry) }}" id="respond-form" class="space-y-4">
                @csrf
                <div>
                    <label for="response" class="block text-sm font-medium text-gray-300 mb-2">Response *</label>
                    <textarea
                        id="response"
                        name="response"
                        rows="5"
                        required
                        maxlength="2000"
                        placeholder="Let the customer know about stock, pricing, or any other details..."
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500"
                    >{{ old('response', $enquiry->response) }}</textarea>
                </div>
            </form>
            <div class="flex gap-4 mt-4">
                <button type="submit" form="respond-form" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                    Send Response
                </button>
                <form method="POST" action="{{ route('vendor.enquiries.close', $enquiry) }}">
                    @csrf
                    <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-semibold">
                        Close Enquiry
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <p class="text-gray-400">This enquiry has been closed.</p>
        </div>
    @endif
</div>
@endsection
