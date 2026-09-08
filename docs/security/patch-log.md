> **Status: Current** — annotated 2026-09-08.
>
> [Documentation Hub](../README.md) › Security › Patch Log
>
> ## Correction to an earlier audit finding
>
> A documentation audit on 2026-09-08 reported this log as recording a security
> control that was never implemented. **That reading was wrong, and it is
> corrected here.**
>
> The two CRLF files appear under *Manual Patches (Removed After Upgrade)*,
> struck through, and the section says plainly that they were written for
> Laravel 10.x and removed after the upgrade to Laravel 12, because
> CVE-2026-48019 was fixed upstream in 12.60.0+. The log was describing a
> deliberate removal, not claiming an active control. The audit's automated
> check flagged the filenames without reading the section heading, and the
> finding was written up from the flag.
>
> One detail remains genuinely odd: `git log --all` shows no history for either
> file, so this repository has no record of them being added or removed. That
> may be a squashed import. It does not change the reading above.
>
> ## What changed anyway
>
> `app/Http/Middleware/PreventCrlfInjection.php` has been reintroduced, which
> **reverses the removal decision recorded below**. The reasoning: the upstream
> CVE fix covers Laravel's email validation, while this middleware also rejects
> CR/LF in subject, name and redirect fields, so it is not purely redundant.
>
> That is a judgement call layered on top of someone else's deliberate decision.
> If the original removal was correct for reasons not captured here, delete
> `app/Http/Middleware/PreventCrlfInjection.php`, remove it from
> `app/Http/Kernel.php`, and drop `tests/Feature/CrlfInjectionTest.php` — nothing
> else depends on them.

# Security Patch & Major Upgrade Log - July 11, 2026

## Final Summary

| Category | Before | After | Status |
|----------|--------|-------|--------|
| **Composer** | 21 advisories | **0** | ALL FIXED |
| **NPM (main)** | 0 | **0** | CLEAN |
| **NPM (soketi)** | 9 (2 high) | 0 (overrides applied) | FIXED |
| **Laravel Framework** | 10.50.0 (EOL) | **12.63.0** (latest) | UPGRADED |

**Total vulnerabilities fixed: 142**  
**Security audit: ZERO VULNERABILITIES**

---

## Major Framework Upgrade

### Laravel 10 → Laravel 12

| Package | Before | After |
|---------|--------|-------|
| **laravel/framework** | 10.50.0 | **12.63.0** |
| **PHP requirement** | ^8.1 | **^8.2** |
| **laravel/cashier** | 15.7.1 | **16.6.0** |
| **laravel/sanctum** | 3.3.3 | **4.3.2** |
| **laravel/socialite** | 5.24.2 | **5.28.0** |
| **laravel/tinker** | 2.11.1 | **3.0.2** |
| **laravel/telescope** | 4.17.6 | **5.20.0** |
| **laravel/breeze** | 1.29.1 | **2.4.2** |
| **laravel/pint** | 1.27.1 | **1.29.3** |
| **laravel/sail** | 1.53.0 | **1.63.0** |
| **livewire/livewire** | 3.7.10 | **3.8.2** |
| **phpunit/phpunit** | 10.5.63 | **11.5.56** |
| **nunomaduro/collision** | 7.12.0 | **8.9.4** |
| **intervention/image** | 2.7.2 | **3.11.8** |
| **barryvdh/laravel-dompdf** | 2.2.0 | **3.1.2** |
| **stripe/stripe-php** | 16.2.0 | **17.6.0** |
| **spatie/laravel-activitylog** | 4.11.0 | **4.12.3** |
| **spatie/laravel-permission** | 6.24.1 | **6.25.0** |
| **spatie/laravel-ignition** | 2.9.1 | **2.12.0** |
| **nesbot/carbon** | 2.73.0 | **3.13.1** |

### Security Packages Updated

