@extends('layouts.shop')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('subscriptions.index') }}" class="text-orange-600 hover:text-orange-700 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Plans
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-orange-600 to-red-600 p-8 text-white">
                <h2 class="text-3xl md:text-4xl font-bold mb-2">Complete Your Subscription</h2>
                <p class="text-orange-100">Start your {{ $plan->trial_days }}-day free trial today</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 p-8">
                <div class="md:col-span-2">
                    <form action="{{ route('subscriptions.subscribe') }}" method="POST" id="subscription-form">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Selected Plan</h2>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                                        <p class="text-gray-600 text-sm mt-1">{{ $plan->description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-3xl font-bold text-gray-900">${{ number_format($plan->price, 0) }}</p>
                                        <p class="text-gray-600 text-sm">/{{ $plan->billing_cycle === 'monthly' ? 'month' : ($plan->billing_cycle === 'yearly' ? 'year' : 'lifetime') }}</p>
                                    </div>
                                </div>

                                @if($plan->trial_days > 0)
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <p class="font-semibold text-green-900">{{ $plan->trial_days }} Days Free Trial</p>
                                                <p class="text-green-700 text-sm mt-1">
                                                    You won't be charged until {{ now()->addDays($plan->trial_days)->format('F j, Y') }}. 
                                                    Cancel anytime during your trial period at no cost.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Confirm Your Details</h2>
                            <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input type="text" value="{{ auth()->user()->name }}" readonly
                                           class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 text-gray-700">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" value="{{ auth()->user()->email }}" readonly
                                           class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 text-gray-700">
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <div class="flex items-start mb-4">
                                <input type="checkbox" id="terms" name="terms" required class="mt-1 w-4 h-4 text-orange-600 border-gray-300 rounded">
                                <label for="terms" class="ml-3 text-sm text-gray-700">
                                    I agree to the <a href="#" class="text-orange-600 hover:text-orange-700">Terms of Service</a> 
                                    and <a href="#" class="text-orange-600 hover:text-orange-700">Privacy Policy</a>. 
                                    I understand I can cancel my subscription at any time.
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white rounded-lg px-8 py-4 font-bold text-lg transition-colors">
                                Start Free Trial
                            </button>
                            <a href="{{ route('subscriptions.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg px-8 py-4 font-bold text-lg transition-colors text-center">
                                Cancel
                            </a>
                        </div>

                        <p class="text-center text-xs text-gray-500 mt-4">
                            Your trial starts today. You can cancel anytime before {{ now()->addDays($plan->trial_days)->format('M j, Y') }} and you won't be charged.
                        </p>
                    </form>
                </div>

                <div class="md:col-span-1">
                    <div class="bg-gray-50 rounded-lg p-6 sticky top-24">
                        <h3 class="font-bold text-gray-900 mb-4">What's Included</h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-700 text-sm">
                                    {{ $plan->max_products === -1 ? 'Unlimited' : $plan->max_products }} Products
                                </span>
                            </div>

                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-700 text-sm">
                                    {{ $plan->max_orders === -1 ? 'Unlimited' : $plan->max_orders }} Orders/month
                                </span>
                            </div>

                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-700 text-sm">
                                    {{ $plan->commission_rate }}% Platform Commission
                                </span>
                            </div>

                            @if($plan->features && count($plan->features) > 0)
                                @foreach($plan->features as $feature)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-gray-700 text-sm">{{ $feature }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-2">Need Help?</h4>
                            <p class="text-sm text-gray-600 mb-3">Have questions about this plan?</p>
                            <a href="{{ route('contact.index') }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                                Contact Support →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

