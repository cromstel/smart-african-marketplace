<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lightweight, dependency-free anti-spam guard for public-facing forms
 * (auth/login, register, password reset, etc.).
 *
 * Two signals are used:
 *   1. Honeypot  - a visually hidden field humans never see or fill. Bots
 *                  that blindly populate every field will populate it.
 *   2. Fill-time - a server-rendered timestamp. Submissions faster than
 *                  MIN_SUBMIT_SECONDS are almost certainly automated.
 *
 * A real user is never falsely flagged: they cannot see the honeypot and
 * they take longer than the minimum fill time.
 */
class PreventSpamSubmissions
{
    /** Field name bots are likely to auto-fill; must stay empty. */
    protected const HONEYPOT_FIELD = 'website_url';

    /** Minimum seconds a form should realistically take to fill. */
    protected const MIN_SUBMIT_SECONDS = 2;

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isSpam($request)) {
            return $this->deny($request);
        }

        return $next($request);
    }

    protected function isSpam(Request $request): bool
    {
        // 1. Honeypot populated -> automated submission.
        if ($request->filled(self::HONEYPOT_FIELD)) {
            return true;
        }

        // 2. Suspiciously fast submission -> automated submission.
        $loadedAt = $request->input('form_loaded_at');
        if (is_numeric($loadedAt)) {
            $elapsed = time() - (int) $loadedAt;
            if ($elapsed < self::MIN_SUBMIT_SECONDS) {
                return true;
            }
        }

        return false;
    }

    protected function deny(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Submission rejected.',
            ], 422);
        }

        return redirect()
            ->back()
            ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
            ->with('error', 'Your submission could not be processed. Please try again.');
    }
}
