<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SystemHealthController extends Controller
{
    public function index()
    {
        $dbStatus = 'ok';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'error';
        }

        $health = [
            'app' => config('app.env'),
            'queue' => config('queue.default'),
            'cache' => config('cache.default'),
            'db' => $dbStatus,
        ];

        return view('admin.health.index', compact('health'));
    }
}
