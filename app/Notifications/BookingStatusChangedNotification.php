<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $oldStatus,
        public string $newStatus
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
        $statusLabel = ucfirst($this->newStatus);

        $mail = (new MailMessage)
            ->subject("Booking {$statusLabel} - {$booking->booking_number}")
            ->greeting("Hello {$notifiable->name}!");

        match ($this->newStatus) {
            'confirmed' => $mail
                ->line('Great news! Your booking has been confirmed!')
                ->line('**Date:** '.\Carbon\Carbon::parse($booking->booking_date)->format('l, F j, Y'))
                ->line("**Time:** {$booking->booking_time}"),

            'cancelled' => $mail
                ->line('Your booking has been cancelled.')
                ->line('If you did not request this cancellation, please contact us.'),

            'completed' => $mail
                ->line('Your booking has been completed!')
                ->line('Thank you for your business.')
                ->action('Leave a Review', route('reviews.create', $booking)),

            'pending' => $mail
                ->line('Your booking status has been updated.')
                ->line('We will confirm your booking shortly.'),

            default => $mail
                ->line("Your booking status has been updated to: {$statusLabel}")
        };

        return $mail
            ->line("**Booking #:** {$booking->booking_number}")
            ->line('**Service:** '.($booking->salonService?->name ?? 'N/A'))
            ->action('View Details', route('bookings.show', $booking));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Booking '.ucfirst($this->newStatus),
            'message' => "Your booking #{$this->booking->booking_number} has been {$this->newStatus}.",
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'action_url' => route('bookings.show', $this->booking),
        ];
    }
}
