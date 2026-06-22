<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Services\Auction\AuctionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function __construct(
        protected AuctionService $auctionService
    ) {}

    /**
     * Display a listing of all auctions.
     */
    public function index(Request $request): View
    {
        $query = Auction::with(['vendor', 'winner']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $auctions = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auctions.index', compact('auctions'));
    }

    /**
     * Display the specified auction.
     */
    public function show(Auction $auction): View
    {
        $auction->load(['vendor', 'bids.user', 'winner.user']);

        return view('admin.auctions.show', compact('auction'));
    }

    /**
     * Show the form for editing the specified auction.
     */
    public function edit(Auction $auction): View
    {
        $auction->load('vendor');

        return view('admin.auctions.edit', compact('auction'));
    }

    /**
     * Update the specified auction.
     */
    public function update(Request $request, Auction $auction): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'starting_price' => 'sometimes|numeric|min:0.01',
            'reserve_price' => 'nullable|numeric|min:0',
            'buy_now_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'nullable|numeric|min:0.01',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date',
            'extension_minutes' => 'nullable|integer|min:1|max:60',
            'type' => 'nullable|in:english,dutch,sealed,buy_now',
            'status' => 'sometimes|in:draft,scheduled,active,ended,cancelled,settled',
        ]);

        $auction->update($validated);

        return redirect()->route('admin.auctions.show', $auction)
            ->with('success', 'Auction updated successfully.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeature(Auction $auction): RedirectResponse
    {
        $this->authorize('feature', $auction);

        $auction = $this->auctionService->toggleFeatured($auction);

        $status = $auction->is_featured ? 'featured' : 'unfeatured';

        return back()->with('success', "Auction {$status} successfully.");
    }

    /**
     * Cancel an auction.
     */
    public function cancel(Request $request, Auction $auction): RedirectResponse
    {
        $this->authorize('cancel', $auction);

        $reason = $request->input('reason', 'Cancelled by admin');

        $auction = $this->auctionService->cancel($auction, $reason);

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction cancelled successfully.');
    }

    /**
     * Settle an auction.
     */
    public function settle(Auction $auction): RedirectResponse
    {
        $this->authorize('manage');

        $auction = $this->auctionService->settle($auction);

        return back()->with('success', 'Auction settled successfully.');
    }

    /**
     * Display auction reports.
     */
    public function reports(Request $request): View
    {
        $query = Auction::query();

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $stats = [
            'total_auctions' => $query->count(),
            'active_auctions' => (clone $query)->where('status', 'active')->count(),
            'ended_auctions' => (clone $query)->where('status', 'ended')->count(),
            'settled_auctions' => (clone $query)->where('status', 'settled')->count(),
            'total_bids' => (clone $query)->sum('total_bids'),
            'featured_auctions' => (clone $query)->where('is_featured', true)->count(),
        ];

        return view('admin.auctions.reports', compact('stats'));
    }

    /**
     * Remove the specified auction.
     */
    public function destroy(Auction $auction): RedirectResponse
    {
        $this->authorize('delete', $auction);

        $auction->delete();

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction deleted successfully.');
    }
}
