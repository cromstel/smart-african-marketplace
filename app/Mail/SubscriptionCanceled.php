<?php

namespace App\Mail;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionCanceled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public UserSubscription $subscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Canceled',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-canceled',
            with: [
                'subscription' => $this->subscription,
            ],
        );
    }
}
