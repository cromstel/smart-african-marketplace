@extends('layouts.shop')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="flex items-center gap-6">
                @php
                    $vendorProfileImage = $vendor->profile_image ?? null;
                @endphp
                @if($vendorProfileImage)
                    <img src="{{ '/storage/' . ltrim($vendorProfileImage ?? '', '/') }}" alt="{{ $vendor->name }} profile image" class="w-24 h-24 rounded-full object-cover border-4 border-white">
                @else
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center">
                        <span class="text-4xl font-bold text-orange-600">{{ substr($vendor->name, 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <h2 class="text-4xl md:text-5xl font-bold mb-2">{{ $vendor->name }}</h2>
                    <p class="text-orange-50">Verified Vendor</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-900">About this Vendor</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-3xl font-bold text-orange-600">{{ $products->total() }}</p>
                        <p class="text-gray-600">Products</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-3xl font-bold text-orange-600">4.8★</p>
                        <p class="text-gray-600">Rating</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-3xl font-bold text-orange-600">{{ $vendor->created_at->diffForHumans() }}</p>
                        <p class="text-gray-600">Member Since</p>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Products from {{ $vendor->name }}</h2>
                
                @if($products->count() > 0)
                    <div class="grid md:grid-cols-4 gap-6">
                        @foreach($products as $product)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                                <a href="{{ route('shop.show', $product) }}">
                                    @if($product->images->count() > 0)
                                        <img src="{{ '/storage/' . ltrim($product->images->first()->image_path ?? '', '/') }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                                    @else
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-400">No image</span>
                                        </div>
                                    @endif
                                    
                                    <div class="p-4">
                                        <h3 class="font-semibold text-gray-900 mb-2">{{ $product->name }}</h3>
                                        <p class="text-orange-600 font-bold">${{ number_format((float) $product->price, 2) }}</p>
                                        
                                        @if($product->stock > 0)
                                            <span class="text-xs text-green-600">In Stock</span>
                                        @else
                                            <span class="text-xs text-red-600">Out of Stock</span>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="text-center py-12 text-gray-600">
                        <p>This vendor hasn't listed any products yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

