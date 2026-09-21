@extends('admin.layout')

@section('title', 'Payment Settings')
@section('header', 'Payment Gateway Settings')

@section('content')
<div class="max-w-4xl">
    <!-- Flash Messages -->
    @if(session('warning'))
        <div class="mb-4 p-4 bg-yellow-900/50 border border-yellow-700 rounded-lg text-yellow-200">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                {{ session('warning') }}
            </div>
        </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-900/50 border border-red-700 rounded-lg text-red-200">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <strong>Please fix the following errors:</strong>
            </div>
            <ul class="list-disc list-inside ml-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex items-center justify-between mb-6">
        <p class="text-gray-400">Configure payment gateways for your store. API keys are stored securely.</p>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.export') }}" 
               class="inline-flex items-center gap-2 px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export
            </a>
            <form method="POST" action="{{ route('admin.settings.import') }}" enctype="multipart/form-data" class="inline">
                @csrf
                <label class="inline-flex items-center gap-2 px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import
                    <input type="file" name="import_file" class="hidden" accept=".json" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('admin.settings.audit') }}" 
               class="inline-flex items-center gap-2 px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Audit Log
            </a>
            <form method="POST" action="{{ route('admin.settings.clear-cache') }}" class="inline">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors"
                        title="Clear configuration cache">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Clear Cache
                </button>
            </form>
        </div>
    </div>

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

                <div class="border-t border-gray-700 pt-4 mt-4">
                    <h3 class="text-lg font-semibold text-white mb-3">M-Pesa (Daraja) Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Consumer Key</label>
                            <input type="text" name="mpesa_consumer_key" value="{{ $settings['mpesa_consumer_key'] ?? '' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                   placeholder="Consumer key">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Consumer Secret</label>
                            <input type="password" name="mpesa_consumer_secret" value="{{ $settings['mpesa_consumer_secret'] ?? '' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                   placeholder="Consumer secret">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Shortcode</label>
                            <input type="text" name="mpesa_shortcode" value="{{ $settings['mpesa_shortcode'] ?? '' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                   placeholder="174379">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Passkey</label>
                            <input type="password" name="mpesa_passkey" value="{{ $settings['mpesa_passkey'] ?? '' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                                   placeholder="Lipa na M-Pesa passkey">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Environment</label>
                            <select name="mpesa_environment" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                <option value="sandbox" {{ ($settings['mpesa_environment'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                <option value="production" {{ ($settings['mpesa_environment'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Production</option>
                            </select>
                        </div>
                    </div>
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

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Push Notifications (FCM)</h2>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="fcm_enabled" value="1" {{ $settings['fcm_enabled'] ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-300">Enabled</span>
                </label>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        FCM Server Key
                        <span class="text-gray-500">(Firebase Cloud Messaging key)</span>
                    </label>
                    <input type="password" name="fcm_server_key" value="{{ $settings['fcm_server_key'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="AAAA...">
                </div>
                <div class="bg-gray-900 border border-gray-700 rounded p-4">
                    <p class="text-sm text-gray-400">
                        Store your FCM server key to enable push delivery to user `fcm_token` values from the notification center.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Email (SMTP) Settings</h2>
            <p class="text-sm text-gray-400 mb-6">Configure SMTP settings to send transactional emails (order confirmations, subscription notifications, etc.)</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Mail Host
                        <span class="text-gray-500">(SMTP server address)</span>
                    </label>
                    <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="smtp.gmail.com">
                    <p class="text-xs text-gray-500 mt-1">
                        Common: smtp.gmail.com, smtp.mailtrap.io, smtp.sendgrid.net, smtp-relay.sendinblue.com
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Mail Port
                        </label>
                        <input type="number" name="mail_port" value="{{ $settings['mail_port'] ?? 587 }}"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="587">
                        <p class="text-xs text-gray-500 mt-1">Common: 587 (TLS), 465 (SSL), 25 (no encryption)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Encryption
                        </label>
                        <select name="mail_encryption" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                            <option value="ssl" {{ ($settings['mail_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="" {{ empty($settings['mail_encryption']) ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Username
                        <span class="text-gray-500">(SMTP username / Email address)</span>
                    </label>
                    <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="your-email@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Password
                        <span class="text-gray-500">(SMTP password / App password)</span>
                    </label>
                    <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="••••••••">
                    <p class="text-xs text-gray-500 mt-1">
                        For Gmail, use App Password (not your account password)
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            From Email
                        </label>
                        <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? '' }}"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="noreply@yourstore.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            From Name
                        </label>
                        <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? config('app.name') }}"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                               placeholder="{{ config('app.name') }}">
                    </div>
                </div>

                <div class="bg-gray-900 border border-gray-700 rounded p-4">
                    <p class="text-sm text-gray-400 mb-3">
                        <strong class="text-white">SMTP Provider Setup:</strong>
                    </p>
                    <div class="space-y-2 text-xs text-gray-400">
                        <p><strong class="text-white">Gmail:</strong> Host: smtp.gmail.com, Port: 587, Encryption: TLS<br>
                        Enable 2FA and create an <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-indigo-400 hover:text-indigo-300">App Password</a></p>
                        
                        <p><strong class="text-white">SendGrid:</strong> Host: smtp.sendgrid.net, Port: 587, Encryption: TLS<br>
                        Username: apikey, Password: Your SendGrid API Key</p>
                        
                        <p><strong class="text-white">Mailtrap (Testing):</strong> Host: smtp.mailtrap.io, Port: 587<br>
                        Get credentials from <a href="https://mailtrap.io" target="_blank" class="text-indigo-400 hover:text-indigo-300">Mailtrap.io</a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Email Section - Outside main form -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Test Email Configuration</h3>
            <form method="POST" action="{{ route('admin.settings.test-email') }}" class="flex items-end gap-4">
                @csrf
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Test Email Address
                    </label>
                    <input type="email" name="test_email" value=""
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="your-email@example.com">
                </div>
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                    Send Test Email
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-2">
                Click "Send Test Email" to verify your SMTP configuration. Make sure to save settings first.
            </p>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Brand Identity Colors</h2>
            <p class="text-sm text-gray-400 mb-6">Customize the gradient colors used across hero sections and branding elements</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Gradient From (Primary Color)</label>
                    <div class="flex items-center gap-4">
                        <input type="color" name="brand_color_from" value="{{ \App\Models\Setting::getValue('brand_color_from', '#ea580c') }}" 
                               class="w-20 h-12 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer">
                        <input type="text" value="{{ \App\Models\Setting::getValue('brand_color_from', '#ea580c') }}" 
                               readonly class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Default: #ea580c (Orange)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Gradient To (Secondary Color)</label>
                    <div class="flex items-center gap-4">
                        <input type="color" name="brand_color_to" value="{{ \App\Models\Setting::getValue('brand_color_to', '#dc2626') }}" 
                               class="w-20 h-12 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer">
                        <input type="text" value="{{ \App\Models\Setting::getValue('brand_color_to', '#dc2626') }}" 
                               readonly class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Default: #dc2626 (Red)</p>
                </div>
            </div>

            <div class="mt-6 bg-gray-900 border border-gray-700 rounded-lg p-4">
                <p class="text-sm text-gray-300 mb-3"><strong class="text-white">Preview:</strong></p>
                <div id="brand-preview" class="h-24 rounded-lg" style="background: linear-gradient(to right, {{ \App\Models\Setting::getValue('brand_color_from', '#ea580c') }}, {{ \App\Models\Setting::getValue('brand_color_to', '#dc2626') }});">
                    <div class="h-full flex items-center justify-center">
                        <span class="text-white text-lg font-bold drop-shadow-lg">Hero Section Preview</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile App Settings -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Mobile App Configuration</h2>
            <p class="text-gray-400 text-sm mb-6">Configure mobile app download URLs, versions, and support links.</p>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Android Settings -->
                <div class="bg-gray-900 border border-gray-700 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.523 2.047a.5.5 0 00-.612.516l-1.026 4.636L9.622 5.404a.5.5 0 00-.484-.32L3.6 6.64A.5.5 0 003 7.118v9.405c0 .299.17.572.423.69l6.555 3.042a.5.5 0 00.484.32l5.543-1.205a.5.5 0 00.423-.69l.623-4.597.378-2.171a.5.5 0 00-.1-.347l-2.322-4.31zM15.5 9.5l-2.45 4.1-3.14-1.47L15.5 9.5z"/>
                        </svg>
                        Android
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Version</label>
                            <input type="text" name="mobile_android_version" value="{{ $settings['mobile_android_version'] ?? '1.0.0' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="1.0.0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Build Number</label>
                            <input type="text" name="mobile_android_build" value="{{ $settings['mobile_android_build'] ?? '1' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Download URL</label>
                            <input type="url" name="mobile_android_url" value="{{ $settings['mobile_android_url'] ?? '' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="https://example.com/app.apk">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Minimum OS Version</label>
                            <input type="text" name="mobile_android_min_os" value="{{ $settings['mobile_android_min_os'] ?? '5.0' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="5.0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">SHA256 Checksum (Optional)</label>
                            <input type="text" name="mobile_android_sha256" value="{{ $settings['mobile_android_sha256'] ?? '' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono text-sm" placeholder="SHA256 hash">
                        </div>
                    </div>
                </div>

                <!-- iOS Settings -->
                <div class="bg-gray-900 border border-gray-700 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                        </svg>
                        iOS
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Version</label>
                            <input type="text" name="mobile_ios_version" value="{{ $settings['mobile_ios_version'] ?? '1.0.0' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="1.0.0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Build Number</label>
                            <input type="text" name="mobile_ios_build" value="{{ $settings['mobile_ios_build'] ?? '1' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">App Store URL</label>
                            <input type="url" name="mobile_ios_app_store_url" value="{{ $settings['mobile_ios_app_store_url'] ?? '' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="https://apps.apple.com/...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Minimum iOS Version</label>
                            <input type="text" name="mobile_ios_min_os" value="{{ $settings['mobile_ios_min_os'] ?? '12.0' }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="12.0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Links -->
            <div class="mt-6 grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Changelog URL</label>
                    <input type="url" name="mobile_changelog_url" value="{{ $settings['mobile_changelog_url'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="https://example.com/changelog">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Installation Guide URL</label>
                    <input type="url" name="mobile_install_instructions_url" value="{{ $settings['mobile_install_instructions_url'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="https://example.com/install">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Contact Support URL</label>
                    <input type="url" name="mobile_contact_support_url" value="{{ $settings['mobile_contact_support_url'] ?? '' }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="https://example.com/support">
                </div>
            </div>

            <div class="mt-4 bg-gray-900 border border-gray-700 rounded-lg p-4">
                <p class="text-sm text-gray-400">
                    <strong class="text-white">Preview:</strong> 
                    <a href="{{ route('mobile.index') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300">{{ route('mobile.index') }}</a>
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-brand-from hover:bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    const textInput = colorInput.parentElement.querySelector('input[type="text"]');
    colorInput.addEventListener('input', function() {
        textInput.value = this.value;
        updatePreview();
    });
});

function updatePreview() {
    const colorFrom = document.querySelector('input[name="brand_color_from"]').value;
    const colorTo = document.querySelector('input[name="brand_color_to"]').value;
    document.getElementById('brand-preview').style.background = `linear-gradient(to right, ${colorFrom}, ${colorTo})`;
}
</script>
@endsection

