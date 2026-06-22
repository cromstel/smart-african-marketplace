@extends('admin.layout')

@section('title', 'Product Reviews')
@section('header', 'Product Reviews')

@section('content')
<div class="space-y-6">
    <form method="GET" class="bg-gray-800 border border-gray-700 rounded-xl p-4">
        <div class="flex flex-wrap items-center gap-3">
            <select name="vendor_id" class="w-full md:w-56 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
            <option value="">All vendors</option>
            @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}" {{ (string) ($filters['vendor_id'] ?? '') === (string) $vendor->id ? 'selected' : '' }}>
                    {{ $vendor->business_name ?: $vendor->user?->name }}
                </option>
            @endforeach
            </select>
            <input type="number" name="product_id" value="{{ $filters['product_id'] ?? '' }}" placeholder="Product ID" class="w-full md:w-32 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
            <select name="rating" class="w-full md:w-36 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                <option value="">All ratings</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ (string) ($filters['rating'] ?? '') === (string) $i ? 'selected' : '' }}>{{ $i }} stars</option>
                @endfor
            </select>
            <select name="status" class="w-full md:w-36 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                <option value="">All statuses</option>
                <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
            <select name="is_spam" class="w-full md:w-32 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                <option value="">Spam?</option>
                <option value="1" {{ ($filters['is_spam'] ?? '') === '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ ($filters['is_spam'] ?? '') === '0' ? 'selected' : '' }}>No</option>
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full md:w-40 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full md:w-40 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search reviews, products, customers..." class="w-full md:w-72 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
            <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Filter</button>
            <a href="{{ route('admin.reviews.index') }}" class="px-4 py-2 rounded bg-gray-900 text-gray-200 hover:bg-gray-700">Reset</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.reviews.bulk') }}" class="space-y-3">
        @csrf
        <div class="flex flex-wrap items-center gap-3">
            <select name="action" class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white">
                <option value="">Bulk Actions</option>
                <option value="approve">Approve</option>
                <option value="reject">Unapprove</option>
                <option value="mark_spam">Mark as Spam</option>
                <option value="unmark_spam">Unmark Spam</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded bg-gray-900 text-gray-200 hover:bg-gray-700" onclick="return confirm('Apply action to selected reviews?')">Apply</button>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">
                        <input type="checkbox" onclick="document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = this.checked)">
                    </th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Product</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Vendor</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Customer</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Rating</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Date</th>
                    <th class="px-4 py-3 text-right text-xs uppercase tracking-wider text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($reviews as $review)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="reviews[]" value="{{ $review->id }}" class="review-checkbox">
                        </td>
                        <td class="px-4 py-3 text-white">
                            <div class="font-medium">{{ $review->product?->name }}</div>
                            <div class="text-xs text-gray-400">#{{ $review->product_id }}</div>
                            @if($review->title)
                                <div class="text-sm text-gray-300 mt-1">{{ $review->title }}</div>
                            @endif
                            <div class="text-xs text-gray-400 mt-1">{{ \Illuminate\Support\Str::limit($review->comment, 90) }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $review->product?->vendor?->business_name ?: $review->product?->vendor?->user?->name ?: 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $review->user?->name ?: 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $review->rating }}/5</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $review->is_approved ? 'bg-green-900 text-green-300' : 'bg-yellow-900 text-yellow-300' }}">
                                {{ $review->is_approved ? 'Approved' : 'Pending' }}
                            </span>
                            @if($review->is_spam)
                                <span class="ml-2 px-2 py-1 rounded text-xs bg-red-900 text-red-300">Spam</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $review->created_at?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-3 whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="is_approved" value="{{ $review->is_approved ? 0 : 1 }}">
                                    <button type="submit" class="text-indigo-400 hover:text-indigo-300">{{ $review->is_approved ? 'Unapprove' : 'Approve' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No reviews found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </form>

    {{ $reviews->links() }}
</div>
@endsection

