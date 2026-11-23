<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * The mobile registration endpoint was unthrottled while login was capped at
 * 10/min. Registration is the more attractive target of the two: it creates
 * rows, so an unmetered endpoint is a database-growth and spam vector rather
 * than just a guessing one.
 */
class RegistrationThrottleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('');
    }

    public function test_registration_is_rate_limited(): void
    {
        $statuses = [];

        // The route allows 5 per minute; the sixth must be refused.
        for ($i = 0; $i < 6; $i++) {
            $statuses[] = $this->postJson('/api/v1/mobile/auth/register', [
                'name' => "Throttle Test {$i}",
                'email' => "throttle-test-{$i}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'device_name' => 'test-device',
            ])->getStatusCode();
        }

        $this->assertContains(
            429,
            $statuses,
            'Registration accepted six requests in a minute; the throttle is not applied. Statuses: '
                .implode(', ', $statuses)
        );
    }
}
