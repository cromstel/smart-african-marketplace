<?php

namespace App\Console\Commands;

use App\Http\Controllers\WebhookController;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class ReplayWebhookFixtureCommand extends Command
{
    protected $signature = 'webhooks:replay-fixture
        {provider : stripe|paystack}
        {fixture : Fixture file name or path (json)}
        {--secret= : Secret used to sign payload}
        {--persist-secret : Keep secret in settings after command finishes}';

    protected $description = 'Replay a signed webhook fixture locally without external providers';

    public function handle(): int
    {
        $provider = strtolower((string) $this->argument('provider'));
        if (! in_array($provider, ['stripe', 'paystack'], true)) {
            $this->error('Provider must be stripe or paystack.');

            return self::FAILURE;
        }

        $fixturePath = $this->resolveFixturePath((string) $this->argument('fixture'));
        if (! is_file($fixturePath)) {
            $this->error("Fixture not found: {$fixturePath}");

            return self::FAILURE;
        }

        $payload = file_get_contents($fixturePath);
        if ($payload === false || trim($payload) === '') {
            $this->error('Fixture payload is empty or unreadable.');

            return self::FAILURE;
        }

        if (json_decode($payload, true) === null) {
            $this->error('Fixture must be valid JSON.');

            return self::FAILURE;
        }

        [$settingKey, $headerName, $headerValue] = $this->buildSignatureInputs($provider, $payload);
        $previous = Setting::where('key', $settingKey)->first();

        Setting::set($settingKey, $this->option('secret') ?: $this->defaultSecret($provider));

        try {
            $request = Request::create(
                $provider === 'stripe' ? '/webhooks/stripe' : '/webhooks/paystack',
                'POST',
                [],
                [],
                [],
                [],
                $payload
            );
            $request->headers->set($headerName, $headerValue);

            /** @var WebhookController $controller */
            $controller = app(WebhookController::class);
            $response = $provider === 'stripe'
                ? $controller->stripe($request)
                : $controller->paystack($request);

            $this->line("Provider: {$provider}");
            $this->line("Fixture: {$fixturePath}");
            $this->line("Status: {$response->getStatusCode()}");
            $this->line('Body: '.$response->getContent());

            return $response->isSuccessful() ? self::SUCCESS : self::FAILURE;
        } finally {
            if (! $this->option('persist-secret')) {
                if ($previous) {
                    Setting::updateOrCreate(
                        ['key' => $previous->key],
                        ['value' => $previous->value, 'type' => $previous->type]
                    );
                } else {
                    Setting::where('key', $settingKey)->delete();
                }
            }
        }
    }

    private function resolveFixturePath(string $fixture): string
    {
        if (is_file($fixture)) {
            return $fixture;
        }

        $name = str_ends_with($fixture, '.json') ? $fixture : "{$fixture}.json";

        return base_path("tests/Fixtures/webhooks/{$name}");
    }

    private function buildSignatureInputs(string $provider, string $payload): array
    {
        $secret = (string) ($this->option('secret') ?: $this->defaultSecret($provider));

        if ($provider === 'stripe') {
            $timestamp = time();
            $signedPayload = "{$timestamp}.{$payload}";
            $signature = hash_hmac('sha256', $signedPayload, $secret);

            return ['stripe_webhook_secret', 'Stripe-Signature', "t={$timestamp},v1={$signature}"];
        }

        $signature = hash_hmac('sha512', $payload, $secret);

        return ['paystack_secret_key', 'X-Paystack-Signature', $signature];
    }

    private function defaultSecret(string $provider): string
    {
        return $provider === 'stripe' ? 'whsec_fixture_test' : 'sk_test_fixture';
    }
}
