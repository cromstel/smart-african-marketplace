<?php

namespace App\Console\Commands;

use App\Jobs\SendWinBackCampaignEmail;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Newsletter;
use App\Models\Order;
use Illuminate\Console\Command;
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

            SendWinBackCampaignEmail::dispatch($recipient);
        }

        // 'sent' here means "handed off to the queue", not "delivered" -- the
        // per-recipient CampaignRecipient/CampaignEvent rows are what track
        // actual delivery now that sends happen asynchronously.
        $campaign->update(['status' => 'sent', 'sent_at' => now()]);

        $this->info("Win-back campaign queued for {$targets->count()} recipients.");

        return self::SUCCESS;
    }
}
