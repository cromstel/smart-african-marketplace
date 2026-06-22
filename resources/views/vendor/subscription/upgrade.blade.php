@extends('vendor.layout')

@section('title', 'Upgrade Subscription')
@section('header', 'Upgrade Subscription')

@section('content')
<div class="max-w-7xl">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white">Upgrade Your Subscription</h2>
        <p class="text-gray-400 mt-2">Choose the plan that fits your business</p>
    </div>

    @if($subscription && $subscription->isTrialing())
        <div class="bg-blue-900/50 border border-blue-700 text-blue-200 px-6 py-4 rounded-lg mb-8">
            <strong>Trial Status:</strong> {{ max(0, now()->diffInDays($subscription->trial_ends_at, false)) }} days remaining
        </div>
    @endif

    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6">
        @foreach($plans as $plan)
            <div class="bg-gray-800 rounded-xl border {{ $plan->is_popular ? 'border-orange-500 shadow-lg shadow-orange-500/20' : 'border-gray-700' }} overflow-hidden relative">
                @if($plan->is_popular)
                    <div class="absolute top-0 right-0 bg-gradient-to-r from-orange-600 to-red-600 text-white px-4 py-1 text-xs font-bold rounded-bl-lg">
                        MOST POPULAR
                    </div>
                @endif

                <div class="p-8">
                    <h3 class="text-2xl font-bold text-white mb-2">{{ $plan->name }}</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-white">${{ number_format($plan->price, 2) }}</span>
                        <span class="text-gray-400">/{{ $plan->billing_cycle }}</span>
                    </div>

                    <ul class="space-y-3 mb-8 max-h-64 overflow-y-auto pr-2">
                        @foreach($plan->features ?? [] as $feature)
                            <li class="flex items-start text-gray-300">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <form action="{{ route('vendor.subscription.process') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                        <button type="submit"
                                class="w-full {{ $plan->is_popular ? 'bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700' : 'bg-gray-700 hover:bg-gray-600' }} text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200">
                            Choose {{ $plan->name }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

