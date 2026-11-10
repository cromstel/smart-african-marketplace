<?php

namespace App\Http\Controllers\Salon;

use App\Http\Controllers\Controller;

class StylistController extends Controller
{
    public function index()
    {
        return view('salon-console.stylists.index', [
            'title' => 'Manage Stylists',
            'stylists' => [],
        ]);
    }
}
