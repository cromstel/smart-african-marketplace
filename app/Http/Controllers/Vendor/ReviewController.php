<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth()->user()->vendor->id;

        $query = Review::whereHas('product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with(['product', 'user'])->withCount('helpfulVotes');

        if ($request->has('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'rating_high':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'rating_low':
                    $query->orderBy('rating', 'asc');
                    break;
                case 'helpful':
                    $query->orderBy('helpful_votes_count', 'desc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        $reviews = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Review::whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId))->count(),
            'approved' => Review::whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId))->where('is_approved', true)->count(),
            'pending' => Review::whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId))->where('is_approved', false)->count(),
            'average_rating' => Review::whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId))->avg('rating'),
        ];

        return view('vendor.reviews.index', compact('reviews', 'stats'));
    }

    public function show(Review $review)
    {
        if ($review->product->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('vendor.reviews.show', compact('review'));
    }
}
