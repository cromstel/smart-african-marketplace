<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\CampaignEvent;
use App\Models\CampaignRecipient;
use App\Models\Newsletter;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendWinBackCampaignCommand extends Command
{
    protected $signature = 'campaigns:send-winback {--days=60} {--max=500}';

    protected $description = 'Send win-back campaign emails to inactive customers';

    public function handle(): int
    {
        $inactiveDays = max(14, (int) $this->option('days'));
        $max = max(1, (int) $this->option('max'));
        $inactiveSince = now()->subDays($inactiveDays);

        $subscribers = Newsletter::query()
            ->where('status', 'subscribed')
            ->whereNotNull('user_id')
            ->limit(5000)
            ->get(['id', 'email', 'user_id', 'created_at']);

        if ($subscribers->isEmpty()) {
            $this->info('No subscribed users found.');

            return self::SUCCESS;
        }

        $orderStats = Order::query()
            ->where('payment_status', 'paid')
            ->whereIn('user_id', $subscribers->pluck('user_id')->unique())
            ->selectRaw('user_id, MAX(created_at) as last_paid_order_at, COUNT(*) as paid_orders')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $eligible = $subscribers->filter(function ($subscriber) use ($orderStats, $inactiveSince) {
            $stats = $orderStats->get($subscriber->user_id);
            if (! $stats || (int) $stats->paid_orders < 1) {
                return false;
            }

            return $stats->last_paid_order_at <= $inactiveSince;
        });

        if ($eligible->isEmpty()) {
            $this->info('No inactive subscribers matched the win-back criteria.');

            return self::SUCCESS;
        }

        $recentlyTargetedEmails = CampaignRecipient::query()
            ->whereIn('email', $eligible->pluck('email'))
            ->whereHas('campaign', function ($query) {
                $query->where('campaign_type', 'win_back')
                    ->where('created_at', '>=', now()->subDays(21));
            })
            ->pluck('email')
            ->unique();

        $targets = $eligible
            ->reject(fn ($subscriber) => $recentlyTargetedEmails->contains($subscriber->email))
            ->take($max)
            ->values();

        if ($targets->isEmpty()) {
            $this->info('No new win-back recipients after recent-send protection.');

            return self::SUCCESS;
        }

        $campaign = Campaign::create([
            'name' => 'Win-Back Campaign '.now()->format('Y-m-d H:i'),
            'channel' => 'email',
            'campaign_type' => 'win_back',
            'audience_segment' => 'inactive',
            'subject' => 'We miss you — here is something special',
            'content' => "It's been a while since your last purchase.\nCome back today and enjoy a special offer curated for you.",
            'status' => 'sending',
            'created_by' => null,
        ]);

        foreach ($targets as $subscriber) {
            $recipient = CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'email' => $subscriber->email,
                'newsletter_id' => $subscriber->id,
                'user_id' => $subscriber->user_id,
                'token' => Str::uuid()->toString(),
                'status' => 'pending',
            ]);

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

            Mail::html($html, function ($message) use ($subscriber, $campaign) {
                $message->to($subscriber->email)->subject($campaign->subject);
            });

            $recipient->update(['status' => 'sent', 'sent_at' => now()]);

            CampaignEvent::create([
                'campaign_id' => $campaign->id,
                'campaign_recipient_id' => $recipient->id,
                'event_type' => 'sent',
                'meta' => ['email' => $subscriber->email, 'kind' => 'win_back'],
            ]);
        }

        $campaign->update(['status' => 'sent', 'sent_at' => now()]);

        $this->info("Win-back campaign sent to {$targets->count()} recipients.");

        return self::SUCCESS;
    }
}
