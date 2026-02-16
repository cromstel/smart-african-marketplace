<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderDelivered;
use App\Mail\OrderShipped;
use App\Models\Order;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderStatusController extends Controller
{
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        $oldStatus = $order->status;
        $order->status = $validated['status'];
        $smsService = app(SmsService::class);

        if ($validated['status'] === 'shipped') {
            $order->shipped_at = now();
            if ($request->tracking_number) {
                $order->tracking_number = $validated['tracking_number'];
            }
            Mail::to($order->user->email)->send(new OrderShipped($order));

            if ($order->user->phone) {
                $smsService->sendOrderShipped(
                    $order->user->phone,
                    $order->order_number,
                    $order->tracking_number
                );
            }
        } elseif ($validated['status'] === 'delivered') {
            $order->delivered_at = now();
            if (! $order->shipped_at) {
                $order->shipped_at = now();
            }
            Mail::to($order->user->email)->send(new OrderDelivered($order));

            if ($order->user->phone) {
                $smsService->sendOrderDelivered($order->user->phone, $order->order_number);
            }
        } elseif ($validated['status'] === 'cancelled') {
            $order->cancelled_at = now();
        }

        $order->save();

        return redirect()->back()->with('success', 'Order status updated successfully');
    }
}
