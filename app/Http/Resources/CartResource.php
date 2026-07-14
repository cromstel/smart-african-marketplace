<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'shipping' => $this->shipping,
            'discount' => $this->discount,
            'total' => $this->total,
            'coupon_code' => $this->coupon_code,
            'items' => CartItemResource::collection($this->whenLoaded('items') ?? []),
            'item_count' => $this->whenLoaded('items', fn () => $this->items->sum('quantity')),
        ];
    }
}
