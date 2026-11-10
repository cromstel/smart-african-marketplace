<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;

class EarningsController extends Controller
{
    public function index()
    {
        return view('salon-console.earnings.index', [
            'title' => 'Earnings',
            'totalEarnings' => 0,
            'earnings' => [],
        ]);
    }
}
