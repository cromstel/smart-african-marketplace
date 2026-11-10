<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('customer-console.dashboard', [
            'title' => 'Customer Dashboard',
            'totalOrders' => 0,
            'totalBookings' => 0,
            'totalWishlist' => 0,
        ]);
    }
}
