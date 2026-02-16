<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'stripe_enabled' => Setting::get('stripe_enabled', false),
            'stripe_public_key' => Setting::get('stripe_public_key', ''),
            'stripe_secret_key' => Setting::get('stripe_secret_key', ''),
            'stripe_webhook_secret' => Setting::get('stripe_webhook_secret', ''),
            'paystack_enabled' => Setting::get('paystack_enabled', false),
            'paystack_public_key' => Setting::get('paystack_public_key', ''),
            'paystack_secret_key' => Setting::get('paystack_secret_key', ''),
            'momo_enabled' => Setting::get('momo_enabled', false),
            'momo_api_key' => Setting::get('momo_api_key', ''),
            'momo_api_secret' => Setting::get('momo_api_secret', ''),
            'momo_user_id' => Setting::get('momo_user_id', ''),
            'momo_environment' => Setting::get('momo_environment', 'sandbox'),
            'cod_enabled' => Setting::get('cod_enabled', true),
            'cod_fee' => Setting::get('cod_fee', 0),
            'low_stock_threshold' => Setting::get('low_stock_threshold', 10),
            'sms_enabled' => Setting::get('sms_enabled', false),
            'twilio_account_sid' => Setting::get('twilio_account_sid', ''),
            'twilio_auth_token' => Setting::get('twilio_auth_token', ''),
            'twilio_phone_number' => Setting::get('twilio_phone_number', ''),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'stripe_enabled' => 'nullable|boolean',
            'stripe_public_key' => 'nullable|string',
            'stripe_secret_key' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
            'paystack_enabled' => 'nullable|boolean',
            'paystack_public_key' => 'nullable|string',
            'paystack_secret_key' => 'nullable|string',
            'momo_enabled' => 'nullable|boolean',
            'momo_api_key' => 'nullable|string',
            'momo_api_secret' => 'nullable|string',
            'momo_user_id' => 'nullable|string',
            'momo_environment' => 'nullable|in:sandbox,production',
            'cod_enabled' => 'nullable|boolean',
            'cod_fee' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1|max:100',
            'sms_enabled' => 'nullable|boolean',
            'twilio_account_sid' => 'nullable|string',
            'twilio_auth_token' => 'nullable|string',
            'twilio_phone_number' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        return redirect()->back()->with('success', 'Payment settings updated successfully');
    }
}
