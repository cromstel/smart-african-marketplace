<?php

namespace App\Providers;

use App\Events\BookingStatusChanged;
use App\Listeners\CreateBookingNotifications;
use App\Listeners\SendBookingCancellation;
use App\Listeners\SendBookingConfirmation;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        BookingStatusChanged::class => [
            SendBookingConfirmation::class,
            SendBookingCancellation::class,
            CreateBookingNotifications::class,
        ],
    ];

    public function boot(): void
    {
        User::observe(UserObserver::class);
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
