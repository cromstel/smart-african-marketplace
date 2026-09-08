<?php

namespace App\Notifications;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public int $hoursUntilBooking
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $hours = $this->hoursUntilBooking;

        return (new MailMessage)
            ->subject("Reminder: Your Booking is Tomorrow - {$booking->booking_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('This is a friendly reminder about your upcoming booking.')
            ->line('**Booking Details:**')
            ->line("Booking #: {$booking->booking_number}")
            ->line('Service: '.($booking->salonService?->name ?? 'N/A'))
            ->line('Date: '.Carbon::parse($booking->booking_date)->format('l, F j, Y'))
            ->line("Time: {$booking->booking_time}")
            ->line('Location: '.($booking->salonService?->user?->vendor?->address ?? 'See booking details'))
            ->line('Please arrive 10 minutes early.')
            ->action('View Booking', route('bookings.show', $booking))
            ->line('We look forward to seeing you!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Booking Reminder',
            'message' => "Your booking #{$this->booking->booking_number} is in {$this->hoursUntilBooking} hours.",
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'booking_date' => Carbon::parse($this->booking->booking_date)->toDateString(),
            'booking_time' => $this->booking->booking_time,
            'action_url' => route('bookings.show', $this->booking),
        ];
    }
}
