<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserActivityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserSecurityController extends Controller
{
    public function __construct(
        protected UserActivityService $activityService
    ) {}

    /**
     * Show security settings page.
     */
    public function index(): View
    {
        $user = Auth::user();

        return view('account.security', compact('user'));
    }

    /**
     * Show 2FA setup page with QR code.
     */
    public function show2faSetup(Request $request): View|JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($user->isTwoFactorEnabled()) {
            return redirect()->route('account.security')->with('info', 'Two-factor authentication is already enabled.');
        }

        // Generate new secret if not already in session
        if (! $request->session()->has('two_factor_secret')) {
            $secret = $user->generateTwoFactorSecret();
            $request->session()->put('two_factor_secret', $secret);
        } else {
            $secret = $request->session()->get('two_factor_secret');
        }

        $qrCodeUrl = $user->get2faQrCodeUrl();

        return view('account.security-2fa-setup', compact('user', 'secret', 'qrCodeUrl'));
    }

    /**
     * Verify 2FA code (for login or confirmation).
     */
    public function verify2fa(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (! $user->isTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 422);
        }

        if ($user->verifyTwoFactorCode($request->code)) {
            // Mark as verified in session for this flow
            $request->session()->put('two_factor_verified', true);

            return response()->json([
                'message' => 'Verification successful.',
            ]);
        }

        return response()->json([
            'message' => 'Invalid verification code.',
        ], 422);
    }

    /**
     * Show recovery codes.
     */
    public function showRecoveryCodes(Request $request): View|JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user->isTwoFactorEnabled()) {
            return redirect()->route('account.security');
        }

        $recoveryCodes = $user->two_factor_recovery_codes ?? [];

        if ($request->expectsJson()) {
            return response()->json([
                'recovery_codes' => $recoveryCodes,
            ]);
        }

        return view('account.security-recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Show sessions management page.
     */
    public function showSessions(Request $request): View
    {
        $user = $request->user();

        $sessions = [];

        if (config('session.driver') === 'database') {
            $sessions = \DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($session) use ($request) {
                    return [
                        'id' => $session->id,
                        'ip_address' => $session->ip_address,
                        'user_agent' => $session->user_agent,
                        'last_activity' => Carbon::createFromTimestamp($session->last_activity)->toIso8601String(),
                        'is_current' => $session->id === $request->session()->getId(),
                    ];
                });
        }

        return view('account.security-sessions', compact('sessions'));
    }

    /**
     * Generate 2FA setup secret.
     */
    public function setupTwoFactor(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled.',
            ], 422);
        }

        $secret = $user->generateTwoFactorSecret();

        // Save temporarily - will be confirmed in enableTwoFactor
        $request->session()->put('two_factor_secret', $secret);

        return response()->json([
            'secret' => $secret,
            'message' => 'Please verify with your authenticator app to enable 2FA.',
        ]);
    }

    /**
     * Enable 2FA with verified code.
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => 'required|string',
        ]);

        $user = $request->user();

        // Verify password before enabling 2FA
        if (! password_verify($request->password, $user->password)) {
            return response()->json([
                'message' => 'The password you entered is incorrect.',
            ], 422);
        }

        if ($user->isTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled.',
            ], 422);
        }

        $secret = $request->session()->get('two_factor_secret');

        if (! $secret) {
            return response()->json([
                'message' => 'Please start the 2FA setup process first.',
            ], 422);
        }

        // Verify the code
        $tempUser = new User;
        $tempUser->two_factor_secret = $secret;

        if (! $tempUser->verifyTwoFactorCode($request->code)) {
            return response()->json([
                'message' => 'Invalid verification code. Please try again.',
            ], 422);
        }

        // Enable 2FA
        $user->enableTwoFactor($secret);
        $user->save();

        $request->session()->forget('two_factor_secret');

        // Log activity
        $this->activityService->logTwoFactorEnabled($user);

        return response()->json([
            'recovery_codes' => $user->two_factor_recovery_codes,
            'message' => 'Two-factor authentication has been enabled successfully.',
        ]);
    }

    /**
     * Disable 2FA.
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (! $user->isTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 422);
        }

        // Verify password
        if (! password_verify($request->password, $user->password)) {
            return response()->json([
                'message' => 'The password you entered is incorrect.',
            ], 422);
        }

        $user->disableTwoFactor();
        $user->save();

        // Log activity
        $this->activityService->logTwoFactorDisabled($user);

        return response()->json([
            'message' => 'Two-factor authentication has been disabled.',
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (! $user->isTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 422);
        }

        // Verify password
        if (! password_verify($request->password, $user->password)) {
            return response()->json([
                'message' => 'The password you entered is incorrect.',
            ], 422);
        }

        $codes = $user->regenerateRecoveryCodes();

        return response()->json([
            'recovery_codes' => $codes,
            'message' => 'Recovery codes have been regenerated.',
        ]);
    }

    /**
     * Get active sessions.
     */
    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get sessions from database (if using database session driver)
        $sessions = [];

        if (config('session.driver') === 'database') {
            $sessions = \DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($session) use ($request) {
                    return [
                        'id' => $session->id,
                        'ip_address' => $session->ip_address,
                        'user_agent' => $session->user_agent,
                        'last_activity' => Carbon::createFromTimestamp($session->last_activity)->toIso8601String(),
                        'is_current' => $session->id === $request->session()->getId(),
                    ];
                });
        }

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    /**
     * Revoke a session.
     */
    public function revokeSession(Request $request, string $sessionId): JsonResponse
    {
        $user = $request->user();

        if (config('session.driver') === 'database') {
            $session = \DB::table('sessions')
                ->where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if (! $session) {
                return response()->json([
                    'message' => 'Session not found.',
                ], 404);
            }

            // Don't revoke current session
            if ($session->id === $request->session()->getId()) {
                return response()->json([
                    'message' => 'Cannot revoke your current session.',
                ], 422);
            }

            \DB::table('sessions')
                ->where('id', $sessionId)
                ->delete();

            // Log activity
            $this->activityService->log('session_revoked', $user, [
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'message' => 'Session revoked successfully.',
        ]);
    }

    /**
     * Revoke all sessions except current.
     */
    public function revokeAllSessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        if (config('session.driver') === 'database') {
            \DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->delete();

            // Log activity
            $this->activityService->log('all_sessions_revoked', $user, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'message' => 'All other sessions have been revoked.',
        ]);
    }
}
