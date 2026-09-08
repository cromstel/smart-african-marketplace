<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects carriage returns and line feeds in request fields that are later
 * placed into single-line contexts -- mail headers above all.
 *
 * A value such as "attacker@example.com\r\nBcc: victim@example.com" injects an
 * extra header when interpolated into a message, which turns a contact form
 * into an open relay. The same applies to a subject line, and to values used
 * to build a redirect.
 *
 * Deliberately narrow: only fields whose names indicate a single-line value are
 * inspected. Free-text fields (customer_notes, admin_notes, message, body) may
 * legitimately contain newlines and are left alone. Widening this list is a
 * security decision -- do not add a field without checking that multi-line
 * input is genuinely invalid for it.
 */
class PreventCrlfInjection
{
    /**
     * Field names that must never contain CR or LF. Matched case-insensitively
     * against the whole key and against the segment after the last dot, so
     * "billing.email" and "recipients.0.email" are both covered.
     *
     * @var array<int, string>
     */
    protected array $singleLineFields = [
        'email',
        'email_address',
        'from',
        'from_email',
        'to',
        'to_email',
        'cc',
        'bcc',
        'reply_to',
        'sender',
        'recipient',
        'subject',
        'name',
        'first_name',
        'last_name',
        'phone',
        'phone_number',
        'redirect',
        'redirect_to',
        'return_url',
        'callback_url',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (Arr::dot($request->all()) as $key => $value) {
            if (! is_string($value) || ! $this->containsCrlf($value)) {
                continue;
            }

            if (! $this->isSingleLineField($key)) {
                continue;
            }

            Log::warning('Blocked CRLF injection attempt', [
                'field' => $key,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return $this->reject($request, $key);
        }

        return $next($request);
    }

    protected function containsCrlf(string $value): bool
    {
        return str_contains($value, "\r") || str_contains($value, "\n");
    }

    protected function isSingleLineField(string $key): bool
    {
        $needle = strtolower($key);
        $segments = explode('.', $needle);
        $last = end($segments);

        foreach ($this->singleLineFields as $field) {
            if ($needle === $field || $last === $field) {
                return true;
            }
        }

        return false;
    }

    protected function reject(Request $request, string $key): Response
    {
        $message = 'The '.str_replace('_', ' ', $key).' field must not contain line breaks.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => [$key => [$message]],
            ], 422);
        }

        return back()
            ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
            ->withErrors([$key => $message]);
    }
}
