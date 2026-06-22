# Configuration Management System - Implementation Plan

## Executive Summary

Refactor all third-party service configurations from hardcoded values and `.env` file entries into a secure, database-driven admin backend interface. This system will allow administrators to configure and modify service credentials (payment gateways, email services, external APIs, storage providers) without exposing sensitive information in environment files.

---

## 1. Current Implementation Analysis

### Existing Setting Model (`app/Models/Setting.php`)
```php
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];
    
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;
        
        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'array' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }
    
    public static function set(string $key, $value, string $type = 'string'): void
    {
        // Type casting logic
    }
}
```

### Existing SettingController (`app/Http/Controllers/Admin/SettingController.php`)
- Index method loads predefined settings from database
- Update method validates and saves settings
- Test email functionality for SMTP verification

### Identified Gaps
1. No encryption for sensitive values (API keys, passwords stored in plain text)
2. No audit logging for configuration changes
3. No granular access control per setting
4. Hardcoded settings - new services require code changes
5. No rollback procedures
6. No environment fallback mechanism
7. Missing notification service replacement (Pusher/FCM)

---

## 2. Migration Strategy

### 2.1 Phased Rollout Approach

| Phase | Description | Duration |
|-------|-------------|----------|
| Phase 1 | Database Schema & Core Services | 2 days |
| Phase 2 | Encryption Implementation | 2 days |
| Phase 3 | Admin UI Enhancements | 2 days |
| Phase 4 | Notification System Migration | 2 days |
| Phase 5 | Testing & Rollback Procedures | 1 day |

### 2.2 Migration Steps

1. **Backup**: Export current settings to JSON backup file
2. **Schema**: Run new migrations for encrypted storage
3. **Seed**: Create default configuration categories
4. **Migrate**: Copy existing settings to new encrypted storage
5. **Verify**: Compare before/after values
6. **Deploy**: Enable database-driven config loading

### 2.3 Rollback Procedures
```php
// Emergency rollback command
php artisan config:rollback --to=env
```

---

## 3. Database Schema Modifications

### 3.1 Configuration Categories Table
```php
// database/migrations/xxxx_xx_xx_create_configuration_categories_table.php
Schema::create('configuration_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');           // e.g., "Payment Gateways"
    $table->string('slug')->unique(); // e.g., "payment-gateways"
    $table->text('description')->nullable();
    $table->string('icon')->nullable(); // for UI
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index('slug');
    $table->index('is_active');
});
```

### 3.2 Configuration Settings Table (Encrypted)
```php
// database/migrations/xxxx_xx_xx_create_configuration_settings_table.php
Schema::create('configuration_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('configuration_category_id')
          ->constrained('configuration_categories')
          ->onDelete('cascade');
    $table->string('key')->unique(); // e.g., "stripe_secret_key"
    $table->string('label');          // e.g., "Secret Key"
    $table->text('description')->nullable();
    $table->enum('type', ['text', 'textarea', 'number', 'boolean', 'select', 'json', 'file', 'encrypted']);
    $table->text('value')->nullable();
    $table->text('default_value')->nullable();
    $table->json('options')->nullable(); // for select type: ["sandbox", "production"]
    $table->json('validation_rules')->nullable(); // e.g., ["required", "min:8"]
    $table->boolean('is_encrypted')->default(false);
    $table->boolean('is_required')->default(false);
    $table->boolean('is_public')->default(false); // accessible without auth
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index('key');
    $table->index('configuration_category_id');
    $table->index(['is_active', 'is_encrypted']);
});
```

### 3.3 Configuration Audit Log Table
```php
// database/migrations/xxxx_xx_xx_create_configuration_audit_logs_table.php
Schema::create('configuration_audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
    $table->string('setting_key');
    $table->text('old_value')->nullable();
    $table->text('new_value')->nullable();
    $table->enum('action', ['created', 'updated', 'deleted', 'restored', 'exported', 'imported']);
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
    
    $table->index('setting_key');
    $table->index('user_id');
    $table->index('created_at');
});
```

---

## 4. Encryption Implementation

