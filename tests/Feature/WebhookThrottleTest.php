<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Payment/SMS provider webhooks previously carried no rate limit at all,
 * unlike the other public POST endpoints in routes/web.php (contact,
 * newsletter, chatbot). Exercising the real 120/min limit end-to-end would
 * mean 121 HTTP requests per test run -- instead this asserts the routes
 * actually carry the `throttle:webhook` middleware, which is what matters:
 * that the limiter is wired in, not its exact threshold.
 */
class WebhookThrottleTest extends TestCase
{
    public function test_webhook_routes_are_rate_limited(): void
    {
        foreach (['webhooks.stripe', 'webhooks.paystack', 'webhooks.momo', 'webhooks.swish'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Route [{$name}] is not registered.");
            $this->assertContains(
                'throttle:webhook',
                $route->gatherMiddleware(),
                "Route [{$name}] is missing the webhook rate limiter."
            );
        }
    }
}
