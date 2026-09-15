<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Vendor;
use App\Services\Auction\AuctionService;
use App\Services\Auction\BiddingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function __construct(
        protected AuctionService $auctionService,
        protected BiddingService $biddingService
    ) {}

    /**
     * Get the current user's vendor or redirect.
     */
    protected function getVendor(): ?Vendor
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $vendor = $user->vendor;

        if (! $vendor) {
            return null;
        }

        if (! $vendor->isApproved()) {
            return null;
        }

        return $vendor;
    }

    /**
     * Display a listing of the vendor's auctions.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            return $this->handleNoVendor();
        }

        $auctions = Auction::forVendor($vendor->id)
            ->with(['bids', 'winner'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('vendor.auctions.index', compact('auctions'));
    }

    /**
     * Handle case when vendor is not available.
     */
    protected function handleNoVendor(): RedirectResponse
    {
        return redirect()->route('dashboard')
            ->with('error', 'You need to set up a vendor store first.');
    }

    /**
     * Show the form for creating a new auction.
     */
    public function create(): View
    {
        $this->authorize('create', Auction::class);

        return view('vendor.auctions.create');
    }

    /**
     * Store a newly created auction.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Auction::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starting_price' => 'required|numeric|min:0.01',
            'reserve_price' => 'nullable|numeric|min:0',
            'buy_now_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'nullable|numeric|min:0.01',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'extension_minutes' => 'nullable|integer|min:1|max:60',
            'type' => 'nullable|in:english,dutch,sealed,buy_now',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $vendor = $this->getVendor();

        if (! $vendor) {
            return $this->handleNoVendor();
        }

        $auction = $this->auctionService->create($validated, $vendor);

        return redirect()->route('vendor.auctions.show', $auction)
            ->with('success', 'Auction created successfully.');
    }

    /**
     * Display the specified auction.
     */
    public function show(Auction $auction): View
    {
        // Not 'update': that ability deliberately excludes active/ended
        // auctions (a vendor can no longer edit the terms once bidding has
        // started), which would also lock the vendor out of viewing their
        // own auction's bid history and status for most of its lifecycle.
        // Not the public 'view' ability either: that lets any vendor browse
        // another vendor's active auction (so they can bid on it), which
        // must not extend to this private management page.
        $this->authorize('viewOwn', $auction);

        $auction->load(['bids.user', 'winner.user']);

        return view('vendor.auctions.show', compact('auction'));
    }

    /**
     * Show the form for editing the specified auction.
     */
    public function edit(Auction $auction): View
    {
        $this->authorize('update', $auction);

        return view('vendor.auctions.edit', compact('auction'));
    }

    /**
     * Update the specified auction.
     */
    public function update(Request $request, Auction $auction): RedirectResponse
    {
        $this->authorize('update', $auction);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'starting_price' => 'sometimes|numeric|min:0.01',
            'reserve_price' => 'nullable|numeric|min:0',
            'buy_now_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'nullable|numeric|min:0.01',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'extension_minutes' => 'nullable|integer|min:1|max:60',
            'type' => 'nullable|in:english,dutch,sealed,buy_now',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $auction = $this->auctionService->update($auction, $validated);

        return redirect()->route('vendor.auctions.show', $auction)
            ->with('success', 'Auction updated successfully.');
    }

    /**
     * Publish/schedule the auction.
     */
    public function publish(Auction $auction): RedirectResponse
    {
        $this->authorize('update', $auction);

        $auction = $this->auctionService->schedule($auction);

        return back()->with('success', 'Auction published successfully.');
    }

    /**
     * Cancel the auction.
     */
    public function cancel(Request $request, Auction $auction): RedirectResponse
    {
        $this->authorize('cancel', $auction);

        $reason = $request->input('reason', '');

        $auction = $this->auctionService->cancel($auction, $reason);

        return redirect()->route('vendor.auctions.index')
            ->with('success', 'Auction cancelled successfully.');
    }

    /**
     * Extend the auction.
     */
    public function extend(Request $request, Auction $auction): RedirectResponse
    {
        $this->authorize('update', $auction);

        $minutes = $request->input('minutes', 60);

        $auction = $this->auctionService->extend($auction, $minutes);

        return back()->with('success', "Auction extended by {$minutes} minutes.");
    }

    /**
     * Settle the auction (determine winner).
     */
    public function settle(Auction $auction): RedirectResponse
    {
        $this->authorize('settle', $auction);

        $auction = $this->auctionService->settle($auction);

        return back()->with('success', 'Auction settled. Winner has been notified.');
    }

    /**
     * Display bids for an auction.
     */
    public function bids(Auction $auction): View
    {
        $this->authorize('viewBids', $auction);

        $bids = $this->biddingService->getBidHistory($auction);

        return view('vendor.auctions.bids', compact('auction', 'bids'));
    }

    /**
     * Remove the specified auction.
     */
    public function destroy(Auction $auction): RedirectResponse
    {
        $this->authorize('delete', $auction);

        $auction->delete();

        return redirect()->route('vendor.auctions.index')
            ->with('success', 'Auction deleted successfully.');
    }
}
