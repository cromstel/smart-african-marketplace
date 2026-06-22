<?php

namespace App\Mail;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionTrialEnding extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public UserSubscription $subscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Free Trial is Ending Soon',
        );
    }

    public function content(): Content
    {
        $daysLeft = now()->diffInDays($this->subscription->trial_ends_at, false);

        return new Content(
            view: 'emails.subscription-trial-ending',
            with: [
                'subscription' => $this->subscription,
                'daysLeft' => max(0, $daysLeft),
            ],
        );
    }
}
