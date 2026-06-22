<?php

namespace App\Http\Requests;

use App\Models\ConfigurationSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin middleware already handles authorization
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'settings' => 'sometimes|required|array',
            'settings.*.key' => 'sometimes|required|string',
            'settings.*.value' => 'sometimes',
        ];

        // Add dynamic validation based on setting type
        foreach ($this->input('settings', []) as $index => $setting) {
            $key = $setting['key'] ?? null;

            if ($key) {
                $validationRules = $this->getValidationRulesForKey($key);
                if ($validationRules) {
                    $rules["settings.{$index}.value"] = $validationRules;
                }
            }
        }

        return $rules;
    }

    /**
     * Get validation rules for a specific configuration key.
     */
    private function getValidationRulesForKey(string $key): ?string
    {
        // First check new ConfigurationSetting table
        $setting = ConfigurationSetting::where('key', $key)->first();

        if ($setting && $setting->validation_rules) {
            $rulesArray = $setting->validation_rules;

            if (is_array($rulesArray)) {
                return implode('|', $rulesArray);
            }

            return $rulesArray;
        }

        // Fallback to legacy settings validation
        return match ($key) {
            'stripe_public_key' => 'nullable|string',
            'stripe_secret_key' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
            'paystack_public_key' => 'nullable|string',
            'paystack_secret_key' => 'nullable|string',
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
            'swish_merchant_id' => 'nullable|string',
            'swish_merchant_token' => 'nullable|string',
            'swish_environment' => 'nullable|in:sandbox,production',
            'swish_webhook_secret' => 'nullable|string',
            'cod_fee' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1|max:100',
            'twilio_account_sid' => 'nullable|string',
            'twilio_auth_token' => 'nullable|string',
            'twilio_phone_number' => 'nullable|string',
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
            default => null,
        };
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'settings.*.key.required' => 'Configuration key is required.',
            'settings.*.value.required' => 'Configuration value is required.',
            'settings.array' => 'Settings must be provided as an array.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation can be added here
        });
    }
}
