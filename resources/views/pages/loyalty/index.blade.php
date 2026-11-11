@extends('layouts.shop')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Loyalty Program</h2>
            <p class="text-xl text-orange-50">Earn points with every purchase</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <div class="text-center mb-8">
                    <p class="text-gray-600 mb-2">Your Points Balance</p>
                    <p class="text-5xl font-bold text-orange-600">{{ number_format($balance) }}</p>
                    <p class="text-sm text-gray-600 mt-2">≈ ${{ number_format($balance * $pointsValue, 2) }} discount value</p>
                </div>

                <div class="grid md:grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <div class="text-3xl mb-2">🛍️</div>
                        <h3 class="font-semibold text-gray-900 mb-1">Earn Points</h3>
                        <p class="text-sm text-gray-600">Get points from purchases and activity</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl mb-2">💰</div>
                        <h3 class="font-semibold text-gray-900 mb-1">Redeem</h3>
                        <p class="text-sm text-gray-600">Apply points as checkout discount</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl mb-2">⏳</div>
                        <h3 class="font-semibold text-gray-900 mb-1">Expiration Policy</h3>
                        <p class="text-sm text-gray-600">Earned points are valid for 1 year</p>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Redeem Points</h2>
                    <p class="text-sm text-gray-600 mb-4">Minimum redemption: {{ number_format($minRedeemPoints) }} points</p>
                    <form action="{{ route('loyalty.redeem') }}" method="POST" class="space-y-3">
                        @csrf
                        <label for="redeem-points" class="block text-sm font-medium text-gray-700">Points to Redeem</label>
                        <input id="redeem-points" type="number" name="points" min="{{ $minRedeemPoints }}" max="{{ $balance }}" value="{{ old('points', $minRedeemPoints) }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 focus:border-orange-500 focus:outline-none">
                        <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 text-white py-3 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 transition">
                            Apply Loyalty Discount to Cart
                        </button>
                    </form>
                    @if($expiringSoon > 0)
                        <p class="mt-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded p-3">
                            {{ number_format($expiringSoon) }} point(s) will expire in the next 30 days.
                        </p>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Tier Progress</h2>
                    <p class="text-sm text-gray-700 mb-2">Current tier: <span class="font-semibold">{{ $tierProgress['current']['name'] }}</span></p>
                    @if($tierProgress['next'])
                        <p class="text-sm text-gray-600 mb-3">Next tier: {{ $tierProgress['next']['name'] }} ({{ number_format($tierProgress['points_to_next']) }} points to go)</p>
                    @else
                        <p class="text-sm text-green-700 mb-3">You are at the highest tier.</p>
                    @endif
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 h-3 rounded-full" style="width: {{ $tierProgress['progress_percent'] }}%"></div>
                    </div>
                    <p class="text-xs text-gray-600 mb-4">{{ $tierProgress['progress_percent'] }}% progress in current tier</p>
                    <div class="space-y-2">
                        <h3 class="font-semibold text-gray-900">Current Benefits</h3>
                        @foreach($tierProgress['current']['benefits'] as $benefit)
                            <p class="text-sm text-gray-700">• {{ $benefit }}</p>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Earning Opportunities</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($earningOpportunities as $opportunity)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900">{{ $opportunity['title'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ number_format($opportunity['points']) }} {{ $opportunity['unit'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Points History</h2>

                @if($history->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 text-gray-700">Date</th>
                                    <th class="text-left py-3 px-4 text-gray-700">Description</th>
                                    <th class="text-right py-3 px-4 text-gray-700">Points</th>
                                    <th class="text-right py-3 px-4 text-gray-700">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $entry)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 px-4 text-gray-700">{{ $entry->created_at->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 text-gray-700">{{ $entry->description }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <span class="{{ $entry->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $entry->points > 0 ? '+' : '' }}{{ number_format($entry->points) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right text-gray-700">{{ number_format((int) $entry->balance_after) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $history->links() }}
                    </div>
                @else
                    <div class="text-center py-12 text-gray-600">
                        <p>No activity yet. Start shopping to earn points!</p>
                        <a href="{{ route('shop.index') }}" class="inline-block mt-4 bg-gradient-to-r from-orange-600 to-red-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 transition">
                            Shop Now
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

