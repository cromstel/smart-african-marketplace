<?php

namespace App\Jobs;

use App\Models\CampaignEvent;
use App\Models\CampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Roadmap 3.2: previously sent inline inside SendWinBackCampaignCommand's
 * foreach loop. The CampaignRecipient row is created up front (by the
 * command, in 'pending' status) so tracking links exist immediately; this
 * job only does the send + the sent/CampaignEvent bookkeeping, and skips
 * cleanly if the recipient was already sent to (or unsubscribed) by the
 * time it runs.
 */
class SendWinBackCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public CampaignRecipient $recipient)
    {
        $this->onQueue('campaigns');
    }

    public function handle(): void
    {
        $recipient = $this->recipient->fresh(['campaign']);

        if (! $recipient || $recipient->status !== 'pending' || ! $recipient->campaign) {
            return;
        }

        $campaign = $recipient->campaign;
        $target = url('/?utm_source=winback&utm_medium=email&utm_campaign=reactivation');
        $link = route('campaigns.track.click', ['token' => $recipient->token, 'target' => $target]);
        $openPixel = route('campaigns.track.open', ['token' => $recipient->token]);
        $unsubscribe = route('campaigns.unsubscribe', ['token' => $recipient->token]);

        $html = '<div style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">'
            .'<h2 style="margin-bottom: 8px;">We miss you 👋</h2>'
            .'<p style="margin:0 0 12px;">It has been a while since your last visit. We saved a special offer for your next order.</p>'
            .'<p style="margin:16px 0;"><a href="'.$link.'" style="display:inline-block;background:#ea580c;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">Shop Now</a></p>'
            .'<p style="font-size:12px;color:#6b7280;margin-top:18px;">If you no longer want these emails, you can <a href="'.$unsubscribe.'">unsubscribe here</a>.</p>'
            .'<img src="'.$openPixel.'" width="1" height="1" alt="" />'
            .'</div>';

        Mail::html($html, function ($message) use ($recipient, $campaign) {
            $message->to($recipient->email)->subject($campaign->subject);
        });

        $recipient->update(['status' => 'sent', 'sent_at' => now()]);

        CampaignEvent::create([
            'campaign_id' => $campaign->id,
            'campaign_recipient_id' => $recipient->id,
            'event_type' => 'sent',
            'meta' => ['email' => $recipient->email, 'kind' => 'win_back'],
        ]);
    }
}
