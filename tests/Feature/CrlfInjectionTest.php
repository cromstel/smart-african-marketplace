<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventCrlfInjection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Covers App\Http\Middleware\PreventCrlfInjection.
 *
 * The attack: a carriage return or line feed inside a value that is later
 * placed into a mail header lets the caller append headers of their own --
 * "victim@example.com\r\nBcc: everyone@example.com" turns a contact form into
 * a relay. The middleware rejects CR/LF in fields whose names indicate a
 * single-line value, and deliberately leaves free-text fields alone.
 */
class CrlfInjectionTest extends TestCase
{
    use DatabaseTransactions;

    public static function crlfPayloads(): array
    {
        return [
            'carriage return + line feed' => ["victim@example.com\r\nBcc: attacker@example.com"],
            'line feed only' => ["victim@example.com\nBcc: attacker@example.com"],
            'carriage return only' => ["victim@example.com\rBcc: attacker@example.com"],
        ];
    }

    /**
     * @dataProvider crlfPayloads
     */
    public function test_it_rejects_line_breaks_in_an_email_field(string $value): void
    {
        $this->postJson('/api/v1/mobile/auth/register', [
            'name' => 'Jane Doe',
            'email' => $value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'test-device',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_it_rejects_line_breaks_in_a_name_field(): void
    {
        $this->postJson('/api/v1/mobile/auth/register', [
            'name' => "Jane\r\nSubject: injected",
            'email' => 'crlf-name@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'test-device',
        ])->assertStatus(422)->assertJsonValidationErrors('name');
    }

    public function test_it_leaves_free_text_fields_alone(): void
    {
        // customer_notes is genuinely multi-line; blocking it would break
        // ordinary use. The middleware must not treat it as single-line.
        $middleware = new PreventCrlfInjection;
        $request = Request::create('/checkout', 'POST', [
            'customer_notes' => "Leave at the back door.\nRing twice.",
        ]);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_it_allows_a_clean_single_line_value_through(): void
    {
        $middleware = new PreventCrlfInjection;
        $request = Request::create('/contact', 'POST', [
            'email' => 'someone@example.com',
            'subject' => 'A perfectly ordinary subject',
        ]);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_it_inspects_nested_fields(): void
    {
        $middleware = new PreventCrlfInjection;
        $request = Request::create('/checkout', 'POST', [
            'billing' => ['email' => "buyer@example.com\r\nBcc: attacker@example.com"],
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(422, $response->getStatusCode());
    }
}
