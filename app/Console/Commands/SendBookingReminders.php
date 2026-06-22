<?php

namespace App\Console\Commands;

use App\Mail\BookingReminder;
use App\Models\Booking;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBookingReminders extends Command
{
    protected $signature = 'booking:send-reminders';

    protected $description = 'Send booking reminders for upcoming appointments within 24 hours';

    public function handle(): int
    {
        $start = Carbon::now()->addHours(23);
        $end = Carbon::now()->addHours(24);

        $bookings = Booking::with(['user', 'salonService'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->filter(function (Booking $booking) use ($start, $end) {
                $slot = Carbon::parse($booking->booking_date->toDateString().' '.$booking->booking_time);

                return $slot->between($start, $end);
            });

        $smsService = app(SmsService::class);

        foreach ($bookings as $booking) {
            if ($booking->user?->email) {
                Mail::to($booking->user->email)->send(new BookingReminder($booking));
            }

            if ($booking->user?->phone) {
                $smsService->sendBookingReminder(
                    $booking->user->phone,
                    $booking->salonService->name,
                    $booking->booking_date->format('M d, Y').' '.Carbon::parse($booking->booking_time)->format('g:i A')
                );
            }
        }

        $this->info('Booking reminders sent: '.$bookings->count());

        return Command::SUCCESS;
    }
}
