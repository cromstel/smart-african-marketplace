<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * The public campaign open/click/unsubscribe endpoints are hit by anyone
 * holding (or guessing) a token, with no session or auth to correlate --
 * they had no rate limit at all until Roadmap pre-Phase-4 audit added the
 * `public-forms` limiter to them (same limiter already used by contact and
 * newsletter). Each hit does a DB lookup, and open()/unsubscribe() also
 * write, so this closes a cheap, unauthenticated DB-amplification vector.
 */
class CampaignTrackingThrottleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('');
    }

    private function makeRecipient(): CampaignRecipient
    {
        $campaign = Campaign::create([
            'name' => 'Throttle test campaign',
            'subject' => 'Subject',
            'content' => 'Content',
        ]);

        return CampaignRecipient::create([
            'campaign_id' => $campaign->id,
            'email' => 'throttle-test@example.com',
            'token' => str()->random(40),
        ]);
    }

    public function test_the_open_pixel_is_rate_limited(): void
    {
        $recipient = $this->makeRecipient();
        $statuses = [];

        // The `public-forms` limiter allows 20 per minute; the 21st must be refused.
        for ($i = 0; $i < 21; $i++) {
            $statuses[] = $this->get(route('campaigns.track.open', $recipient->token))->getStatusCode();
        }

        $this->assertContains(
            429,
            $statuses,
            'The campaign open pixel accepted 21 requests in a minute; the throttle is not applied.'
        );
    }

    public function test_the_unsubscribe_link_is_rate_limited(): void
    {
        $recipient = $this->makeRecipient();
        $statuses = [];

        for ($i = 0; $i < 21; $i++) {
            $statuses[] = $this->get(route('campaigns.unsubscribe', $recipient->token))->getStatusCode();
        }

        $this->assertContains(
            429,
            $statuses,
            'The campaign unsubscribe link accepted 21 requests in a minute; the throttle is not applied.'
        );
    }
}
