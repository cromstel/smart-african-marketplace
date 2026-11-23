# Module Backlog — Unimplemented Premium Modules

**Status:** Draft — generated from `config/modules.php` (`implemented => false`) + `premium-modules.md` + `subscription-modules.md`.
**Last updated:** 2026-11-22

> These 9 modules are flagged `implemented: false` in `config/modules.php` but are still listed in the tier entitlements (`tiers.professional` / `tiers.business`). Until they are implemented **or** removed from the tier lists + marketing site, customers may be promised features that don't exist.
>
> **Two blockers make this worse than it first appears.** They are documented in full in `module-issues.md` (Blocker A and Blocker B) and were confirmed by reading the current code:
>
> 1. **The `implemented` flag does not gate entitlements.** `SubscriptionPlanSeeder` merges `config('modules.catalog')` and `config('modules.tiers')` into each plan's `feature_keys` without ever checking `implemented`. Free Trial ($0) and Enterprise ($199/mo) are granted **all 9** unimplemented modules; Professional ($79/mo) is granted 5 and Business ($129/mo) is granted 4. `User::hasModuleAccess()` reads only `feature_keys`. Flipping the flag alone changes nothing — the seeder must filter first.
> 2. **CI executes zero tests.** `/tests/` is gitignored (commit `cec97464`), so no test file is tracked in `HEAD`. `ci.yml` guards both test steps on `hashFiles('tests/**/*.php') != ''`, which evaluates to `'' != ''` → `false`, so the test and coverage steps are skipped on both matrix legs and the pipeline reports green without running anything.
>
> The detailed, actionable versions of both — with an `ISSUE-00` for the entitlement guard — live in `module-issues.md`.

## Quick index

| # | Module | Tier | Effort | Status |
|---|--------|------|--------|--------|
| 1 | Vendor Support Module | Professional | M | Not started |
| 2 | Seller Badge | Professional | S | Not started |
| 3 | Request for Quotation | Business | L | Not started |
| 4 | Report Abuse | Professional | S | Not started |
| 5 | EU Compliance Fields | Professional | S | Not started |
| 6 | Store Support | Professional | S | Not started |
| 7 | Vendor Staff Manager | Business | L | Not started |
| 8 | Wholesale | Business | L | Not started |
| 9 | Product Q&A | Business | M | Not started |

Effort: S = 1–3 days, M = 1 week, L = 2+ weeks.

---

