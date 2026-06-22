<?php

namespace App\Providers;

use App\Models\Auction;
use App\Models\Booking;
use App\Models\SupportTicket;
use App\Policies\AuctionPolicy;
use App\Policies\BookingPolicy;
use App\Policies\SupportTicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Auction::class => AuctionPolicy::class,
        Booking::class => BookingPolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
