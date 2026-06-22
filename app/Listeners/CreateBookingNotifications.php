<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Models\Booking;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\FcmPushService;

class CreateBookingNotifications
{
    public function __construct(private readonly FcmPushService $fcmPushService) {}

    public function handle(BookingStatusChanged $event): void
    {
        $booking = $event->booking->loadMissing('user', 'salonService.user');

        $this->notifyCustomer($booking, $event->newStatus);
        $this->notifyVendor($booking, $event->newStatus);
    }

    protected function notifyCustomer(Booking $booking, string $status): void
    {
        if (! $booking->user) {
            return;
        }

        $label = $this->statusLabel($status);
        $title = "Booking {$label}";
        $message = "Your booking {$booking->booking_number} is now {$label}.";

        $this->createNotification(
            $booking->user_id,
            $title,
            $message,
            'booking',
            route('bookings.show', $booking)
        );
    }

    protected function notifyVendor(Booking $booking, string $status): void
    {
        $vendor = $booking->salonService?->user;
        if (! $vendor) {
            return;
        }

        $label = $this->statusLabel($status);
        $title = "Booking {$label}";
        $message = "Booking {$booking->booking_number} for {$booking->salonService->name} is {$label}.";

        $this->createNotification(
            $vendor->id,
            $title,
            $message,
            'booking',
            route('vendor.bookings.show', $booking)
        );
    }

    protected function createNotification(int $userId, string $title, ?string $message, string $type, ?string $actionUrl): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        if (! $user->notificationPreference('in_app_enabled', true)) {
            return;
        }
        if ($type === 'booking' && ! $user->notificationPreference('booking_enabled', true)) {
            return;
        }

        UserNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
        ]);

        $this->fcmPushService->sendToUser($user, $title, (string) $message, [
            'type' => $type,
            'action_url' => (string) $actionUrl,
        ]);
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            default => ucfirst($status),
        };
    }
}
