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

        if ($user->isVendor() && $user->vendor) {
            // Vendor can view bookings for their salon
            if ($booking->salon_owner_id === $user->vendor->id ||
                $booking->vendor_id === $user->vendor->id) {
                return true;
            }
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

        if ($user->isVendor() && $user->vendor) {
            // Vendor can update bookings for their salon
            if ($booking->salon_owner_id === $user->vendor->id ||
                $booking->vendor_id === $user->vendor->id) {
                return true;
            }
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

        if ($user->isVendor() && $user->vendor) {
            // Vendor can confirm bookings for their salon
            return $booking->salon_owner_id === $user->vendor->id ||
                   $booking->vendor_id === $user->vendor->id;
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

        if ($user->isVendor() && $user->vendor) {
            // Vendor can cancel bookings for their salon
            if ($booking->salon_owner_id === $user->vendor->id ||
                $booking->vendor_id === $user->vendor->id) {
                return true;
            }
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
