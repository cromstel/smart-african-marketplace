# Local Performance + Accessibility Automation (March 3, 2026)

Automated checks were executed locally with Playwright against `http://localhost:8000`.

## Checked paths
- `/`
- `/shop`
- `/login`
- `/admin/dashboard`

## Admin dashboard behavior verification
- Desktop Marketplace submenu toggle: pass (`Marketplace Manager` submenu opens)
- Mobile sidebar behavior: pass
  - Sidebar hidden by default
  - Sidebar opens with `#sidebar-toggle`
  - Marketplace submenu opens on mobile

## Performance snapshots (navigation timing)
- `/admin/dashboard` desktop: `totalMs=1650`, `ttfbMs=457`
- `/admin/dashboard` mobile: `totalMs=663`, `ttfbMs=271`
- `/shop` desktop: `totalMs=4903`, `ttfbMs=2503`
- `/shop` mobile: `totalMs=10071`, `ttfbMs=3768`

## Accessibility signal snapshots
- `/admin/dashboard`: `imgNoAlt=0`, `emptyButtons=1`, `unlabeledInputs=1`
- `/shop`: `imgNoAlt=0`, `emptyButtons=7`, `unlabeledInputs=7`
- `/`: `imgNoAlt=0`, `emptyButtons=2`, `unlabeledInputs=4`
- `/login`: `imgNoAlt=0`, `emptyButtons=0`, `unlabeledInputs=0`

## Current conclusion
- Dashboard behavior and basic performance are acceptable for local smoke.
- `/shop` remains a performance hotspot on local environment and should be optimized before final production sign-off.
- Accessibility labeling gaps remain on `/shop`, `/`, and `/admin/dashboard`; these should be resolved before final accessibility closeout.

## Hardening rerun (same day)
After the hardening patch set, automated recheck results:

- `/` => `totalMs=4347`, `ttfbMs=3073`, `unlabeled=0`, `emptyButtons=0`, `h1Count=1`
- `/shop` => `totalMs=881`, `ttfbMs=197`, `unlabeled=0`, `emptyButtons=0`, `h1Count=1`
- `/admin/dashboard` => `totalMs=4904`, `ttfbMs=3631`, `unlabeled=0`, `emptyButtons=0`, `h1Count=1`

Validation status:
- Accessibility signal issues identified in the initial pass are resolved on audited pages.
- `/shop` baseline is materially improved in the same local environment.

## Final closure rerun (March 3, 2026)

Additional Playwright smoke pass executed against `http://localhost:8000` with admin authentication:

- `/admin/dashboard` desktop: `totalMs=973`, `ttfbMs=231`, Marketplace Manager submenu hover interaction `pass`
- `/admin/dashboard` mobile: `totalMs=871`, `ttfbMs=212`, sidebar toggle + submenu expand interaction `pass`
- `/shop`: `totalMs=1626`, `ttfbMs=428`

Accessibility closeout rerun (visible form controls only):

- `/admin/dashboard`: `totalControls=1`, `unlabeled=0`
- `/shop`: `totalControls=5`, `unlabeled=0`
- `/`: `totalControls=2`, `unlabeled=0`

Conclusion:
- Manual smoke behavior for admin desktop/mobile navigation is validated.
- Performance is acceptable for current local regression baseline.
- Accessibility form-control labeling checks are passing on audited pages.
