<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;

class VendorProfileController extends Controller
{
    public function show(User $vendor)
    {
        if ($vendor->role !== 'vendor') {
            abort(404);
        }

        $products = Product::where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->latest()
            ->paginate(12);

        return view('pages.vendor-profile', compact('vendor', 'products'));
    }
}