### 4.1 EncryptionService
```php
// app/Services/ConfigurationEncryptionService.php
namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ConfigurationEncryptionService
{
    private string $cipher = 'AES-256-CBC';
    
    /**
     * Encrypt sensitive configuration value
     */
    public function encrypt(string $value): string
    {
        try {
            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            Log::error('Encryption failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to encrypt configuration value');
        }
    }
    
    /**
     * Decrypt configuration value
     */
    public function decrypt(string $encryptedValue): string
    {
        try {
            return Crypt::decryptString($encryptedValue);
        } catch (\Exception $e) {
            Log::error('Decryption failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Failed to decrypt configuration value');
        }
    }
    
    /**
     * Determine if a value appears to be encrypted
     */
    public function isEncrypted(string $value): bool
    {
        if (empty($value)) return false;
        
        try {
            $decrypted = $this->decrypt($value);
            return $decrypted !== $value;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Rotate encryption key
     */
    public function rotateKey(string $oldKey, string $newKey): int
    {
        // Re-encrypt all values with new key
        $settings = ConfigurationSetting::where('is_encrypted', true)->get();
        $count = 0;
        
        foreach ($settings as $setting) {
            try {
                $decrypted = $this->decrypt($setting->value);
                $setting->value = $this->encrypt($decrypted);
                $setting->save();
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to re-encrypt setting: {$setting->key}");
            }
        }
        
        return $count;
    }
}
```

### 4.2 Cipher Selection
- **Recommended**: `AES-256-CBC` (Laravel default)
- **Alternative**: `AES-128-CBC` for performance (not recommended for high security)

---

## 5. Secure Password & Access Control

### 5.1 Role-Based Access Control
```php
// ConfigurationPermissions.php
namespace App\Enums;

enum ConfigurationPermission: string
{
    case VIEW = 'configuration.view';
    case PAYMENT_EDIT = 'configuration.payment.edit';
    case EMAIL_EDIT = 'configuration.email.edit';
    case STORAGE_EDIT = 'configuration.storage.edit';
    case NOTIFICATION_EDIT = 'configuration.notification.edit';
    case ANALYTICS_EDIT = 'configuration.analytics.edit';
    case AUDIT_VIEW = 'configuration.audit.view';
    case EXPORT = 'configuration.export';
    case IMPORT = 'configuration.import';
    
    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Configurations',
            self::PAYMENT_EDIT => 'Edit Payment Settings',
            self::EMAIL_EDIT => 'Edit Email Settings',
            self::STORAGE_EDIT => 'Edit Storage Settings',
            self::NOTIFICATION_EDIT => 'Edit Notification Settings',
            self::ANALYTICS_EDIT => 'Edit Analytics Settings',
            self::AUDIT_VIEW => 'View Audit Logs',
            self::EXPORT => 'Export Configuration',
            self::IMPORT => 'Import Configuration',
        };
    }
}
```

### 5.2 Middleware for Access Control
```php
// app/Http/Middleware/ConfigurationAccess.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationAccess
{
    public function handle(Request $request, string $permission): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->hasPermissionTo($permission)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return $next($request);
    }
}
```

---

## 6. Data Validation & Error Handling

### 6.1 Form Request Validation
```php
// app/Http/Requests/UpdateConfigurationRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configuration.view');
    }
    
    public function rules(): array
    {
        $rules = [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:configuration_settings,key',
            'settings.*.value' => 'sometimes|string',
        ];
        
        // Add dynamic validation based on setting type
        foreach ($this->input('settings', []) as $index => $setting) {
            $key = $setting['key'] ?? null;
            $validationRules = $this->getValidationRules($key);
            if ($validationRules) {
                $rules["settings.{$index}.value"] = $validationRules;
            }
        }
        
        return $rules;
    }
    
    private function getValidationRules(?string $key): ?string
    {
        if (!$key) return null;
        
        $setting = \App\Models\ConfigurationSetting::where('key', $key)->first();
        if (!$setting || !$setting->validation_rules) return null;
        
        return $setting->validation_rules;
    }
    
    public function messages(): array
    {
        return [
            'settings.*.key.exists' => 'The configuration key does not exist.',
        ];
    }
}
```

