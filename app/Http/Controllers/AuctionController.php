<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Services\Auction\AuctionService;
use App\Services\Auction\BiddingService;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function __construct(
        protected AuctionService $auctionService,
        protected BiddingService $biddingService
    ) {}

    /**
     * Display a listing of active auctions.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'vendor_id', 'per_page']);

        $auctions = $this->auctionService->getActiveAuctions($filters);

        // SEO
        $seo = app(SeoService::class);
        $seo->setTitle('Live Auctions - Bid on African Products')
            ->setDescription('Discover exciting live auctions on authentic African products. Bid on unique items from verified vendors across Africa.')
            ->addStructuredData($seo->generateAuctionListSchema($auctions->items()));

        return view('auctions.index', compact('auctions'));
    }

    /**
     * Display featured auctions.
     */
    public function featured(): View
    {
        $featuredAuctions = $this->auctionService->getFeaturedAuctions();

        // SEO
        $seo = app(SeoService::class);
        $seo->setTitle('Featured Auctions')
            ->setDescription('Browse our featured auctions with special items from verified African vendors.')
            ->addStructuredData($seo->generateAuctionListSchema($featuredAuctions->items()));

        return view('auctions.featured', compact('featuredAuctions'));
    }

    /**
     * Display auctions ending soon.
     */
    public function endingSoon(): View
    {
        $auctions = $this->auctionService->getEndingSoon(24);

        // SEO
        $seo = app(SeoService::class);
        $seo->setTitle('Auctions Ending Soon')
            ->setDescription('Don\'t miss out! Bid now on auctions ending soon.')
            ->addStructuredData($seo->generateAuctionListSchema($auctions->items()));

        return view('auctions.ending-soon', compact('auctions'));
    }

    /**
     * Display the specified auction.
     */
    public function show(Auction $auction): View
    {
        $this->authorize('view', $auction);

        // Increment view count
        $auction->incrementViewCount();

        $auction->load(['vendor', 'bids.user', 'images']);

        $minimumBid = $auction->getMinimumBid();

        // Get user's bid status if authenticated
        $userBid = null;
        $isWinning = false;

        if (auth()->check()) {
            $userBid = $auction->bids()
                ->where('user_id', auth()->id())
                ->orderByDesc('amount')
                ->first();

            $isWinning = $auction->isWinning(auth()->user());
        }

        // SEO with structured data
        $seo = app(SeoService::class);
        $seo->setTitle($auction->title)
            ->setDescription($auction->description)
            ->setImage($auction->images->first()?->image_path)
            ->setCanonical(route('auctions.show', $auction->slug))
            ->setType('product')
            ->addStructuredData($seo->generateAuctionSchema($auction));

        return view('auctions.show', compact(
            'auction',
            'minimumBid',
            'userBid',
            'isWinning'
        ));
    }

    /**
     * Search auctions.
     */
    public function search(Request $request): View
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->get('q');

        $auctions = Auction::active()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['vendor'])
            ->paginate(15);

        return view('auctions.search', compact('auctions', 'query'));
    }

    /**
     * Display bid history for an auction.
     */
    public function bids(Auction $auction)
    {
        $this->authorize('viewBids', $auction);

        $bids = $this->biddingService->getBidHistory($auction);

        return response()->json([
            'bids' => $bids,
        ]);
    }

    /**
     * Place a bid on an auction.
     */
    public function bid(Request $request, Auction $auction)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'You must be logged in to place a bid.',
            ], 401);
        }

        // Check if user can bid (not the auction owner)
        if (! $auction->canBid($user)) {
            return response()->json([
                'message' => 'You cannot place a bid on this auction.',
            ], 403);
        }

        try {
            $bid = $this->biddingService->placeBid(
                $auction,
                $user,
                (float) $request->input('amount')
            );

            return response()->json([
                'message' => 'Bid placed successfully!',
                'bid' => $bid,
                'current_price' => $auction->fresh()->current_price,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
