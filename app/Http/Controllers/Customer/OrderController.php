<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index()
    {
        return view('customer-console.orders.index', [
            'title' => 'My Orders',
            'orders' => [],
        ]);
    }

    public function show($order)
    {
        return view('customer-console.orders.show', [
            'title' => 'Order Details',
            'order' => null,
        ]);
    }
}
