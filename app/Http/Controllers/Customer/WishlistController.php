<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class WishlistController extends Controller
{
    public function index()
    {
        return view('customer-console.wishlist.index', [
            'title' => 'Wishlist',
            'items' => [],
        ]);
    }
}
