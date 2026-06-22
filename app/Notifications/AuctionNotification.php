<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionNotification extends Notification
{
    use Queueable;

    public const TYPE_OUTBID = 'outbid';

    public const TYPE_WON = 'won';

    public const TYPE_LOST = 'lost';

    public const TYPE_ENDED = 'ended';

    public const TYPE_SETTLED = 'settled';

    public const TYPE_CANCELLED = 'cancelled';

    protected $auction;

    protected $type;

    public function __construct(Auction $auction, string $type)
    {
        $this->auction = $auction;
        $this->type = $type;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return match ($this->type) {
            self::TYPE_OUTBID => $this->outbidMail($notifiable),
            self::TYPE_WON => $this->wonMail($notifiable),
            self::TYPE_LOST => $this->lostMail($notifiable),
            self::TYPE_ENDED => $this->endedMail($notifiable),
            self::TYPE_CANCELLED => $this->cancelledMail($notifiable),
            default => $this->defaultMail($notifiable),
        };
    }

    public function toArray($notifiable): array
    {
        return [
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'type' => $this->type,
            'message' => $this->getMessage(),
        ];
    }

    protected function getMessage(): string
    {
        return match ($this->type) {
            self::TYPE_OUTBID => "You've been outbid on \"{$this->auction->title}\"",
            self::TYPE_WON => "Congratulations! You won the auction for \"{$this->auction->title}\"",
            self::TYPE_LOST => "The auction for \"{$this->auction->title}\" has ended",
            self::TYPE_ENDED => "The auction \"{$this->auction->title}\" has ended",
            self::TYPE_CANCELLED => "The auction \"{$this->auction->title}\" has been cancelled",
            default => "Update on auction \"{$this->auction->title}\"",
        };
    }

    protected function outbidMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You\'ve been outbid!')
            ->line($this->getMessage())
            ->line('Current bid: $'.number_format((float) $this->auction->current_price, 2))
            ->action('View Auction', route('auctions.show', $this->auction));
    }

    protected function wonMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Congratulations! You won!')
            ->line($this->getMessage())
            ->line('Winning bid: $'.number_format((float) $this->auction->current_price, 2))
            ->action('Complete Purchase', route('auctions.show', $this->auction));
    }

    protected function lostMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction Ended')
            ->line($this->getMessage())
            ->action('View Auction', route('auctions.show', $this->auction));
    }

    protected function endedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction Ended')
            ->line($this->getMessage())
            ->action('View Results', route('auctions.show', $this->auction));
    }

    protected function cancelledMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction Cancelled')
            ->line($this->getMessage())
            ->line('If you have any questions, please contact the seller.');
    }

    protected function defaultMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction Update')
            ->line($this->getMessage())
            ->action('View Auction', route('auctions.show', $this->auction));
    }
}
