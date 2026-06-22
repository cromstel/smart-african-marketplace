<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewHelpfulVote;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $existingReview = Review::where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this product');
        }

        $order = Order::where('user_id', auth()->id())
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->where('status', 'delivered')
            ->latest()
            ->first();

        $hasPurchased = (bool) $order;

        $spamScore = $this->calculateSpamScore($validated['comment'], $validated['title'] ?? null);
        $isSpam = $spamScore >= 0.7;

        DB::transaction(function () use ($product, $validated, $hasPurchased, $order, $spamScore, $isSpam, $request, &$review) {
            $review = Review::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'order_id' => $order?->id,
                'rating' => $validated['rating'],
                'title' => $validated['title'],
                'comment' => $validated['comment'],
                'is_verified_purchase' => $hasPurchased,
                'is_approved' => false,
                'spam_score' => $spamScore,
                'is_spam' => $isSpam,
                'flagged_at' => $isSpam ? now() : null,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('review-images', 'public');
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
        });

        $this->updateProductRating($product);

        $message = $isSpam
            ? 'Thanks for your review. It is pending moderation.'
            : 'Thanks for your review. It will appear once approved.';

        return redirect()->back()->with('success', $message);
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

    public function toggleHelpful(Review $review)
    {
        if ($review->user_id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot vote on your own review.');
        }

        $existing = ReviewHelpfulVote::where('review_id', $review->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();

            return redirect()->back()->with('success', 'Helpful vote removed.');
        }

        ReviewHelpfulVote::create([
            'review_id' => $review->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Thanks for your feedback!');
    }

    public function myReviews()
    {
        $reviews = Review::where('user_id', auth()->id())
            ->with(['product', 'images'])
            ->withCount('helpfulVotes')
            ->latest()
            ->paginate(20);

        return view('account.reviews', compact('reviews'));
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

    private function calculateSpamScore(string $comment, ?string $title = null): float
    {
        $text = strtolower(trim(($title ?? '').' '.$comment));
        if ($text === '') {
            return 0;
        }

        $score = 0.0;
        $linkCount = preg_match_all('/https?:\\/\\//', $text);
        if ($linkCount > 0) {
            $score += min(0.5, 0.15 * $linkCount);
        }

        $suspicious = ['buy now', 'cheap', 'free', 'visit', 'click', 'promo', 'winner'];
        foreach ($suspicious as $keyword) {
            if (str_contains($text, $keyword)) {
                $score += 0.1;
            }
        }

        if (strlen($text) < 30) {
            $score += 0.15;
        }

        return min(1.0, $score);
    }
}
