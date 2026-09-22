@extends('admin.layout')

@section('title', 'Coupons')
@section('header', 'Coupon Management')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h3 class="text-2xl font-bold text-white">All Coupons</h3>
        <p class="text-gray-400 mt-1">Manage discount coupons and promotional codes</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Coupon
    </a>
</div>

<div class="mb-6 bg-gray-800 rounded-lg border border-gray-700 p-4">
    <form method="GET" class="flex flex-wrap gap-4">
        <div>
            <select name="vendor_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">All Vendors (Global + Vendor)</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->business_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
            Filter
        </button>
        @if(request('vendor_id'))
            <a href="{{ route('admin.coupons.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                Clear
            </a>
        @endif
    </form>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Vendor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Usage</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Valid Until</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($coupons as $coupon)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-white">{{ $coupon->code }}</div>
                        @if($coupon->description)
                            <div class="text-xs text-gray-400">{{ Str::limit($coupon->description, 30) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded {{ $coupon->type === 'fixed' ? 'bg-blue-900 text-blue-300' : 'bg-purple-900 text-purple-300' }}">
                            {{ ucfirst($coupon->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $coupon->vendor?->business_name ?? 'Global' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-semibold">
                        @if($coupon->type === 'fixed')
                            ${{ number_format($coupon->value, 2) }}
                        @else
                            {{ $coupon->value }}%
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $coupon->usage_count }}
                        @if($coupon->usage_limit)
                            / {{ $coupon->usage_limit }}
                        @else
                            / ∞
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        @if($coupon->expires_at)
                            {{ $coupon->expires_at->format('M d, Y') }}
                        @else
                            <span class="text-gray-400">No expiry</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($coupon->isValid())
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">Edit</a>
                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                        No coupons found. Create your first coupon to get started.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($coupons->hasPages())
    <div class="mt-6">
        {{ $coupons->links() }}
    </div>
@endif
@endsection

