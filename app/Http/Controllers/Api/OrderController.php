<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product.images', 'billingAddress', 'shippingAddress'])
            ->latest()
            ->paginate($request->get('per_page', 10));

        return $this->successResponse(OrderResource::collection($orders), 'Orders retrieved successfully');
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $order->load(['items.product.images', 'billingAddress', 'shippingAddress', 'payment']);

        return $this->successResponse(new OrderResource($order), 'Order retrieved successfully');
    }
}
