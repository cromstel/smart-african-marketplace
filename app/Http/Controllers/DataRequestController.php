<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DataRequestController extends Controller
{
    public function export()
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->with(['items', 'billingAddress', 'shippingAddress'])
            ->get();

        $payload = [
            'user' => $user->only(['id', 'name', 'email', 'phone', 'created_at']),
            'addresses' => $user->addresses()->get(),
            'orders' => $orders,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT);

        return Response::streamDownload(function () use ($json) {
            echo $json;
        }, 'data-export-'.$user->id.'.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $timestamp = now()->format('YmdHis');

        $user->update([
            'is_active' => false,
            'name' => 'Deleted User',
            'email' => 'deleted-'.$timestamp.'-'.$user->email,
        ]);

        Auth::logout();

        return redirect()->route('home')->with('success', 'Your account has been deactivated.');
    }
}
