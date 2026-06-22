<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::query()->with(['product.vendor.user', 'user']);

        if ($request->filled('vendor_id')) {
            $query->whereHas('product', fn ($q) => $q->where('vendor_id', (int) $request->integer('vendor_id')));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', (int) $request->integer('product_id'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->integer('rating'));
        }

        if ($request->filled('status')) {
            if ($request->string('status') === 'approved') {
                $query->where('is_approved', true);
            }

            if ($request->string('status') === 'pending') {
                $query->where('is_approved', false);
            }
        }

        if ($request->filled('is_spam')) {
            $query->where('is_spam', (bool) $request->boolean('is_spam'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from')->toDateString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to')->toDateString());
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        $vendors = Vendor::query()->with('user')->orderBy('business_name')->get(['id', 'user_id', 'business_name']);

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'vendors' => $vendors,
            'filters' => $request->only(['vendor_id', 'product_id', 'rating', 'status', 'date_from', 'date_to', 'search', 'is_spam']),
        ]);
    }

    public function updateStatus(Request $request, Review $review)
    {
        $validated = $request->validate([
            'is_approved' => ['required', 'boolean'],
        ]);

        $review->update([
            'is_approved' => (bool) $validated['is_approved'],
            'approved_at' => (bool) $validated['is_approved'] ? now() : null,
        ]);

        $this->refreshProductReviewStats($review);

        return redirect()->route('admin.reviews.index')->with('success', 'Review status updated successfully.');
    }

    public function destroy(Review $review)
    {
        $product = $review->product;
        $review->delete();

        if ($product) {
            $this->refreshProductReviewStatsForProduct($product);
        }

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject,mark_spam,unmark_spam,delete',
            'reviews' => 'required|array',
            'reviews.*' => 'integer|exists:reviews,id',
        ]);

        $reviews = Review::whereIn('id', $validated['reviews'])->get();

        foreach ($reviews as $review) {
            switch ($validated['action']) {
                case 'approve':
                    $review->update(['is_approved' => true, 'approved_at' => now()]);
                    $this->refreshProductReviewStats($review);
                    break;
                case 'reject':
                    $review->update(['is_approved' => false, 'approved_at' => null]);
                    $this->refreshProductReviewStats($review);
                    break;
                case 'mark_spam':
                    $review->update(['is_spam' => true, 'flagged_at' => now()]);
                    break;
                case 'unmark_spam':
                    $review->update(['is_spam' => false, 'flagged_at' => null]);
                    break;
                case 'delete':
                    $product = $review->product;
                    $review->delete();
                    if ($product) {
                        $this->refreshProductReviewStatsForProduct($product);
                    }
                    break;
            }
        }

        return redirect()->route('admin.reviews.index')->with('success', 'Bulk action completed.');
    }

    private function refreshProductReviewStats(Review $review): void
    {
        $product = $review->product;
        if (! $product) {
            return;
        }

        $this->refreshProductReviewStatsForProduct($product);
    }

    private function refreshProductReviewStatsForProduct(Product $product): void
    {
        $averageRating = $product->reviews()->approved()->avg('rating');
        $reviewsCount = $product->reviews()->approved()->count();

        $product->update([
            'average_rating' => $averageRating ? round((float) $averageRating, 2) : 0,
            'reviews_count' => $reviewsCount,
        ]);
    }
}
