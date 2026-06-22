<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Mail\BookingCancelled;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;

class SendBookingCancellation
{
    public function handle(BookingStatusChanged $event): void
    {
        if ($event->newStatus !== 'cancelled') {
            return;
        }

        $booking = $event->booking->loadMissing('user', 'salonService.user');

        if ($booking->user?->email) {
            Mail::to($booking->user->email)->send(new BookingCancelled($booking));
        }

        if ($booking->user?->phone) {
            app(SmsService::class)->sendBookingCancellation(
                $booking->user->phone,
                $booking->salonService->name,
                $booking->booking_date->format('M d, Y').' '.\Carbon\Carbon::parse($booking->booking_time)->format('g:i A')
            );
        }
    }
}
