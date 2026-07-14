<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'compare_price' => $this->compare_price,
            'stock_quantity' => $this->stock_quantity,
            'stock_status' => $this->stock_status,
            'weight' => $this->weight,
            'origin_country' => $this->origin_country,
            'attributes' => $this->attributes,
            'featured_image' => $this->featured_image,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'sales_count' => $this->sales_count,
            'views_count' => $this->views_count,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'brand' => $this->whenLoaded('brand', fn () => new BrandResource($this->brand)),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'image' => $img->image,
                'sort_order' => $img->sort_order,
            ])->values()),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'price' => $v->price,
                'stock_quantity' => $v->stock_quantity,
                'stock_status' => $v->stock_status,
                'attributes' => $v->attributes,
            ])->values()),
            'reviews' => $this->whenLoaded('reviews', fn () => $this->reviews->take(10)->map(fn ($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
                'created_at' => $r->created_at,
            ])->values()),
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'slug' => $this->vendor->slug,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
