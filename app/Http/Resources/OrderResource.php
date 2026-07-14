<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'is_parent' => $this->is_parent,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'shipping_cost' => $this->shipping_cost,
            'discount' => $this->discount,
            'total' => $this->total,
            'coupon_code' => $this->coupon_code,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'tracking_number' => $this->tracking_number,
            'customer_notes' => $this->customer_notes,
            'paid_at' => $this->paid_at,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items') ?? []),
            'billing_address' => $this->whenLoaded('billingAddress', fn () => new AddressResource($this->billingAddress)),
            'shipping_address' => $this->whenLoaded('shippingAddress', fn () => new AddressResource($this->shippingAddress)),
            'payment' => $this->whenLoaded('payment', fn () => [
                'id' => $this->payment->id,
                'payment_method' => $this->payment->payment_method,
                'amount' => $this->payment->amount,
                'currency' => $this->payment->currency,
                'status' => $this->payment->status,
                'transaction_id' => $this->payment->transaction_id,
            ]),
        ];
    }
}
