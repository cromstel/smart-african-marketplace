@extends('admin.layout')

@section('title', 'Referral Settings')
@section('header', 'Referral Settings')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.referrals.index') }}" class="text-gray-400 hover:text-white inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Referrals
    </a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
    <h3 class="text-xl font-bold text-white mb-6">Referral Program Settings</h3>
    
    <form action="{{ route('admin.referrals.settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Enable Referrals -->
            <div class="flex items-center justify-between">
                <div>
                    <label class="text-white font-medium">Enable Referral Program</label>
                    <p class="text-gray-400 text-sm mt-1">Allow customers to refer new users and earn rewards</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="referral_enabled" value="1" 
                        {{ old('referral_enabled', \App\Models\Setting::get('referral_enabled', true)) ? 'checked' : '' }}
                        class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>
            
            <!-- Reward Amount -->
            <div>
                <label class="block text-white font-medium mb-2">Reward Amount ($)</label>
                <p class="text-gray-400 text-sm mb-2">The amount the referrer earns when a referral is completed</p>
                <input type="number" name="referral_reward_amount" step="0.01" min="0"
                    value="{{ old('referral_reward_amount', \App\Models\Setting::get('referral_reward_amount', 10)) }}"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('referral_reward_amount')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Minimum Purchase -->
            <div>
                <label class="block text-white font-medium mb-2">Minimum Purchase Amount ($)</label>
                <p class="text-gray-400 text-sm mb-2">The minimum purchase amount required for a referral to be considered completed</p>
                <input type="number" name="referral_min_purchase" step="0.01" min="0"
                    value="{{ old('referral_min_purchase', \App\Models\Setting::get('referral_min_purchase', 20)) }}"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('referral_min_purchase')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-700">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                Save Settings
            </button>
        </div>
    </form>
</div>

<!-- How it works -->
<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mt-6">
    <h4 class="text-lg font-semibold text-white mb-4">How the Referral Program Works</h4>
    <div class="space-y-3 text-gray-300">
        <div class="flex items-start">
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3 mt-0.5">1</span>
            <p>Customers share their unique referral code with friends</p>
        </div>
        <div class="flex items-start">
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3 mt-0.5">2</span>
            <p>New users sign up using the referral code</p>
        </div>
        <div class="flex items-start">
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3 mt-0.5">3</span>
            <p>When the referred user makes a purchase above the minimum amount, the referral becomes eligible</p>
        </div>
        <div class="flex items-start">
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3 mt-0.5">4</span>
            <p>Admin reviews and approves the referral to pay the reward to the referrer</p>
        </div>
    </div>
</div>
@endsection

