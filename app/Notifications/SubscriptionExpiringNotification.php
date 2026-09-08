<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $planName,
        public int $daysLeft,
        public ?int $subscriptionId = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $upgradeUrl = route('vendor.subscription.upgrade');

        return (new MailMessage)
            ->subject('Your '.config('app.name').' Subscription is Expiring Soon')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your **{$this->planName}** subscription will expire in **{$this->daysLeft} days**.")
            ->line('To continue enjoying uninterrupted access to our platform, please upgrade your subscription now.')
            ->action('Upgrade Subscription', $upgradeUrl)
            ->line('Benefits of upgrading:')
            ->line('Priority support and improved operations')
            ->line('Advanced analytics and growth tools')
            ->line('Thank you for being a valued member of '.config('app.name').'!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_expiring',
            'title' => 'Subscription Expiring Soon',
            'message' => "Your {$this->planName} subscription expires in {$this->daysLeft} days. Upgrade now to continue selling.",
            'days_left' => $this->daysLeft,
            'subscription_id' => $this->subscriptionId,
            'action_url' => route('vendor.subscription.upgrade'),
            'action_text' => 'Upgrade Now',
        ];
    }
}
