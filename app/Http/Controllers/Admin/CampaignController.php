<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignEvent;
use App\Models\CampaignRecipient;
use App\Models\Cart;
use App\Models\LeadAttribution;
use App\Models\Newsletter;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = Campaign::withCount('recipients')->latest()->paginate(20);

        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('admin.campaigns.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'campaign_type' => 'required|in:standard,welcome,abandoned_cart,promotional,flash_sale,win_back',
            'audience_segment' => 'required|in:all,new,active,vip,abandoned_cart,newsletter_new,inactive',
            'banner_image_url' => 'nullable|url|max:1024',
            'scheduled_at' => 'nullable|date',
            'flash_sale_ends_at' => 'nullable|date',
        ]);

        Campaign::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'campaign_type' => $validated['campaign_type'],
            'audience_segment' => $validated['audience_segment'],
            'banner_image_url' => $validated['banner_image_url'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'flash_sale_ends_at' => $validated['flash_sale_ends_at'] ?? null,
            'status' => isset($validated['scheduled_at']) ? 'scheduled' : 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign saved.');
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load(['recipients' => fn ($query) => $query->latest()->limit(100)]);
        $stats = [
            'sent' => CampaignEvent::where('campaign_id', $campaign->id)->where('event_type', 'sent')->count(),
            'opened' => CampaignEvent::where('campaign_id', $campaign->id)->where('event_type', 'opened')->count(),
            'clicked' => CampaignEvent::where('campaign_id', $campaign->id)->where('event_type', 'clicked')->count(),
            'converted' => LeadAttribution::where('campaign_id', $campaign->id)->whereNotNull('order_id')->count(),
            'revenue' => (float) LeadAttribution::where('campaign_id', $campaign->id)->sum('order_total'),
        ];

        return view('admin.campaigns.show', compact('campaign', 'stats'));
    }

    public function edit(Campaign $campaign): View
    {
        return view('admin.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'campaign_type' => 'required|in:standard,welcome,abandoned_cart,promotional,flash_sale,win_back',
            'audience_segment' => 'required|in:all,new,active,vip,abandoned_cart,newsletter_new,inactive',
            'banner_image_url' => 'nullable|url|max:1024',
            'scheduled_at' => 'nullable|date',
            'flash_sale_ends_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,sending,sent',
        ]);

        $campaign->update($validated);

        return redirect()->route('admin.campaigns.show', $campaign)->with('success', 'Campaign updated.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign deleted.');
    }

    public function send(Campaign $campaign): RedirectResponse
    {
        $campaign->update(['status' => 'sending']);

        $subscribers = $this->segmentedSubscribers($campaign);

        foreach ($subscribers as $subscriber) {
            $recipient = CampaignRecipient::firstOrCreate(
                ['campaign_id' => $campaign->id, 'email' => $subscriber->email],
                [
                    'newsletter_id' => $subscriber->id,
                    'user_id' => $subscriber->user_id,
                    'token' => Str::uuid()->toString(),
                    'status' => 'pending',
                ]
            );

            $link = route('campaigns.track.click', ['token' => $recipient->token, 'target' => url('/')]);
            $openPixel = route('campaigns.track.open', ['token' => $recipient->token]);
            $html = $this->buildEmailHtml($campaign, $link, $openPixel);

            Mail::html($html, function ($message) use ($subscriber, $campaign) {
                $message->to($subscriber->email)->subject($campaign->subject);
            });

            $recipient->update(['status' => 'sent', 'sent_at' => now()]);
            CampaignEvent::create([
                'campaign_id' => $campaign->id,
                'campaign_recipient_id' => $recipient->id,
                'event_type' => 'sent',
                'meta' => ['email' => $subscriber->email],
            ]);
        }

        $campaign->update(['status' => 'sent', 'sent_at' => now()]);

        return redirect()->route('admin.campaigns.show', $campaign)->with('success', 'Campaign sent successfully.');
    }

    private function segmentedSubscribers(Campaign $campaign)
    {
        $query = Newsletter::query()->where('status', 'subscribed');

        if ($campaign->audience_segment === 'newsletter_new') {
            $query->where('created_at', '>=', now()->subDays(14));
        }

        if ($campaign->audience_segment === 'abandoned_cart') {
            $emails = Cart::query()
                ->whereNotNull('user_id')
                ->where('updated_at', '<=', now()->subHours(2))
                ->where('updated_at', '>=', now()->subDays(7))
                ->whereHas('items')
                ->with('user:id,email')
                ->get()
                ->pluck('user.email')
                ->filter()
                ->unique()
                ->values();

            $query->whereIn('email', $emails);
        }

        if ($campaign->audience_segment === 'inactive') {
            $inactiveSince = now()->subDays(60);
            $stats = Order::query()
                ->where('payment_status', 'paid')
                ->selectRaw('user_id, MAX(created_at) as last_paid_order_at, COUNT(*) as paid_orders')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $emails = Newsletter::query()
                ->where('status', 'subscribed')
                ->whereNotNull('user_id')
                ->get()
                ->filter(function ($subscriber) use ($stats, $inactiveSince) {
                    $userStats = $stats->get($subscriber->user_id);
                    if (! $userStats || (int) $userStats->paid_orders < 1) {
                        return false;
                    }

                    return $userStats->last_paid_order_at <= $inactiveSince;
                })
                ->pluck('email')
                ->filter()
                ->unique()
                ->values();

            $query->whereIn('email', $emails);
        }

        if (in_array($campaign->audience_segment, ['new', 'active', 'vip'], true)) {
            $newThreshold = 2;
            $vipOrderThreshold = 10;
            $vipSpendThreshold = 1000;

            $segments = Order::query()
                ->where('payment_status', 'paid')
                ->selectRaw('user_id, COUNT(*) as orders_count, COALESCE(SUM(total), 0) as total_spent')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $emails = Newsletter::query()
                ->where('status', 'subscribed')
                ->whereNotNull('user_id')
                ->get()
                ->filter(function ($subscriber) use ($campaign, $segments, $newThreshold, $vipOrderThreshold, $vipSpendThreshold) {
                    $stat = $segments->get($subscriber->user_id);
                    $orderCount = (int) ($stat->orders_count ?? 0);
                    $totalSpent = (float) ($stat->total_spent ?? 0);

                    if ($campaign->audience_segment === 'new') {
                        return $orderCount < $newThreshold;
                    }
                    if ($campaign->audience_segment === 'active') {
                        return $orderCount >= $newThreshold && $orderCount < $vipOrderThreshold;
                    }

                    return $orderCount >= $vipOrderThreshold || $totalSpent >= $vipSpendThreshold;
                })
                ->pluck('email')
                ->filter()
                ->unique()
                ->values();

            $query->whereIn('email', $emails);
        }

        return $query->limit(2000)->get();
    }

    private function buildEmailHtml(Campaign $campaign, string $link, string $openPixel): string
    {
        $html = '<div style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">';

        if (! empty($campaign->banner_image_url)) {
            $html .= '<div style="margin-bottom:16px;"><img src="'.e($campaign->banner_image_url).'" alt="Campaign Banner" style="max-width:100%; border-radius:12px;"></div>';
        }

        $html .= '<div style="white-space: pre-wrap;">'.nl2br(e($campaign->content)).'</div>';

        if ($campaign->campaign_type === 'flash_sale' && $campaign->flash_sale_ends_at) {
            $html .= '<p style="margin-top:16px; font-weight:600; color:#dc2626;">Flash sale ends at: '.$campaign->flash_sale_ends_at->format('M d, Y H:i').'</p>';
        }

        $html .= '<p style="margin-top:16px;"><a href="'.$link.'" style="display:inline-block;background:#ea580c;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">Visit Store</a></p>';
        $html .= '<img src="'.$openPixel.'" width="1" height="1" alt="" />';
        $html .= '</div>';

        return $html;
    }
}
