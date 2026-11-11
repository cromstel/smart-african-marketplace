@extends('vendor.layout')

@section('title', 'Coupons')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-white">Coupons</h2>
        <p class="text-gray-400 mt-1">Create coupons for your products.</p>
    </div>
    <a href="{{ route('vendor.coupons.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">New Coupon</a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700">
    <div class="divide-y divide-gray-700">
        @forelse($coupons as $coupon)
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-gray-300">{{ $coupon->code }}</div>
                    <div class="text-xs text-gray-400">{{ ucfirst($coupon->type) }} · {{ $coupon->value }}</div>
                </div>
                <div class="text-sm text-gray-400">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</div>
                <div class="flex gap-2">
                    <a href="{{ route('vendor.coupons.edit', $coupon) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                    <form method="POST" action="{{ route('vendor.coupons.destroy', $coupon) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="px-4 py-6 text-gray-400">No coupons yet.</div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    {{ $coupons->links() }}
</div>
@endsection

