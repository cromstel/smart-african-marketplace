<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;

class AuctionPolicy
{
    /**
     * Determine whether the user can view any auctions.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor()) {
            return true;
        }

        if ($user->isCustomer()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the auction's vendor-management
     * detail page (status, bid history, settlement controls).
     *
     * Deliberately distinct from view(): that ability also lets any
     * authenticated visitor -- vendors included, since a vendor may want to
     * browse and bid on another vendor's auction -- see the public page for
     * an active/ended/settled auction. This one must never fall through to
     * that public check, or a vendor could open another vendor's private
     * management page for any auction that happens to be active.
     */
    public function viewOwn(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isVendor() && $user->vendor && $auction->vendor_id === $user->vendor->id;
    }

    /**
     * Determine whether the user can view the auction.
     */
    public function view(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor) {
            if ($auction->vendor_id === $user->vendor->id) {
                return true;
            }
        }

        // Anyone can view active/ended/settled auctions
        return in_array($auction->status, ['active', 'ended', 'settled']);
    }

    /**
     * Determine whether the user can create an auction.
     */
    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor && $user->vendor->isApproved()) {
            return $user->hasModuleAccess('auctions');
        }

        return false;
    }

    /**
     * Determine whether the user can update the auction.
     */
    public function update(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor) {
            // Vendor can only update their own auctions that are not active/ended
            return $auction->vendor_id === $user->vendor->id
                && in_array($auction->status, ['draft', 'scheduled']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the auction.
     */
    public function delete(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor) {
            // Vendor can only delete their own auctions that are draft
            return $auction->vendor_id === $user->vendor->id
                && $auction->status === 'draft';
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the auction.
     */
    public function cancel(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor) {
            return $auction->vendor_id === $user->vendor->id
                && in_array($auction->status, ['draft', 'scheduled', 'active']);
        }

        return false;
    }

    /**
     * Determine whether the user can manage (full control) the auction.
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can feature/unfeature the auction.
     */
    public function feature(User $user, Auction $auction): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view bids for an auction.
     */
    public function viewBids(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor) {
            return $auction->vendor_id === $user->vendor->id;
        }

        // Users can only see public bid history for active/ended auctions
        return in_array($auction->status, ['active', 'ended', 'settled']);
    }

    /**
     * Determine whether the user can settle an auction.
     */
    public function settle(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor) {
            return $auction->vendor_id === $user->vendor->id
                && $auction->status === 'ended';
        }

        return false;
    }
}
