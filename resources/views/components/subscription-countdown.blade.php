@props(['subscription'])

@php
    $isActive = $subscription && method_exists($subscription, 'isActive') && $subscription->isActive();

    $daysLeft = null;
    $isTrialing = false;
    $planName = 'Subscription';

    if ($isActive) {
        $isTrialing = method_exists($subscription, 'isTrialing') ? $subscription->isTrialing() : false;

        if (isset($subscription->plan_name)) {
            $planName = $subscription->plan_name ?: 'Subscription';
            $endDate = $subscription->trial_ends_at ?? $subscription->current_period_end;
        } else {
            $planName = $subscription->plan?->name ?? 'Subscription';
            $endDate = $subscription->trial_ends_at ?? $subscription->current_period_end;
        }

        if ($endDate) {
            $daysLeft = max(0, now()->diffInDays($endDate, false));
        }
    }

    $isExpiringSoon = $daysLeft !== null && $daysLeft <= 7;
    $statusType = $daysLeft !== null && $daysLeft <= 3 ? 'danger' : 'warning';
@endphp

@if($isActive && $isExpiringSoon)
    <div class="bg-{{ $statusType === 'danger' ? 'red' : 'orange' }}-50 border border-{{ $statusType === 'danger' ? 'red' : 'orange' }}-200 rounded-lg p-4 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-{{ $statusType === 'danger' ? 'red' : 'orange' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-{{ $statusType === 'danger' ? 'red' : 'orange' }}-800">
                        {{ $isTrialing ? 'Trial Ending Soon' : 'Subscription Expiring Soon' }}
                    </h3>
                    <p class="mt-1 text-sm text-{{ $statusType === 'danger' ? 'red' : 'orange' }}-700">
                        Your {{ $planName }} plan expires in <strong>{{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }}</strong>.
                        Upgrade now to avoid service interruption.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('vendor.subscription.upgrade') }}"
                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            Upgrade Now
                        </a>
                    </div>
                </div>
            </div>
            <button type="button" onclick="this.closest('[class*=\'bg-\']').remove()" class="flex-shrink-0 text-{{ $statusType === 'danger' ? 'red' : 'orange' }}-400 hover:text-{{ $statusType === 'danger' ? 'red' : 'orange' }}-600" aria-label="Dismiss">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
@endif

