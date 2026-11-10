<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function index()
    {
        return view('customer-console.bookings.index', [
            'title' => 'My Bookings',
            'bookings' => [],
        ]);
    }

    public function show($booking)
    {
        return view('customer-console.bookings.show', [
            'title' => 'Booking Details',
            'booking' => null,
        ]);
    }
}
