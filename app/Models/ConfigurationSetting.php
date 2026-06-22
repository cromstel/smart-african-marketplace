<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ConfigurationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_category_id',
        'key',
        'label',
        'description',
        'type',
        'value',
        'default_value',
        'options',
        'validation_rules',
        'is_encrypted',
        'is_required',
        'is_public',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_encrypted' => 'boolean',
        'is_required' => 'boolean',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Sensitive key patterns that should be encrypted.
     */
    protected static array $sensitivePatterns = [
        '*_secret*',
        '*_password*',
        '*_key',
        '*_token*',
        '*_api_key*',
        '*_private*',
    ];

    /**
     * Get the category for this setting.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ConfigurationCategory::class, 'configuration_category_id');
    }

    /**
     * Scope to get only active settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Scope to get public settings (accessible without auth).
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true)->where('is_active', true);
    }

    /**
     * Get the decrypted value for display.
     */
    public function getDecryptedValue(): ?string
    {
        if (! $this->value) {
            return null;
        }

        if ($this->is_encrypted) {
            try {
                return Crypt::decryptString($this->value);
            } catch (\Exception $e) {
                // If decryption fails, return the raw value
                return $this->value;
            }
        }

        return $this->value;
    }

    /**
     * Get the decrypted default value.
     */
    public function getDecryptedDefaultValue(): ?string
    {
        if (! $this->default_value) {
            return null;
        }

        if ($this->is_encrypted) {
            try {
                return Crypt::decryptString($this->default_value);
            } catch (\Exception $e) {
                return $this->default_value;
            }
        }

        return $this->default_value;
    }

    /**
     * Set the value (handles encryption automatically).
     */
    public function setValueAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['value'] = null;

            return;
        }

        // Check if this setting should be encrypted
        if ($this->shouldEncrypt()) {
            try {
                $this->attributes['value'] = Crypt::encryptString($value);
            } catch (\Exception $e) {
                // If encryption fails, store as plain text with a warning
                $this->attributes['value'] = $value;
            }
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Determine if this setting should be encrypted.
     */
    public function shouldEncrypt(): bool
    {
        if ($this->is_encrypted) {
            return true;
        }

        // Check if key matches sensitive patterns
        foreach (self::$sensitivePatterns as $pattern) {
            $regex = str_replace('*', '.*', $pattern);
            if (preg_match("/^{$regex}$/i", $this->key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the value with proper type casting.
     */
    public function getCastValue()
    {
        $value = $this->getDecryptedValue() ?? $this->getDecryptedDefaultValue();

        if ($value === null) {
            return null;
        }

        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get validation rules as a string.
     */
    public function getValidationRulesString(): ?string
    {
        if (! $this->validation_rules || ! is_array($this->validation_rules)) {
            return null;
        }

        return implode('|', $this->validation_rules);
    }

    /**
     * Determine if value has changed and needs to be encrypted.
     */
    public function isDirtyAndShouldEncrypt(): bool
    {
        return $this->isDirty('value') && $this->shouldEncrypt();
    }

    /**
     * Check if this is a sensitive setting (for UI indication).
     */
    public function isSensitive(): bool
    {
        return $this->is_encrypted || $this->shouldEncrypt();
    }
}
