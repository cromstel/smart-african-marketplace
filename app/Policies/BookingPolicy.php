<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any bookings.
     * Admin, Vendor (own), Customer (own)
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
     * Determine whether the user can view the booking.
     * Admin, Vendor (own), Customer (own)
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor()) {
            // bookings.salon_owner_id is a foreign key to users.id (the
            // salon service's owning user), not vendors.id -- there is no
            // vendor_id column on this table at all. Comparing it against
            // $user->vendor->id (as this used to) meant a vendor's own
            // bookings almost never matched their own vendor id, and in
            // rare cases another vendor's user id could coincidentally
            // equal it, granting a cross-tenant view.
            return $booking->salon_owner_id === $user->id;
        }

        if ($user->isCustomer()) {
            // Customer can view their own bookings
            return $booking->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create bookings.
     * Admin, Customer
     */
    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the booking.
     * Admin, Vendor (own), Customer (own)
     */
    public function update(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor()) {
            // See view(): salon_owner_id is a users.id, not vendors.id.
            return $booking->salon_owner_id === $user->id;
        }

        if ($user->isCustomer()) {
            // Customer can update their own bookings (e.g., reschedule)
            return $booking->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the booking.
     * Admin only
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can confirm the booking.
     * Admin, Vendor (own)
     */
    public function confirm(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor()) {
            // See view(): salon_owner_id is a users.id, not vendors.id.
            return $booking->salon_owner_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the booking.
     * Admin, Vendor (own), Customer (own)
     */
    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor()) {
            // See view(): salon_owner_id is a users.id, not vendors.id.
            return $booking->salon_owner_id === $user->id;
        }

        if ($user->isCustomer()) {
            // Customer can cancel their own bookings
            return $booking->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can manage booking types.
     * Admin only
     */
    public function manageTypes(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage booking resources.
     * Admin, Vendor
     */
    public function manageResources(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor && $user->vendor->isApproved()) {
            return true;
        }

        return false;
    }
}
