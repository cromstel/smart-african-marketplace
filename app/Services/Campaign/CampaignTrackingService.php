<?php

namespace App\Services\Campaign;

use App\Models\CampaignEvent;
use App\Models\CampaignRecipient;
use App\Models\LeadAttribution;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CampaignTrackingService
{
    private const SESSION_KEY = 'campaign_last_click';

    public function captureClick(CampaignRecipient $recipient, Request $request): void
    {
        $recipient->forceFill([
            'status' => 'clicked',
            'clicked_at' => now(),
        ])->save();

        CampaignEvent::create([
            'campaign_id' => $recipient->campaign_id,
            'campaign_recipient_id' => $recipient->id,
            'event_type' => 'clicked',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'meta' => ['url' => $request->fullUrl()],
        ]);

        session([self::SESSION_KEY => [
            'campaign_recipient_id' => $recipient->id,
            'campaign_id' => $recipient->campaign_id,
            'email' => $recipient->email,
            'clicked_at' => now()->toDateTimeString(),
        ]]);
    }

    public function captureOpen(CampaignRecipient $recipient, Request $request): void
    {
        if (! $recipient->opened_at) {
            $recipient->forceFill([
                'status' => $recipient->status === 'clicked' ? 'clicked' : 'opened',
                'opened_at' => now(),
            ])->save();
        }

        CampaignEvent::create([
            'campaign_id' => $recipient->campaign_id,
            'campaign_recipient_id' => $recipient->id,
            'event_type' => 'opened',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'meta' => [],
        ]);
    }

    public function attachAttribution(Order $order, ?string $sessionId, ?string $email, ?int $userId): void
    {
        $lastClick = session(self::SESSION_KEY);
        if (! is_array($lastClick)) {
            return;
        }

        $clickedAt = isset($lastClick['clicked_at']) ? Carbon::parse($lastClick['clicked_at']) : null;
        if (! $clickedAt || $clickedAt->lt(now()->subDays(30))) {
            return;
        }

        LeadAttribution::updateOrCreate(
            ['order_id' => $order->id],
            [
                'campaign_id' => $lastClick['campaign_id'],
                'campaign_recipient_id' => $lastClick['campaign_recipient_id'],
                'user_id' => $userId,
                'email' => $email,
                'session_id' => $sessionId,
                'order_total' => $order->total,
                'clicked_at' => $clickedAt,
                'converted_at' => now(),
            ]
        );

        CampaignEvent::create([
            'campaign_id' => $lastClick['campaign_id'],
            'campaign_recipient_id' => $lastClick['campaign_recipient_id'],
            'event_type' => 'converted',
            'meta' => ['order_id' => $order->id, 'order_total' => $order->total],
        ]);
    }
}
