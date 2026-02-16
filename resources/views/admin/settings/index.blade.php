@extends('admin.layout')

@section('title', 'Payment Settings')
@section('header', 'Payment Gateway Settings')

@section('content')
<div class="max-w-4xl">
    <p class="text-gray-400 mb-6">Configure payment gateways for your store. API keys are stored securely.</p>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Stripe Payment Gateway</h2>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="stripe_enabled" value="1" {{ $settings['stripe_enabled'] ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-300">Enabled</span>
                </label>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Publishable Key
                        <span class="text-gray-500">(pk_test_... or pk_live_...)</span>
                    </label>
                    <input type="text" name="stripe_public_key" value="{{ $settings['stripe_public_key'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="pk_test_...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Secret Key
                        <span class="text-gray-500">(sk_test_... or sk_live_...)</span>
                    </label>
                    <input type="password" name="stripe_secret_key" value="{{ $settings['stripe_secret_key'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="sk_test_...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Webhook Secret (Optional)
                        <span class="text-gray-500">(whsec_...)</span>
                    </label>
                    <input type="password" name="stripe_webhook_secret" value="{{ $settings['stripe_webhook_secret'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="whsec_...">
                </div>

                <div class="bg-gray-900 border border-gray-700 rounded p-4">
                    <p class="text-sm text-gray-400 mb-3">
                        <strong class="text-white">Get your Stripe API keys:</strong><br>
                        1. Sign up or log in to <a href="https://dashboard.stripe.com" target="_blank" class="text-indigo-400 hover:text-indigo-300">Stripe Dashboard</a><br>
                        2. Go to Developers → API keys<br>
                        3. Copy the Publishable key and Secret key<br>
                        4. Use test keys for testing and live keys for production
                    </p>
                    <p class="text-sm text-gray-400">
                        <strong class="text-white">Webhook Configuration:</strong><br>
                        Add this webhook URL in Stripe Dashboard → Developers → Webhooks:<br>
                        <code class="bg-gray-800 px-2 py-1 rounded text-indigo-400">{{ config('app.url') }}/webhooks/stripe</code><br>
                        <span class="text-xs">Events to listen for: charge.succeeded, charge.failed, payment_intent.succeeded, payment_intent.payment_failed</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Paystack Payment Gateway</h2>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="paystack_enabled" value="1" {{ $settings['paystack_enabled'] ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-300">Enabled</span>
                </label>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Public Key
                        <span class="text-gray-500">(pk_test_... or pk_live_...)</span>
                    </label>
                    <input type="text" name="paystack_public_key" value="{{ $settings['paystack_public_key'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="pk_test_...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Secret Key
                        <span class="text-gray-500">(sk_test_... or sk_live_...)</span>
                    </label>
                    <input type="password" name="paystack_secret_key" value="{{ $settings['paystack_secret_key'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="sk_test_...">
                </div>

                <div class="bg-gray-900 border border-gray-700 rounded p-4">
                    <p class="text-sm text-gray-400 mb-3">
                        <strong class="text-white">Get your Paystack API keys:</strong><br>
                        1. Sign up or log in to <a href="https://dashboard.paystack.com" target="_blank" class="text-indigo-400 hover:text-indigo-300">Paystack Dashboard</a><br>
                        2. Go to Settings → API Keys & Webhooks<br>
                        3. Copy the Public key and Secret key<br>
                        4. Use test keys for testing and live keys for production
                    </p>
                    <p class="text-sm text-gray-400">
                        <strong class="text-white">Webhook Configuration:</strong><br>
                        Add this webhook URL in Paystack Dashboard → Settings → API Keys & Webhooks:<br>
                        <code class="bg-gray-800 px-2 py-1 rounded text-indigo-400">{{ config('app.url') }}/webhooks/paystack</code><br>
                        <span class="text-xs">Events to listen for: charge.success, charge.failed</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Mobile Money (MTN/Airtel)</h2>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="momo_enabled" value="1" {{ $settings['momo_enabled'] ?? false ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-300">Enabled</span>
                </label>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        API Key
                        <span class="text-gray-500">(Your MOMO API Key)</span>
                    </label>
                    <input type="text" name="momo_api_key" value="{{ $settings['momo_api_key'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="momo_api_key_...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        API Secret
                        <span class="text-gray-500">(Your MOMO API Secret)</span>
                    </label>
                    <input type="password" name="momo_api_secret" value="{{ $settings['momo_api_secret'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="momo_secret_...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Collection ID (User ID)
                        <span class="text-gray-500">(Your MOMO User ID)</span>
                    </label>
                    <input type="text" name="momo_user_id" value="{{ $settings['momo_user_id'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="user_id">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Environment
                    </label>
                    <select name="momo_environment" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="sandbox" {{ ($settings['momo_environment'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                        <option value="production" {{ ($settings['momo_environment'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Production (Live)</option>
                    </select>
                </div>

                <div class="bg-gray-900 border border-gray-700 rounded p-4">
                    <p class="text-sm text-gray-400 mb-3">
                        <strong class="text-white">Configure Mobile Money:</strong><br>
                        1. Register for MTN MOMO API at <a href="https://momodeveloper.mtn.com" target="_blank" class="text-indigo-400 hover:text-indigo-300">MTN Developer Portal</a><br>
                        2. Subscribe to Collections product<br>
                        3. Create API User and generate API Key<br>
                        4. Use sandbox for testing, production for live transactions
                    </p>
                    <p class="text-sm text-gray-400">
                        <strong class="text-white">Supported Networks:</strong> MTN Mobile Money, Airtel Money, and other regional providers
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Cash on Delivery</h2>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="cod_enabled" value="1" {{ $settings['cod_enabled'] ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-300">Enabled</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    COD Fee (Optional)
                    <span class="text-gray-500">Extra charge for cash on delivery</span>
                </label>
                <input type="number" name="cod_fee" value="{{ $settings['cod_fee'] }}" min="0" step="0.01"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                       placeholder="0.00">
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Inventory Settings</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Low Stock Threshold
                        <span class="text-gray-500">(Alert when stock falls below this number)</span>
                    </label>
                    <input type="number" name="low_stock_threshold" value="{{ $settings['low_stock_threshold'] }}"
                           min="1" max="100"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="10">
                    <p class="text-xs text-gray-500 mt-1">
                        Daily email alerts will be sent to admins when products fall below this stock level.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">SMS Notifications (Twilio)</h2>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="sms_enabled" value="1" {{ $settings['sms_enabled'] ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-300">Enabled</span>
                </label>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Account SID
                        <span class="text-gray-500">(From Twilio Console)</span>
                    </label>
                    <input type="text" name="twilio_account_sid" value="{{ $settings['twilio_account_sid'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Auth Token
                        <span class="text-gray-500">(From Twilio Console)</span>
                    </label>
                    <input type="password" name="twilio_auth_token" value="{{ $settings['twilio_auth_token'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="Your auth token">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Phone Number
                        <span class="text-gray-500">(Your Twilio phone number)</span>
                    </label>
                    <input type="text" name="twilio_phone_number" value="{{ $settings['twilio_phone_number'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="+1234567890">
                    <p class="text-xs text-gray-500 mt-1">
                        SMS notifications will be sent for order status updates and booking confirmations.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
