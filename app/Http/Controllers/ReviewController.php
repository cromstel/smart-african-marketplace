<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        $existingReview = Review::where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this product');
        }

        $hasPurchased = Order::where('user_id', auth()->id())
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->where('status', 'delivered')
            ->exists();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => $hasPurchased,
            'is_approved' => true,
        ]);

        $this->updateProductRating($product);

        return redirect()->back()->with('success', 'Thank you for your review!');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $product = $review->product;
        $review->delete();

        $this->updateProductRating($product);

        return redirect()->back()->with('success', 'Review deleted successfully');
    }

    private function updateProductRating(Product $product)
    {
        $averageRating = $product->reviews()->approved()->avg('rating');
        $reviewsCount = $product->reviews()->approved()->count();

        $product->update([
            'average_rating' => $averageRating ? round($averageRating, 2) : 0,
            'reviews_count' => $reviewsCount,
        ]);
    }
}
