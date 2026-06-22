@extends('layouts.shop')

@section('content')
<div class="container mx-auto px-4 py-16">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Welcome Aboard!</h2>
            <p class="text-lg text-gray-600 mb-8">
                Your subscription has been successfully activated. Your free trial starts now!
            </p>

            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h3 class="font-bold text-gray-900 mb-3">What's Next?</h3>
                <div class="space-y-3 text-left">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Set Up Your Store</p>
                            <p class="text-sm text-gray-600">Add your first products and start selling</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Explore Features</p>
                            <p class="text-sm text-gray-600">Discover all the tools included in your plan</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Enjoy Your Trial</p>
                            <p class="text-sm text-gray-600">No charges for 30 days - cancel anytime</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                @if(auth()->user()->isVendor())
                    <a href="{{ route('vendor.dashboard') }}" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('shop.index') }}" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                        Start Shopping
                    </a>
                @endif

                <a href="{{ route('subscriptions.my') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold transition-colors">
                    View Subscription
                </a>
            </div>

            <p class="text-sm text-gray-500 mt-6">
                A confirmation email has been sent to {{ auth()->user()->email }}
            </p>
        </div>
    </div>
</div>
@endsection

