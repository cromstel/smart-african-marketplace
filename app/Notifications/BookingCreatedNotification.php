<?php

namespace App\Notifications;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking
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
        $serviceName = $booking->salonService?->name ?? 'Your booking';

        return (new MailMessage)
            ->subject("Booking Confirmation - {$booking->booking_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your booking has been received and is pending confirmation.')
            ->line('**Booking Details:**')
            ->line("Booking #: {$booking->booking_number}")
            ->line("Service: {$serviceName}")
            ->line('Date: '.Carbon::parse($booking->booking_date)->format('l, F j, Y'))
            ->line("Time: {$booking->booking_time}")
            ->line('Price: $'.number_format((float) $booking->price, 2))
            ->line('Status: '.ucfirst($booking->status))
            ->action('View Booking Details', route('bookings.show', $booking))
            ->line('We will confirm your booking shortly. Thank you for your patience!');
    }

    public function toArray(object $notifiable): array
    {
        $booking = $this->booking;

        return [
            'title' => 'New Booking Received',
            'message' => "Your booking #{$booking->booking_number} has been received.",
            'booking_id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'status' => $booking->status,
            'action_url' => route('bookings.show', $booking),
        ];
    }
}
