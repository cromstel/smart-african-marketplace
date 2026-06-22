<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigurationAudit;
use App\Models\ConfigurationCategory;
use App\Models\ConfigurationSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingController extends Controller
{
    /**
     * Display a listing of settings.
     */
    public function index(Request $request)
    {
        // Get settings from both legacy table and new ConfigurationSetting
        $legacySettings = $this->getLegacySettings();

        // Get new configuration categories with settings
        $categories = ConfigurationCategory::with(['settings' => function ($query) {
            $query->orderBy('sort_order');
        }])->where('is_active', true)->orderBy('sort_order')->get();

        $settings = $this->getLegacySettings();

        return view('admin.settings.index', compact('settings', 'categories'));
    }

    /**
     * Get legacy settings for backward compatibility.
     */
    protected function getLegacySettings(): array
    {
        $settings = [
            'stripe_enabled' => Setting::getValue('stripe_enabled', false),
            'stripe_public_key' => Setting::getValue('stripe_public_key', ''),
            'stripe_secret_key' => Setting::getDecrypted('stripe_secret_key', ''),
            'stripe_webhook_secret' => Setting::getDecrypted('stripe_webhook_secret', ''),
            'paystack_enabled' => Setting::getValue('paystack_enabled', false),
            'paystack_public_key' => Setting::getValue('paystack_public_key', ''),
            'paystack_secret_key' => Setting::getDecrypted('paystack_secret_key', ''),
            'momo_enabled' => Setting::getValue('momo_enabled', false),
            'momo_api_key' => Setting::getDecrypted('momo_api_key', ''),
            'momo_api_secret' => Setting::getDecrypted('momo_api_secret', ''),
            'momo_user_id' => Setting::getValue('momo_user_id', ''),
            'momo_environment' => Setting::getValue('momo_environment', 'sandbox'),
            'mpesa_consumer_key' => Setting::getValue('mpesa_consumer_key', ''),
            'mpesa_consumer_secret' => Setting::getDecrypted('mpesa_consumer_secret', ''),
            'mpesa_shortcode' => Setting::getValue('mpesa_shortcode', ''),
            'mpesa_passkey' => Setting::getDecrypted('mpesa_passkey', ''),
            'mpesa_environment' => Setting::getValue('mpesa_environment', 'sandbox'),
            'swish_enabled' => Setting::getValue('swish_enabled', false),
            'swish_certificate' => Setting::getDecrypted('swish_certificate', ''),
            'swish_private_key' => Setting::getDecrypted('swish_private_key', ''),
            'swish_environment' => Setting::getValue('swish_environment', 'sandbox'),
            'cod_enabled' => Setting::getValue('cod_enabled', true),
            'cod_fee' => Setting::getValue('cod_fee', 0),
            'low_stock_threshold' => Setting::getValue('low_stock_threshold', 10),
            'sms_enabled' => Setting::getValue('sms_enabled', false),
            'twilio_account_sid' => Setting::getValue('twilio_account_sid', ''),
            'twilio_auth_token' => Setting::getDecrypted('twilio_auth_token', ''),
            'twilio_phone_number' => Setting::getValue('twilio_phone_number', ''),
            'fcm_enabled' => Setting::getValue('fcm_enabled', false),
            'fcm_server_key' => Setting::getDecrypted('fcm_server_key', ''),
            'mail_host' => Setting::getValue('mail_host', ''),
            'mail_port' => Setting::getValue('mail_port', 587),
            'mail_encryption' => Setting::getValue('mail_encryption', 'tls'),
            'mail_username' => Setting::getValue('mail_username', ''),
            'mail_password' => Setting::getDecrypted('mail_password', ''),
            'mail_from_address' => Setting::getValue('mail_from_address', ''),
            'mail_from_name' => Setting::getValue('mail_from_name', config('app.name')),
            'brand_color_from' => Setting::getValue('brand_color_from', '#ea580c'),
            'brand_color_to' => Setting::getValue('brand_color_to', '#dc2626'),
            // Mobile App Settings
            'mobile_android_version' => Setting::getValue('mobile_android_version', '1.0.0'),
            'mobile_android_build' => Setting::getValue('mobile_android_build', '1'),
            'mobile_android_url' => Setting::getValue('mobile_android_url', ''),
            'mobile_android_min_os' => Setting::getValue('mobile_android_min_os', '5.0'),
            'mobile_android_sha256' => Setting::getValue('mobile_android_sha256', ''),
            'mobile_ios_version' => Setting::getValue('mobile_ios_version', '1.0.0'),
            'mobile_ios_build' => Setting::getValue('mobile_ios_build', '1'),
            'mobile_ios_app_store_url' => Setting::getValue('mobile_ios_app_store_url', ''),
            'mobile_ios_min_os' => Setting::getValue('mobile_ios_min_os', '12.0'),
            'mobile_changelog_url' => Setting::getValue('mobile_changelog_url', ''),
            'mobile_install_instructions_url' => Setting::getValue('mobile_install_instructions_url', ''),
            'mobile_contact_support_url' => Setting::getValue('mobile_contact_support_url', ''),
        ];

        return $settings;
    }

    /**
     * Update the specified settings.
     */
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
            'mpesa_consumer_key' => 'nullable|string',
            'mpesa_consumer_secret' => 'nullable|string',
            'mpesa_shortcode' => 'nullable|string',
            'mpesa_passkey' => 'nullable|string',
            'mpesa_environment' => 'nullable|in:sandbox,production',
            'swish_enabled' => 'nullable|boolean',
            'swish_certificate' => 'nullable|string',
            'swish_private_key' => 'nullable|string',
            'swish_environment' => 'nullable|in:sandbox,production',
            'cod_enabled' => 'nullable|boolean',
            'cod_fee' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1|max:100',
            'sms_enabled' => 'nullable|boolean',
            'twilio_account_sid' => 'nullable|string',
            'twilio_auth_token' => 'nullable|string',
            'twilio_phone_number' => 'nullable|string',
            'fcm_enabled' => 'nullable|boolean',
            'fcm_server_key' => 'nullable|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
            'brand_color_from' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'brand_color_to' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            // Mobile App Settings
            'mobile_android_version' => 'nullable|string',
            'mobile_android_build' => 'nullable|string',
            'mobile_android_url' => 'nullable|url',
            'mobile_android_min_os' => 'nullable|string',
            'mobile_android_sha256' => 'nullable|string',
            'mobile_ios_version' => 'nullable|string',
            'mobile_ios_build' => 'nullable|string',
            'mobile_ios_app_store_url' => 'nullable|url',
            'mobile_ios_min_os' => 'nullable|string',
            'mobile_changelog_url' => 'nullable|url',
            'mobile_install_instructions_url' => 'nullable|url',
            'mobile_contact_support_url' => 'nullable|url',
        ]);

        // Track changes for audit
        $user = $request->user();
        $changes = [];

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $oldValue = Setting::getValue($key);

                // Determine if this key should be encrypted
                if (Setting::shouldEncrypt($key) && ! empty($value)) {
                    Setting::setSecure($key, $value);
                } else {
                    Setting::set($key, $value);
                }

                // Log the change
                if ($oldValue != $value) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $value,
                    ];

                    // Create audit log
                    ConfigurationAudit::log(
                        $key,
                        $oldValue,
                        $value,
                        ConfigurationAudit::ACTION_UPDATED,
                        $user?->id
                    );
                }
            }
        }

        // Clear configuration cache
        Cache::flush();

        // Log summary
        if (! empty($changes)) {
            Log::info('Settings updated', [
                'user_id' => $user?->id,
                'changes' => array_keys($changes),
            ]);
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    /**
     * Send a test email to verify SMTP configuration.
     */
    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        $testEmail = $validated['test_email'];

        // Check if SMTP is configured
        $host = Setting::getValue('mail_host');

        if (empty($host)) {
            return redirect()->back()->with('error', 'SMTP host is not configured. Please save your SMTP settings first.');
        }

        try {
            // Temporarily configure mail with database settings
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) Setting::getValue('mail_port', 587),
                'mail.mailers.smtp.encryption' => Setting::getValue('mail_encryption', 'tls'),
                'mail.mailers.smtp.username' => Setting::getValue('mail_username'),
                'mail.mailers.smtp.password' => Setting::getDecrypted('mail_password'),
                'mail.from.address' => Setting::getValue('mail_from_address', config('mail.from.address')),
                'mail.from.name' => Setting::getValue('mail_from_name', config('mail.from.name')),
                'mail.default' => 'smtp',
            ]);

            Mail::raw('This is a test email from your Laravel application. If you received this, your SMTP configuration is working correctly!', function ($message) use ($testEmail) {
                $message->to($testEmail)
                    ->subject('Test Email - SMTP Configuration');
            });

            // Log successful test
            ConfigurationAudit::log(
                'mail.test',
                null,
                $testEmail,
                ConfigurationAudit::ACTION_UPDATED,
                $request->user()?->id
            );

            return redirect()->back()->with('success', "Test email sent successfully to {$testEmail}");
        } catch (\Exception $e) {
            Log::error('Test email failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to send test email: '.$e->getMessage());
        }
    }

    /**
     * Export settings as JSON.
     */
    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        // Get all settings
        $settings = Setting::all()->keyBy('key')->toArray();

        // Mask sensitive values
        foreach ($settings as $key => $setting) {
            if (Setting::shouldEncrypt($key)) {
                $settings[$key]['value'] = '[ENCRYPTED]';
            }
        }

        // Log export
        ConfigurationAudit::log(
            'settings.export',
            null,
            json_encode(array_keys($settings)),
            ConfigurationAudit::ACTION_EXPORTED,
            $user?->id
        );

        return response()->streamDownload(function () use ($settings) {
            echo json_encode($settings, JSON_PRETTY_PRINT);
        }, 'settings-export-'.date('Y-m-d-H-i-s').'.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import settings from JSON.
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:json',
        ]);

        try {
            $file = $request->file('import_file');
            $content = file_get_contents($file->getRealPath());
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->with('error', 'Invalid JSON file format.');
            }

            $imported = 0;
            $errors = [];
            $user = $request->user();

            foreach ($settings as $key => $setting) {
                try {
                    $value = $setting['value'] ?? null;
                    $type = $setting['type'] ?? 'string';

                    if ($value === null || $value === '[ENCRYPTED]') {
                        continue;
                    }

                    if (Setting::shouldEncrypt($key)) {
                        Setting::setSecure($key, $value, $type);
                    } else {
                        Setting::set($key, $value, $type);
                    }

                    // Log each import
                    ConfigurationAudit::log(
                        $key,
                        null,
                        $value,
                        ConfigurationAudit::ACTION_IMPORTED,
                        $user?->id
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to import {$key}: ".$e->getMessage();
                }
            }

            // Clear cache
            Cache::flush();

            if (empty($errors)) {
                return redirect()->back()->with('success', "Successfully imported {$imported} settings.");
            } else {
                return redirect()->back()->with('warning', "Imported {$imported} settings with ".count($errors).' errors.');
            }
        } catch (\Exception $e) {
            Log::error('Settings import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    /**
     * Show audit logs.
     */
    public function audit(Request $request)
    {
        $query = ConfigurationAudit::with('user')->orderBy('created_at', 'desc');

        // Filter by key if provided
        if ($request->has('key') && $request->key) {
            $query->forKey($request->key);
        }

        // Filter by user if provided
        if ($request->has('user_id') && $request->user_id) {
            $query->byUser($request->user_id);
        }

        $audits = $query->paginate(50);

        return view('admin.settings.audit', compact('audits'));
    }

    /**
     * Clear configuration cache.
     */
    public function clearCache(Request $request)
    {
        try {
            Cache::flush();

            // Log cache clear
            ConfigurationAudit::log(
                'settings.cache',
                null,
                'cleared',
                ConfigurationAudit::ACTION_UPDATED,
                $request->user()?->id
            );

            return redirect()->back()->with('success', 'Configuration cache cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to clear cache: '.$e->getMessage());
        }
    }
}
