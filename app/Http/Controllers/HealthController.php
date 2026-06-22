<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => 'ok',
            'database' => 'ok',
            'cache_driver' => config('cache.default'),
            'queue_connection' => config('queue.default'),
            'mail_mailer' => config('mail.default'),
        ];

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $exception) {
            $checks['database'] = 'down';

            return response()->json([
                'status' => 'degraded',
                'checks' => $checks,
            ], 503);
        }

        return response()->json([
            'status' => 'ok',
            'checks' => $checks,
        ]);
    }
}
