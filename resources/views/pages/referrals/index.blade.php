@extends('layouts.shop')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Referral Program</h2>
            <p class="text-xl text-orange-50">Invite friends and earn rewards</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Your Referral Code</h2>
                    <div class="bg-gray-50 rounded-lg p-6 mb-4">
                        <p class="text-3xl font-mono font-bold text-center text-orange-600">{{ auth()->user()->referral_code }}</p>
                    </div>
                    <button onclick="copyReferralCode()" class="w-full bg-gradient-to-r from-orange-600 to-red-600 text-white py-3 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 transition">
                        Copy Code
                    </button>
                    <p class="text-sm text-gray-600 mt-3 text-center">Share this code with friends to earn rewards</p>
                    <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <a href="https://wa.me/?text={{ urlencode('Use my referral code '.auth()->user()->referral_code.' to sign up: '.$referralShareUrl) }}" target="_blank" rel="noopener" class="text-center bg-green-500 hover:bg-green-600 text-white text-sm py-2 rounded">WhatsApp</a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode('Join using my referral code '.auth()->user()->referral_code.' '.$referralShareUrl) }}" target="_blank" rel="noopener" class="text-center bg-sky-500 hover:bg-sky-600 text-white text-sm py-2 rounded">X/Twitter</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referralShareUrl) }}" target="_blank" rel="noopener" class="text-center bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 rounded">Facebook</a>
                        <a href="mailto:?subject={{ urlencode('Join with my referral code') }}&body={{ urlencode('Use my referral code '.auth()->user()->referral_code.' and sign up here: '.$referralShareUrl) }}" class="text-center bg-gray-700 hover:bg-gray-800 text-white text-sm py-2 rounded">Email</a>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Your Earnings</h2>
                    <div class="text-center mb-6">
                        <p class="text-gray-600 mb-2">Total Earnings</p>
                        <p class="text-5xl font-bold text-green-600">${{ number_format($earnings, 2) }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-center pt-6 border-t border-gray-200">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $referrals->total() }}</p>
                            <p class="text-sm text-gray-600">Total Referrals</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $referrals->where('status', 'completed')->count() }}</p>
                            <p class="text-sm text-gray-600">Successful</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">How It Works</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="bg-orange-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-orange-600">1</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Share Your Code</h3>
                        <p class="text-gray-600 text-sm">Give your referral code to friends</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-orange-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-orange-600">2</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">They Make a Purchase</h3>
                        <p class="text-gray-600 text-sm">Your friend uses your code at checkout</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-orange-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-orange-600">3</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">You Both Win</h3>
                        <p class="text-gray-600 text-sm">Earn rewards for each successful referral</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Your Referrals</h2>
                
                @if($referrals->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 text-gray-700">Date</th>
                                    <th class="text-left py-3 px-4 text-gray-700">Friend</th>
                                    <th class="text-left py-3 px-4 text-gray-700">Status</th>
                                    <th class="text-right py-3 px-4 text-gray-700">Reward</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($referrals as $referral)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 px-4 text-gray-700">{{ $referral->created_at->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 text-gray-700">{{ $referral->referred->name ?? 'N/A' }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                {{ $referral->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                   ($referral->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ ucfirst($referral->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right text-gray-700">
                                            {{ $referral->reward_amount ? '$'.number_format($referral->reward_amount, 2) : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $referrals->links() }}
                    </div>
                @else
                    <div class="text-center py-12 text-gray-600">
                        <p>No referrals yet. Start sharing your code to earn rewards!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralCode() {
    const code = '{{ auth()->user()->referral_code }}';
    navigator.clipboard.writeText(code).then(() => {
        alert('Referral code copied to clipboard!');
    });
}
</script>
@endsection

