<?php

namespace App\Providers;

use App\Models\Auction;
use App\Models\Booking;
use App\Models\SupportTicket;
use App\Policies\AuctionPolicy;
use App\Policies\BookingPolicy;
use App\Policies\SupportTicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Auction::class => AuctionPolicy::class,
        Booking::class => BookingPolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
    ];

    public function boot(): void
    {
        // `bookings.manage_resources` gates the vendor booking-resources
        // routes (routes/web.php). It was never defined anywhere -- no
        // Gate::define, no matching Policy method, and the app's real
        // permission system is User::hasRole()/hasAnyRole(), not an
        // ability-string permission table (see
        // 2026_09_14_000000_drop_unused_spatie_permission_tables). An
        // undefined ability makes Laravel's Gate deny by default, so this
        // route group returned 403 for every vendor, with no code path that
        // could ever pass it. Defining it here matches the guard the route
        // already assumes (vendor-only, already enforced by the `vendor`
        // route middleware and `module.access:bookings`).
        //
        // `bookings.view_any`, `bookings.update`, `bookings.cancel`,
        // `bookings.create`, `bookings.confirm`, `bookings.manage_types`
        // (admin, customer and vendor Booking routes) had the same
        // undefined-ability problem. Those needed real per-resource
        // ownership checks rather than a blanket role check, so instead of
        // Gate::define they were rewired in routes/web.php to
        // can:ability,model / can:ability,App\Models\Booking against
        // BookingPolicy, whose view/update/confirm/cancel methods were
        // themselves fixed at the same time (they compared salon_owner_id,
        // a users.id foreign key, against $user->vendor->id instead of
        // $user->id).
        Gate::define('bookings.manage_resources', fn ($user) => $user->isVendor());
    }
}