## 1. Vendor Support Module
- **Tier:** Professional, Business, Enterprise
- **Effort:** M
- **Description:** In-dashboard support channel between vendors and admins.
- **Acceptance criteria:**
  - [ ] Vendor can open a support ticket to admin from vendor dashboard
  - [ ] Admin can reply, change status, assign
  - [ ] Email notification on new reply
  - [ ] Reuse existing `SupportTicket`/`SupportMessage` models with a `vendor_id` scope
  - [ ] Vendor-isolation test (vendor A cannot see vendor B's tickets)
- **Files to touch:** `app/Http/Controllers/Vendor/SupportController.php`, `resources/views/vendor/support/*`, routes, `SupportTicket` (add vendor scope)
- **Depends on:** existing Support module

## 2. Seller Badge
- **Tier:** Professional, Business, Enterprise
- **Effort:** S
- **Description:** Award badges to vendors for marketplace accomplishments (top-seller, verified, fast-ship).
- **Acceptance criteria:**
  - [ ] `VendorBadge` model + migration (vendor_id, badge_key, awarded_at, revoked_at)
  - [ ] Badge criteria definitions (sales volume, rating, fulfillment speed)
  - [ ] Automated award job (scheduled)
  - [ ] Badge display on store header + product listings
  - [ ] Admin manual award/revoke
- **Files to touch:** new model/migration, `VendorBadgeService`, admin controller, Blade partials

## 3. Request for Quotation
- **Tier:** Business, Enterprise
- **Effort:** L
- **Description:** Allow customers to request price quotes from vendors; vendors respond and convert to orders.
- **Acceptance criteria:**
  - [ ] `QuoteRequest` + `QuoteResponse` models + migrations
  - [ ] Quote request form on product pages
  - [ ] Vendor workflow to respond with price/validity
  - [ ] Convert accepted quote to order
  - [ ] Email notifications both directions
  - [ ] Vendor-isolation test
- **Depends on:** Product, Order, Vendor

## 4. Report Abuse
- **Tier:** Professional, Business, Enterprise
- **Effort:** S
- **Description:** Customers can report abusive products/vendors; admin moderation queue.
- **Acceptance criteria:**
  - [ ] `AbuseReport` model + migration (reporter_id, target_type, target_id, reason, status)
  - [ ] Report form on product/store pages
  - [ ] Admin moderation queue with hide/suspend actions
  - [ ] Acknowledgement email to reporter
- **Depends on:** Product, Vendor, User

## 5. EU Compliance Fields
- **Tier:** Professional, Business, Enterprise (Starter too per subscription-modules.md)
- **Effort:** S
- **Description:** VAT/TAX ID and company fields for EU law compliance (customers + vendors).
- **Acceptance criteria:**
  - [ ] Add `tax_id`/`vat_number`/`company_name` to User and Vendor (migration)
  - [ ] Validation in registration/profile
  - [ ] Display on invoices and checkout
  - [ ] Admin review/approval
- **Depends on:** User, Vendor, Order, Payment

## 6. Store Support
- **Tier:** Professional, Business, Enterprise
- **Effort:** S
- **Description:** Frontend support widget on store pages so customers get help from vendors.
- **Acceptance criteria:**
  - [ ] Store-level support form (reuses SupportTicket with vendor scope)
  - [ ] Route to vendor staff
  - [ ] Optional integration with ticketing
- **Depends on:** Vendor Support Module (#1) — implement together

## 7. Vendor Staff Manager
- **Tier:** Business, Enterprise
- **Effort:** L
- **Description:** Vendors appoint staff accounts to manage their store with a permission matrix.
- **Acceptance criteria:**
  - [ ] `VendorStaff` model + migration (vendor_id, user_id, role, permissions json)
  - [ ] Invitation flow (email invite + accept)
  - [ ] Role/permission matrix (products, orders, bookings, etc.)
  - [ ] Audit log of staff actions
  - [ ] Vendor-isolation test
- **Depends on:** Vendor, User, spatie/laravel-permission

## 8. Wholesale
- **Tier:** Business, Enterprise
- **Effort:** L
- **Description:** Vendors offer wholesale pricing (tiered/quantity breaks) and wholesale-only checkout.
- **Acceptance criteria:**
  - [ ] Wholesale price tiers on Product/ProductVariant (min qty, price)
  - [ ] Wholesale customer flag / group
  - [ ] Wholesale-only checkout restrictions
  - [ ] Cart total uses wholesale price when applicable
- **Depends on:** Product, Cart, Order, User

## 9. Product Q&A
- **Tier:** Business, Enterprise
- **Effort:** M
- **Description:** Customers/visitors ask product questions; vendors answer publicly on the product page.
- **Acceptance criteria:**
  - [ ] `ProductQuestion` + `ProductAnswer` models + migration
  - [ ] Question form on product pages (guest + auth)
  - [ ] Vendor inbox for pending questions
  - [ ] Public Q&A display with moderation
  - [ ] Email notification to vendor on new question, to customer on answer
  - [ ] Vendor-isolation test
- **Depends on:** Product, Vendor, User, Notification
- **Reference:** See the implementation plan (generated alongside this backlog) for full detail.

---

## Cross-cutting gaps (not in the 9 flagged modules)

| Feature | Source | Status | Notes |
|---------|--------|--------|-------|
| Installer System (CMS-style web wizard) | PRD §3 | Not implemented (documented) | README explicitly states not implemented; deploy via runbook |
| Virtual Try-On (AR) | PRD §2.1.2 (Phase 2) | Deferred | Phase 2 scope |
| Multi-language (Swahili, Portuguese, Arabic) | PRD §2.4.2 | Partial (only en/es/fr) | Add `sw`, `pt`, `ar` to `lang/` |
| Multi-currency switcher | PRD §2.4.2 | Partial (per-transaction currency only) | Add currency config + switcher + rates |
| PDF Invoice generation | subscription-modules.md | Not implemented | Add invoice template + PDF lib + email attachment |
| Printful Integration | subscription-modules.md | Not implemented | Optional fulfillment adapter |
| ShipStation Integration | subscription-modules.md | Not implemented | Optional shipping adapter |
| Wirecard | subscription-modules.md | Not implemented | Deprecated (Wirecard insolvent) — recommend removing from catalog |
| Smart Migrator Tool | subscription-modules.md | Not implemented | Optional migration tooling |
| Product Duplicator | subscription-modules.md | Not implemented | Add duplicate action |
| Single Product Multivendor | subscription-modules.md | Not implemented | Shared listings + vendor comparison |
| Vendor Vacation | subscription-modules.md | Not implemented | Vacation flag + auto-hide |
| Product Addon / upsells | subscription-modules.md | Not implemented | Addon templates + pricing |
| Table Rate Shipping | subscription-modules.md | Partial (basic shipping exists) | Complex rate tables per vendor |
| Minimum/Maximum Order | subscription-modules.md | Not implemented | Quantity/value limits |
| Product Subscription (customer subscribes to a product) | subscription-modules.md | Not implemented | Recurring product subscriptions |
| Delivery Time (date/time slots) | subscription-modules.md | Not implemented | Slot selection in checkout |
| Stripe Connect (vendor payouts) | subscription-modules.md | Partial (StripePaymentService exists; Connect onboarding not) | Platform onboarding + automated payouts |
| Duplicate auth middleware (AdminMiddleware vs EnsureUserIsAdmin) | overview.md debt #2 | Tech debt | Consolidate |
| env() outside config (2 sites) | overview.md debt #3 | Tech debt | ConfigurationService, VerifyReadinessCommand |
| Test coverage (6 files vs 146 controllers) | overview.md debt #5 | Tech debt | Expand per roadmap Phase 2 |
| Entire test suite untracked by git | `cec97464` + `.gitignore:176` | **Blocker** | `/tests/` is ignored, so 0 test files are tracked in `HEAD`; CI skips both test steps via `hashFiles('tests/**/*.php') != ''` and reports green without running anything. Re-track the suite or make CI honest — see `module-issues.md` Blocker A |
| Application-enforced tenancy (not DB-level) | overview.md | Architectural risk | Add DB-level scoping or strengthen tests |

## Recommended sequencing (per roadmap guiding principle)

> The platform is **feature-rich and under-verified**. Prefer making the existing surface trustworthy before adding modules.

1. **First:** Land the two blockers in `module-issues.md` — gate plan entitlements on the `implemented` flag (`ISSUE-00`), and decide whether to re-track the test suite or make CI honest about running zero tests. Until the entitlement guard lands, remove the 9 unimplemented modules from `tiers.professional` / `tiers.business` and from the enterprise / free-trial key sets — flipping the flag alone is a no-op.
2. **Then:** Phase 2 test foundation (vendor isolation + money paths) per `roadmap.md` — noting that this work is currently unversioned, so it only becomes a gate once Blocker A is resolved.
3. **Then:** Implement highest-value missing modules (Product Q&A, RFQ, Vendor Staff Manager, Wholesale).
4. **Optional/deferred:** Printful, ShipStation, PDF Invoice, Smart Migrator, Virtual Try-On (Phase 2), Installer wizard, multi-language expansion (sw/pt/ar), multi-currency, middleware consolidation.

## Notes
- `premium-modules.md` and `subscription-modules.md` are aspirational catalogs (sourced from a WordPress-style marketplace plugin ecosystem). Not all items are appropriate for this Laravel platform — e.g., Wirecard (insolvent), Elementor/WPML (WordPress-only), Printful/ShipStation (third-party SaaS). Treat as a menu, not a mandate.
- The `config/modules.php` `implemented` flags are the source of truth for what the codebase claims to ship. Keep them in sync with reality.