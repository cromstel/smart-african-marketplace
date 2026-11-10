<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function index()
    {
        return view('salon-console.bookings.index', [
            'title' => 'Manage Bookings',
            'bookings' => [],
        ]);
    }

    public function show($booking)
    {
        return view('salon-console.bookings.show', [
            'title' => 'Booking Details',
            'booking' => null,
        ]);
    }
}
