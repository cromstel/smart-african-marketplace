<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('salon-console.dashboard', [
            'title' => 'Salon Dashboard',
            'totalServices' => 0,
            'totalBookings' => 0,
            'totalEarnings' => 0,
            'totalStylists' => 0,
        ]);
    }
}