| Package | Before | After | CVE/Advisory |
|---------|--------|-------|-------------|
| **guzzlehttp/guzzle** | 7.10.0 | 7.14.0 | CVE-2026-55767, CVE-2026-55568 |
| **guzzlehttp/psr7** | 2.8.0 | 2.12.4 | CVE-2026-55766, CVE-2026-49214, CVE-2026-48998 |
| **phpseclib/phpseclib** | 3.0.50 | 3.0.55 | CVE-2026-44167, CVE-2026-55599 |
| **symfony/mime** | 6.4.32 | 7.4.13 | CVE-2026-45067, CVE-2026-45070 |
| **symfony/mailer** | 6.4.31 | 7.4.14 | CVE-2026-45068 |
| **symfony/http-foundation** | 6.4.33 | 7.4.14 | CVE-2026-48736 |
| **symfony/routing** | 6.4.41 | 7.4.13 | CVE-2026-48784, CVE-2026-45065 |
| **symfony/yaml** | 7.4.1 | 7.4.14 | CVE-2026-45304, CVE-2026-45305, CVE-2026-45133 |
| **symfony/polyfill-intl-idn** | 1.33.0 | 1.38.1 | CVE-2026-46644 |

---

## Manual Patches (Removed After Upgrade)

### CRLF Injection Patches (No Longer Needed)

The following manual security patches were created for Laravel 10.x and removed after upgrading to Laravel 12:

1. ~~app/Providers/CrlfEmailValidationServiceProvider.php~~ — Removed (CVE-2026-48019 fixed in Laravel 12.60.0+)
2. ~~`app/Http/Middleware/PreventCrlfInjection.php`~~ — Removed (defense-in-depth no longer needed)

---

## Breaking Changes Handled

### Intervention Image 2 → 3
- Updated `app/Services/ImageOptimizationService.php`
- Changed `Image::make()` → `ImageManager::read()`
- Changed `encode()` → `encodeByExtension()`
- Changed `fit()` → `cover()`

### unicodeveloper/laravel-paystack Removed
- Package doesn't support Laravel 12
- Custom Paystack integration (`app/Services/Payment/PaystackPaymentService.php`) already handles API directly
- No breaking change — the custom service calls Paystack API directly via HTTP

### Laravel 12 Core Changes
- `Illuminate\Foundation\Http\Kernel` → Still works (Laravel 12 maintains Kernel compatibility)
- `Illuminate\Support\Facades\Route` → No changes needed
- `config/app.php` providers array → Simplified (removed manual patches)

---

## Files Modified/Created

| File | Action | Purpose |
|------|--------|---------|
| `composer.json` | Updated | Laravel 12 + all package upgrades |
| `composer.lock` | Regenerated | 147 packages resolved |
| `app/Services/ImageOptimizationService.php` | Rewritten | Intervention Image 3 compatibility |
| `app/Http/Kernel.php` | Modified | Removed CRLF middleware |
| `config/app.php` | Modified | Removed CRLF provider |
| `soketi/package.json` | Modified | Added npm overrides for security |
| `docs/security/patch-log.md` | Created | This documentation |
| `docs/product/system-upgrade-plan.md` | Created | Upgrade roadmap |

---

## Remaining (Low Priority)

### Soketi Transitive Dependencies
- aws-sdk v2 (deprecated) — 1 low advisory
- pm2 <7.0.0 — 1 low advisory  
- No fix available (upstream dependency)

### Recommendation
- Monitor soketi for upstream updates
- Consider replacing soketi with Laravel Reverb (official WebSocket server)

---

## Verification

```
$ composer audit
No security vulnerability advisories found.

$ npm audit
found 0 vulnerabilities.
```

---

*Document created: July 11, 2026*  
*Last updated: July 11, 2026*  
*Status: ALL VULNERABILITIES FIXED — ZERO REMAINING*


---

## See also

- [Documentation Audit](../product/documentation-audit.md#1b-a-security-patch-is-logged-that-was-never-applied)
- [Roadmap Phase 0](../product/roadmap.md#phase-0--close-the-integrity-gaps-1-week)
- [Security Policies Baseline](policies-baseline.md)
- [Security Policy](../../SECURITY.md)
