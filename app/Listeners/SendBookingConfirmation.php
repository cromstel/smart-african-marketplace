<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Mail\BookingConfirmed;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmation
{
    public function handle(BookingStatusChanged $event): void
    {
        if ($event->newStatus !== 'confirmed') {
            return;
        }

        $booking = $event->booking->loadMissing('user', 'salonService.user');

        if ($booking->user?->email) {
            Mail::to($booking->user->email)->send(new BookingConfirmed($booking));
        }

        if ($booking->user?->phone) {
            app(SmsService::class)->sendBookingConfirmation(
                $booking->user->phone,
                $booking->salonService->name,
                $booking->booking_date->format('M d, Y').' '.Carbon::parse($booking->booking_time)->format('g:i A')
            );
        }
    }
}