### 6.2 Exception Handling
```php
// app/Exceptions/ConfigurationExceptionHandler.php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConfigurationExceptionHandler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Configuration')) {
                Log::error('Configuration error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
        
        $this->renderable(function (ValidationException $e) {
            if ($e->getMessage() === 'validation.configuration') {
                return response()->json([
                    'error' => 'Validation failed',
                    'details' => $e->errors()
                ], 422);
            }
        });
    }
}
```

---

## 7. Backward Compatibility

### 7.1 Environment Variable Fallback
```php
// app/Services/ConfigurationService.php
namespace App\Services;

class ConfigurationService
{
    public static function get(string $key, $default = null)
    {
        // 1. First check database configuration
        $setting = ConfigurationSetting::where('key', $key)
            ->where('is_active', true)
            ->first();
        
        if ($setting) {
            $value = $setting->value;
            
            // Decrypt if encrypted
            if ($setting->is_encrypted && $value) {
                $value = app(ConfigurationEncryptionService::class)->decrypt($value);
            }
            
            return $value;
        }
        
        // 2. Fallback to .env file
        $envKey = strtoupper(str_replace('.', '_', $key));
        if (env($envKey) !== null) {
            return env($envKey);
        }
        
        // 3. Return default value
        return $default;
    }
    
    public static function has(string $key): bool
    {
        return ConfigurationSetting::where('key', $key)->exists()
            || env(strtoupper(str_replace('.', '_', $key))) !== null;
    }
}
```

### 7.2 Configuration Caching
```php
// Refresh config cache after database changes
public function set(string $key, $value): void
{
    // Save to database
    // ...
    
    // Clear config cache
    cache()->forget("config.{$key}");
    cache()->forget("config.all");
}
```

---

## 8. Admin Interface Enhancements

### 8.1 Configuration Categories UI

| Category | Settings |
|----------|----------|
| Payment Gateways | Stripe, Paystack, MOMO, Swish, COD |
| Email Services | SMTP, SendGrid, Mailgun |
| Storage | Local, AWS S3 |
| Notifications | In-App Push (Database-driven) |
| Analytics | Internal tracking only |

### 8.2 Setting Field Components
```blade
{{-- field-encrypted.blade.php --}}
<div class="form-group">
    <label>{{ $label }}</label>
    <div class="input-group">
        <input type="password" 
               name="{{ $name }}" 
               value="{{ $decryptedValue }}"
               class="form-control">
        <button type="button" class="btn btn-secondary toggle-visibility">
            <i class="fa fa-eye"></i>
        </button>
    </div>
    @if($description)
        <small class="form-text text-muted">{{ $description }}</small>
    @endif
</div>

{{-- field-file.blade.php --}}
<div class="form-group">
    <label>{{ $label }}</label>
    <input type="file" name="{{ $name }}" class="form-control">
    @if($currentValue)
        <small class="form-text">Current: {{ $currentValue }}</small>
    @endif
</div>
```

---

## 9. Notification System Migration

### 9.1 Remove Pusher/FCM Dependencies
All Pusher and FCM references are to be removed from:
- `config/services.php`
- `config/pusher.php`
- Notification service files

### 9.2 In-App Custom Notification System
```php
// app/Models/Notification.php
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'priority',
    ];
    
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
```

### 9.3 Notification Queue System
```php
// app/Jobs/SendInAppNotification.php
class SendInAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(): void
    {
        $notification = Notification::create([
            'user_id' => $this->userId,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'priority' => $this->priority ?? 'normal',
        ]);
        
        // Broadcast to user via WebSocket (optional, using Laravel Reverb)
        event(new NotificationCreated($notification));
    }
}
```

---

## 10. Migration Scripts

