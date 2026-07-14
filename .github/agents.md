# Smart African Marketplace - Agent/Copilot Context

This document provides context for AI agents and Copilot about the codebase architecture, coding standards, and preferred patterns.

## Project Overview

**Smart African Marketplace** is a Laravel-based multi-vendor e-commerce platform for African products and services. It uses:
- Laravel 12 (PHP 8.2+)
- Livewire 3 for reactive UI components
- Tailwind CSS 3 for styling
- Alpine.js for minimal JavaScript interactions

## Architecture

### Backend Structure
```
app/
├── Http/Controllers/   # Request handlers - MVC pattern
├── Models/             # Eloquent models with relationships
├── Services/           # Business logic layer
├── Livewire/           # Reactive components (preferred over controllers for UI)
└── Policies/           # Authorization policies (spatie/laravel-permission)
```

### Frontend Structure
```
resources/
├── views/              # Blade templates
│   ├── components/     # Reusable Blade components
│   ├── livewire/       # Livewire component views
│   └── layouts/        # App layouts
├── css/                # Tailwind CSS source
└── js/                 # Alpine.js components
```

## Coding Standards

### PHP/Laravel
- Follow PSR-12 coding standards
- Use Laravel Pint for formatting: `vendor/bin/pint`
- Models should use `$fillable` for mass assignment (not `$guarded`)
- Controllers should be thin - delegate to Services
- Livewire components should handle UI interactions, not business logic
- Use FormRequest classes for validation
- Use route model binding where possible

### Livewire Components
- Component class and view follow naming convention: `MyComponent.php` + `my-component.blade.php`
- Public properties for data binding, protected for internal logic
- Use lifecycle hooks (`mount`, `updated`, `hydrate`) appropriately
- Avoid heavy queries in render - use pagination/lazy loading

### Blade Templates
- Extend layouts using `@extends('layouts.app')`
- Use `@push` for scripts/styles in head/footer sections
- Prefer `@forelse` over `@if + @foreach` for lists
- Use components: `@include('components.alert')` or `<x-alert>`

### Database Migrations
- Use snake_case for column names
- Add indexes on foreign keys and searchable columns
- Use JSON columns for flexible attributes when appropriate
- Write reversible `down()` methods

## Preferred Patterns

### Models
```php
// Use $fillable for security
protected $fillable = ['name', 'email', 'status'];

// Always define casts
protected $casts = [
    'email_verified_at' => 'datetime',
    'metadata' => 'array',
];

// Define relationship methods clearly
public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}
```

### Livewire Components
```php
class ProductList extends Component
{
    public $search = '';
    public $category = null;
    public $perPage = 12;

    public function mount($category = null)
    {
        $this->category = $category;
    }

    public function updating($propertyName)
    {
        // Reset pagination when filters change
        if ($propertyName !== 'page') {
            $this->resetPage();
        }
    }

    public function render()
    {
        return view('livewire.product-list', [
            'products' => Product::where('active', true)
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->when($this->category, fn($q) => $q->where('category_id', $this->category))
                ->paginate($this->perPage),
        ]);
    }
}
```

### Form Requests
- Use FormRequest for complex validation
- Authorization in `authorize()` method
- Custom messages in `$message()` array

### Service Classes
- Single responsibility principle
- Place in `app/Services/`
- Inject dependencies via constructor
- Return structured data or throw exceptions

## Security Practices

- Never expose internal IDs directly - use UUIDs where appropriate
- Validate all user input (FormRequest or `$request->validate()`)
- Use `view()->exists()` for dynamic view names
- Escape output in Blade (automatic with `{{ }}`)
- Rate limit API endpoints
- Log security-relevant events

## Testing

- PHPUnit tests in `tests/Feature` and `tests/Unit`
- Livewire tests using `Livewire::test()`
- Use factories or seeders for test data
- Aim for meaningful coverage, not 100%

## Common Commands

```bash
# Run tests
php artisan test

# Format code
vendor/bin/pint

# Clear caches
php artisan optimize:clear

# Build assets
npm run build

# Run queues
php artisan queue:work
```

## Key Modules

- **Products**: Catalog with categories, variants, inventory
- **Orders**: Cart, checkout, status tracking
- **Vendors**: Multi-vendor marketplace with commissions
- **Salon**: Booking system with time slots
- **Delivery**: Zone-based delivery with driver management
- **Payments**: Stripe + Paystack integration
- **Admin**: Management dashboard with analytics

## Known Security Notes

### Completed Security Upgrades
- **Laravel 12.0**: Upgraded from Laravel 11 - all framework vulnerabilities resolved
- **Dompdf dev-master**: Upgraded to Laravel 12 compatible version
- **Removed unicodeveloper/laravel-paystack**: Package was unused (custom PaystackPaymentService already implemented)
- **SecureEmail rule** (`app/Rules/SecureEmail.php`): Custom email validation preventing CRLF injection

### Mitigations Applied
- **SecureEmail rule** (`app/Rules/SecureEmail.php`): Custom email validation preventing CRLF injection in user inputs
- **Automated scanning**: GitHub Actions weekly security audit workflow enabled
- **All npm vulnerabilities resolved**: Upgraded axios, vite, alpinejs, and all frontend dependencies

### Dependencies & Versions

- Laravel Framework: ^12.0
- Livewire: ^3.0|^4.0
- PHP: ^8.2|^8.3|^8.4
- MySQL: 8.0+

Check `composer.json` and `package.json` for full dependency list.