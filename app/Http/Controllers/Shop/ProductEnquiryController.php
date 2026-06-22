<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductEnquiry;
use Illuminate\Http\Request;

class ProductEnquiryController extends Controller
{
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ProductEnquiry::create([
            'product_id' => $product->id,
            'user_id' => auth()->id() ?? null,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your enquiry has been submitted successfully. The vendor will respond soon.',
            ]);
        }

        return redirect()->back()->with('success', 'Your enquiry has been submitted successfully.');
    }
}
