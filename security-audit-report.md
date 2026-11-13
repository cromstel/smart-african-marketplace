# Website Security Audit Report

**Target:** http://localhost:8000/ (Smart African Market)
**Date:** November 13, 2026
**Format:** Markdown

---

## Executive Summary

A security assessment of the Smart African Market web application identified **7 issues** across the following categories:

| Severity | Count |
|----------|-------|
| High | 2 |
| Medium | 3 |
| Low | 2 |

The application demonstrates strong security fundamentals (CSRF protection, parameterized queries, httpOnly cookies, input validation), but lacks several HTTP security headers and has overly permissive CORS configuration.

---

## Findings

### 1. Missing HTTP Security Headers (HIGH)

**Description:** The application does not send critical security headers that protect against common web attacks.

**Missing Headers:**
- `Content-Security-Policy` (CSP) — No protection against XSS/injection
- `X-Frame-Options` — No clickjacking protection
- `X-Content-Type-Options: nosniff` — No MIME-type sniffing protection
- `Strict-Transport-Security` (HSTS) — No HTTPS enforcement
- `Referrer-Policy` — No referrer leakage control
- `Permissions-Policy` — No feature policy restrictions

**Evidence:** Response headers from `GET /`:
```
Content-Type: text/html; charset=utf-8
X-Powered-By: PHP/8.5.10
```
No security headers present.

**Impact:** Attackers can exploit XSS, clickjacking, MIME confusion, and other client-side attacks without restriction.

**Remediation:**
Create a `SecurityHeaders` middleware and register it in the global middleware stack:

```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
            "style-src 'self' 'unsafe-inline'; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' data:; " .
            "connect-src 'self' ws: wss:; " .
            "frame-ancestors 'self';"
        );

        return $response;
    }
}
```

Register in `bootstrap/app.php`:
```php
$middleware->use([
    // ... existing middleware ...
    \App\Http\Middleware\SecurityHeaders::class,
]);
```

---

### 2. X-Powered-By Header Exposed (MEDIUM)

**Description:** The `X-Powered-By: PHP/8.5.10` header reveals the server technology and version, aiding attackers in targeting known vulnerabilities.

**Evidence:**
```
X-Powered-By: PHP/8.5.10
```

**Impact:** Information disclosure that can guide targeted attacks.

**Remediation:**
Add to `.htaccess` or `php.ini`:
```apache
# .htaccess
Header unset X-Powered-By
```

Or in `bootstrap/app.php` middleware:
```php
$response->headers->remove('X-Powered-By');
```

Also set in `php.ini`:
```ini
expose_php = Off
```

---

### 3. Overly Permissive CORS Configuration (MEDIUM)

**Description:** CORS is configured to allow all origins (`*`) on API paths, which could allow unauthorized cross-origin requests.

**Evidence:** `config/cors.php`:
```php
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

**Impact:** Malicious websites could make authenticated API requests on behalf of users (if credentials were enabled).

**Remediation:**
Restrict to specific origins in production:
```php
'allowed_origins' => [
    env('APP_URL'),
    'https://yourdomain.com',
],
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN'],
```

---

### 4. Login Route Not Rate-Limited (MEDIUM)

**Description:** The `POST /auth/login` route has `antispam` middleware but no `throttle` middleware, unlike other auth routes (register, admin login, password reset) which have `throttle:5,1` or `throttle:10,1`.

**Evidence:** `routes/auth.php`:
```php
Route::post('auth/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('antispam');  // Missing throttle!
```

**Impact:** Brute-force password attacks are possible without rate limiting.

**Remediation:**
```php
Route::post('auth/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['antispam', 'throttle:5,1']);
```

---

### 5. Debug Mode Enabled (MEDIUM)

**Description:** `APP_DEBUG=true` and `APP_ENV=local` are set in `.env`, which exposes detailed error messages, stack traces, and internal application details.

**Evidence:** `.env`:
```
APP_ENV=local
APP_DEBUG=true
```

**Impact:** Attackers can gain insight into application internals, database structure, and file paths through error pages.

**Remediation:**
For production, set:
```
APP_ENV=production
APP_DEBUG=false
```

The application already strips exception details from API responses (in `bootstrap/app.php`), but HTML error pages still expose details when `APP_DEBUG=true`.

---

### 6. No Security Headers Middleware (LOW)

**Description:** No dedicated middleware exists for setting security headers. The application relies on `.htaccess` and framework defaults, which don't include CSP, HSTS, or other modern security headers.

**Remediation:** Create and register the `SecurityHeaders` middleware (see Finding #1).

---

### 7. CORS `supports_credentials` Disabled (LOW)

**Description:** While `supports_credentials` is `false` (good), the combination of `allowed_origins: *` with potential future changes could introduce credential leakage. This is currently safe but should be monitored.

**Remediation:** Keep `supports_credentials` as `false` and restrict origins when enabling credentials.

---

## Positive Security Practices Found

| Practice | Status |
|----------|--------|
| CSRF protection (`VerifyCsrfToken`) | ✅ Enabled |
| httpOnly session cookies | ✅ Enabled |
| SameSite=Lax cookies | ✅ Enabled |
| Secure cookies (HTTPS) | ✅ Enabled |
| Parameterized SQL queries | ✅ Used throughout |
| API error responses strip exception details | ✅ Implemented |
| Rate limiting on most routes | ✅ Enabled |
| Antispam/honeypot on auth routes | ✅ Enabled |
| Input validation (Zod/schemas) | ✅ Used |
| CompressResponse middleware | ✅ Enabled |
| No `.env` files in public directory | ✅ Verified |
| No private keys/certificates tracked | ✅ Verified |
| No hardcoded secrets in codebase | ✅ Verified |

---

## Recommendations Summary

### Immediate (High Priority)
1. **Add security headers middleware** (CSP, X-Frame-Options, HSTS, X-Content-Type-Options)
2. **Remove `X-Powered-By` header** to prevent information disclosure

### Short-term (Medium Priority)
3. **Restrict CORS origins** to specific domains in production
4. **Add throttle middleware** to `POST /auth/login` route
5. **Set `APP_DEBUG=false`** and `APP_ENV=production` for production deployment

### Long-term (Low Priority)
6. **Monitor CORS configuration** when enabling credentials
7. **Implement Content Security Policy** with strict rules
8. **Add security headers to `.htaccess`** as defense-in-depth

---

## Remediation Code Snippets

### Security Headers Middleware
See Finding #1 for full implementation.

### Remove X-Powered-By
Add to `bootstrap/app.php` global middleware or `.htaccess`:
```apache
# .htaccess
Header unset X-Powered-By
```

### Fix Login Rate Limiting
In `routes/auth.php`:
```php
Route::post('auth/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['antispam', 'throttle:5,1']);
```

### Production .env Settings
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

---

*Report generated by Website Vulnerability Checker skill*
*All findings are based on passive assessment of the running application*
