# Authentication Form Layout

This project uses a storefront-auth overlay layout for customer and vendor authentication pages.

## Reference

- Visual reference: `docs/authentication-form/login-page-format.avif`
- Hero background image: `public/images/auth-bg.avif`
- Layout rule: single-section background (no split columns)
- Form placement: shifted `20vw` toward center from right edge on desktop, centered on smaller screens
- Height rule: auth shell uses `100vh` (`h-[100vh] min-h-[100vh]`)
- Form card surface: `bg-white` (solid white, no transparency)
- `/forgot-password`: the route now issues a `redirect()->route('login', ['forgot' => 1])`, so there is no standalone view and the reset form content lives in the login modal instead.

## Applied Pages (Customer/Vendor)

- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- the forgot-password screen — **no standalone view**; `auth/forgot-password`
  redirects to `login?forgot=1` and the modal partial supplies the form
- `resources/views/auth/reset-password.blade.php`
- `resources/views/auth/confirm-password.blade.php`
- `resources/views/auth/verify-email.blade.php`

These pages now render through `layouts.shop` so the global storefront header/footer remain visible, with the form panel rendered on top of the shared background image. `/forgot-password` now redirects to `/login?forgot=1` and the modal partial delivers the input UI.

## Excluded Page

- `resources/views/auth/admin-login.blade.php`

Admin login intentionally keeps its separate format and does not use the storefront auth wrapper.

## Manifest & Favicon

- `public/site.webmanifest` now lists `/favicon.svg`, `/favicon-16x16.png`, `/favicon-32x32.png`, `/favicon-192x192.png`, and `/favicon-512x512.png` so browsers can load every icon referenced by the Blade SEO partial.
- When browsers report `Manifest` icon failures (e.g., `login:1 Error while trying to use .../favicon.svg` or `GET /favicon-32x32.png 404` on `http://127.0.0.1:8000`), verify the files exist in `public/` and then clear the service worker/data cache from DevTools ⇒ Application ⇒ Clear storage (or run `caches.delete('african-market-shell-v2')` in console) so stale cached manifests are replaced.
- Repeat the clear-storage step whenever you bump `site.webmanifest` so Safari/Edge/PWA previews fetch the latest icon set from the updated manifest.
