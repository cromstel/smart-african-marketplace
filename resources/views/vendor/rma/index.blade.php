@extends('vendor.layout')

@section('title', 'RMA Requests')
@section('header', 'RMA Requests')

@section('content')
<div class="mb-6 flex items-center space-x-4">
    @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'completed' => 'Completed'] as $value => $label)
        <a href="{{ route('vendor.rma.index', ['status' => $value !== 'all' ? $value : null]) }}" 
           class="px-4 py-2 rounded {{ request('status') == $value || (request('status') === null && $value === 'all') ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
            {{ $label }}
            @if(isset($statusCounts[$value]))
                <span class="ml-1">({{ $statusCounts[$value] }})</span>
            @endif
        </a>
    @endforeach
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($rmas as $rma)
                <tr class="hover:bg-gray-700 {{ $rma->status === 'pending' ? 'bg-yellow-900/10' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('vendor.orders.show', $rma->order) }}" class="text-indigo-400 hover:text-indigo-300 font-medium">
                            {{ $rma->order->order_number }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            @if($rma->orderItem->product->featured_image)
                                <img src="{{ '/storage/' . ltrim($rma->orderItem->product->featured_image ?? '', '/') }}" 
                                     alt="{{ $rma->orderItem->product->name }}" 
                                     class="w-10 h-10 object-cover rounded">
                            @else
                                <div class="w-10 h-10 bg-gray-700 rounded flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <span class="text-gray-300 text-sm">{{ Str::limit($rma->orderItem->product->name, 30) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-300">
                        {{ $rma->user->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-300">
                            {{ ucfirst($rma->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                        {{ $rma->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded {{ 
                            $rma->status === 'completed' ? 'bg-green-900 text-green-300' : 
                            ($rma->status === 'rejected' ? 'bg-red-900 text-red-300' : 
                            ($rma->status === 'approved' ? 'bg-blue-900 text-blue-300' : 'bg-yellow-900 text-yellow-300')) 
                        }}">
                            {{ ucfirst($rma->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('vendor.rma.show', $rma) }}" class="text-indigo-400 hover:text-indigo-300">
                            {{ $rma->status === 'pending' ? 'Review' : 'View' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center">
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                            </svg>
                            <p class="text-gray-400">No RMA requests found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($rmas->hasPages())
    <div class="mt-6">
        {{ $rmas->links() }}
    </div>
@endif
@endsection

