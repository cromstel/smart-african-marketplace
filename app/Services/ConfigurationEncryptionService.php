<?php

namespace App\Services;

use App\Models\ConfigurationSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ConfigurationEncryptionService
{
    private string $cipher = 'AES-256-CBC';

    /**
     * Encrypt sensitive configuration value.
     */
    public function encrypt(string $value): string
    {
        try {
            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            Log::error('Configuration encryption failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to encrypt configuration value');
        }
    }

    /**
     * Decrypt configuration value.
     */
    public function decrypt(string $encryptedValue): string
    {
        try {
            return Crypt::decryptString($encryptedValue);
        } catch (\Exception $e) {
            Log::error('Configuration decryption failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to decrypt configuration value');
        }
    }

    /**
     * Determine if a value appears to be encrypted.
     */
    public function isEncrypted(string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        // Laravel encrypted strings start with "eyJ" (base64 encoded JSON)
        if (! str_starts_with($value, 'eyJ')) {
            return false;
        }

        try {
            $decrypted = $this->decrypt($value);

            // If decryption succeeds and produces a valid result, it's encrypted
            return ! empty($decrypted);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Rotate encryption key by re-encrypting all values.
     */
    public function rotateKey(): int
    {
        $settings = ConfigurationSetting::where('is_encrypted', true)->get();
        $count = 0;

        foreach ($settings as $setting) {
            try {
                $decrypted = $this->decrypt($setting->value);
                $setting->value = $decrypted; // Will re-encrypt via accessor
                $setting->save();
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to re-encrypt setting: {$setting->key}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Encrypt a value if it matches sensitive patterns.
     */
    public function encryptIfSensitive(string $key, string $value): string
    {
        $sensitivePatterns = [
            '*_secret*',
            '*_password*',
            '*_key',
            '*_token*',
            '*_api_key*',
            '*_private*',
        ];

        foreach ($sensitivePatterns as $pattern) {
            $regex = str_replace('*', '.*', $pattern);
            if (preg_match("/^{$regex}$/i", $key)) {
                return $this->encrypt($value);
            }
        }

        return $value;
    }

    /**
     * Check if a key should be encrypted based on patterns.
     */
    public function shouldEncrypt(string $key): bool
    {
        $sensitivePatterns = [
            '*_secret*',
            '*_password*',
            '*_key',
            '*_token*',
            '*_api_key*',
            '*_private*',
        ];

        foreach ($sensitivePatterns as $pattern) {
            $regex = str_replace('*', '.*', $pattern);
            if (preg_match("/^{$regex}$/i", $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the cipher being used.
     */
    public function getCipher(): string
    {
        return $this->cipher;
    }
}
