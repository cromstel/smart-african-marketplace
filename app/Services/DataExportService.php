<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;

class DataExportService
{
    public function export(User $user): string
    {
        $orders = Order::where('user_id', $user->id)
            ->with(['items', 'billingAddress', 'shippingAddress'])
            ->get();

        $payload = [
            'user' => $user->only(['id', 'name', 'email', 'phone', 'created_at']),
            'addresses' => $user->addresses()->get()->toArray(),
            'orders' => $orders->toArray(),
        ];

        return json_encode($payload, JSON_PRETTY_PRINT);
    }
}
