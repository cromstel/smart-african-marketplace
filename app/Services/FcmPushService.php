<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    public function isConfigured(): bool
    {
        return (bool) Setting::get('fcm_enabled', false)
            && ! empty((string) Setting::get('fcm_server_key', ''));
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        $token = trim((string) ($user->fcm_token ?? ''));
        if ($token === '' || ! $this->isConfigured()) {
            return false;
        }

        if (! $user->notificationPreference('push_enabled', true)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key='.Setting::get('fcm_server_key'),
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

            if (! $response->successful()) {
                Log::warning('FCM push request failed', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (\Throwable $exception) {
            Log::error('FCM push exception: '.$exception->getMessage(), [
                'user_id' => $user->id,
            ]);

            return false;
        }
    }
}