### 10.1 Export Current Settings
```php
// database/seeders/MigrateSettingsToConfigurationsSeeder.php
public function run(): void
{
    // Get all existing settings
    $settings = Setting::all();
    
    $categoryMap = [
        'stripe_' => 'payment-gateways',
        'paystack_' => 'payment-gateways',
        'momo_' => 'payment-gateways',
        'mail_' => 'email-services',
        'twilio_' => 'notifications',
        'fcm_' => 'notifications',
    ];
    
    foreach ($settings as $setting) {
        $categorySlug = 'general';
        foreach ($categoryMap as $prefix => $category) {
            if (str_starts_with($setting->key, $prefix)) {
                $categorySlug = $category;
                break;
            }
        }
        
        $category = ConfigurationCategory::where('slug', $categorySlug)->first();
        
        ConfigurationSetting::create([
            'configuration_category_id' => $category?->id,
            'key' => $setting->key,
            'label' => ucwords(str_replace(['_', '-'], ' ', $setting->key)),
            'type' => $this->mapType($setting->type),
            'value' => $this->shouldEncrypt($setting->key) 
                ? app(ConfigurationEncryptionService::class)->encrypt($setting->value)
                : $setting->value,
            'is_encrypted' => $this->shouldEncrypt($setting->key),
            'is_active' => true,
        ]);
    }
}
```

### 10.2 Data Integrity Verification
```php
public function verifyIntegrity(): array
{
    $results = [];
    $settings = Setting::all();
    
    foreach ($settings as $setting) {
        $newValue = ConfigurationService::get($setting->key);
        $match = $setting->value == $newValue;
        
        $results[] = [
            'key' => $setting->key,
            'original' => $setting->value,
            'migrated' => $newValue,
            'match' => $match,
        ];
    }
    
    return $results;
}
```

---

## 11. Security Considerations

### 11.1 Input Sanitization
- Strip HTML tags from all text inputs
- Validate file uploads for allowed types only
- Sanitize file paths to prevent directory traversal

### 11.2 Output Encoding
- Never output raw configuration values in views
- Use Laravel's `e()` helper for HTML encoding
- Mask sensitive values in logs

### 11.3 Audit Logging Requirements
- Log all configuration changes with:
  - Timestamp
  - User ID
  - IP Address
  - Old value (encrypted)
  - New value (encrypted)
  - Action type

### 11.4 Encryption at Rest
- All API keys, passwords, tokens stored encrypted
- Use AES-256-CBC cipher
- Key rotation supported

---

## 12. Implementation Timeline

| Day | Tasks |
|-----|-------|
| 1-2 | Database migrations, models, basic services |
| 3-4 | Encryption implementation, form requests |
| 5-6 | Admin UI, CRUD operations |
| 7-8 | Notification system (in-app push) |
| 9 | Testing, rollback procedures |
| 10 | Documentation, deployment |

---

## 13. Files to Create/Modify

### New Files
- `database/migrations/*_create_configuration_categories_table.php`
- `database/migrations/*_create_configuration_settings_table.php`
- `database/migrations/*_create_configuration_audit_logs_table.php`
- `app/Models/ConfigurationCategory.php`
- `app/Models/ConfigurationSetting.php`
- `app/Models/ConfigurationAudit.php`
- `app/Models/Notification.php`
- `app/Services/ConfigurationEncryptionService.php`
- `app/Services/ConfigurationService.php`
- `app/Http/Controllers/Admin/ConfigurationController.php`
- `app/Http/Requests/UpdateConfigurationRequest.php`
- `app/Http/Middleware/ConfigurationAccess.php`
- `app/Jobs/SendInAppNotification.php`
- `app/Events/NotificationCreated.php`
- `database/seeders/ConfigurationSettingsSeeder.php`
- `database/seeders/MigrateSettingsToConfigurationsSeeder.php`
- `resources/views/admin/configurations/*`

### Modified Files
- `app/Providers/AppServiceProvider.php`
- `app/Http/Kernel.php`
- `routes/admin.php`
- `config/services.php` (remove Pusher/FCM)
- `resources/views/layouts/admin.blade.php`

### Removed
- All Pusher configuration references
- All FCM configuration references
- All social login references (Google, Facebook, Apple)
