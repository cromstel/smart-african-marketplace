# Module Issues — Unimplemented Premium Modules

**Status:** Backlog — derived from `config/modules.php`, `user-guide/modules/module-backlog.md`, `user-guide/modules/premium-modules.md`, and `user-guide/modules/subscription-modules.md`.
**Last updated:** 2026-11-22

> These issues cover the nine catalog modules marked `implemented: false` in `config/modules.php`. They remain present in paid-tier entitlements, so the implementation, entitlement lists, and marketing catalog must be reconciled before the modules can be considered delivered.

## Summary table

| Issue ID | Module | Tier | Effort | Dependency | Blocks revenue? |
|---|---|---|---|---|---|
| ISSUE-00 | Entitlement guard (`implemented` flag enforcement) | All plans | S | — | Yes — root cause of every other row in this table |
| ISSUE-01 | Product Q&A | Business, Enterprise | M | Product, Vendor, User, Notification | Yes — paid-tier entitlement is unimplemented |
| ISSUE-02 | Request for Quotation | Business, Enterprise | L | Product, Order, Vendor | Yes — paid-tier entitlement is unimplemented |
| ISSUE-03 | Vendor Staff Manager | Business, Enterprise | L | Vendor, User, `spatie/laravel-permission` | Yes — paid-tier entitlement is unimplemented |
| ISSUE-04 | Wholesale | Business, Enterprise | L | Product, Cart, Order, User | Yes — paid-tier entitlement is unimplemented |
| ISSUE-05 | EU Compliance Fields | Professional, Business, Enterprise (Starter also listed in the subscription catalog) | S | User, Vendor, Order, Payment | Yes — paid-tier entitlement is unimplemented |
| ISSUE-06 | Report Abuse | Professional, Business, Enterprise | S | Product, Vendor, User | Yes — paid-tier entitlement is unimplemented |
| ISSUE-07 | Seller Badge | Professional, Business, Enterprise | S | Vendor and marketplace performance data | Yes — paid-tier entitlement is unimplemented |
| ISSUE-08 | Vendor Support | Professional, Business, Enterprise | M | Existing Support module (`SupportTicket`, `SupportMessage`) | Yes — paid-tier entitlement is unimplemented |
| ISSUE-09 | Store Support | Professional, Business, Enterprise | S | ISSUE-08 — Vendor Support | Yes — paid-tier entitlement is unimplemented |

Effort: S = 1–3 days, M = 1 week, L = 2+ weeks.

## Blockers — read before the issues

Two findings must be resolved before any issue in this document can be considered delivered. Both were confirmed by reading the current code, not inferred.

### Blocker A — CI executes zero tests

`.gitignore` line 176 ignores `/tests/` (added deliberately in commit `cec97464 chore(repo): untrack tests folder from remote (keep locally)`, 2026-11-13). That commit removed every tracked test file. `git ls-tree -r HEAD --name-only` now returns **0 files** under `tests/`; 101 test files exist on disk but are untracked and unversioned.

`.github/workflows/ci.yml` gates both test steps on the presence of test files:

```yaml
- name: Run tests
  if: matrix.php-version != '8.3' && hashFiles('tests/**/*.php') != ''
```

`hashFiles('tests/**/*.php')` resolves against the checked-out workspace, which contains no `tests/` directory. The expression evaluates to `'' != ''` → **false**, so the test step is skipped on the 8.2 leg and the coverage step is skipped on the 8.3 leg. The workflow reports **green while running no tests at all**.

Consequences:

- No regression gate exists on `main` or on pull requests, despite the workflow presenting itself as a test gate.
- Any test added from here on — including the Product Q&A tests — is invisible to CI and to every other clone of the repository.
- The "Roadmap 2.4: make coverage visible, not a gate" intent recorded in `ci.yml` is unreachable: there is no coverage to upload.

This is an owner decision to confirm, not a defect to silently revert. The options are:

1. **Re-track the suite** — remove `/tests/` from `.gitignore` and `git add -f tests/` — restoring the CI gate. Recommended if the tests are not sensitive.
2. **Keep tests local and make CI honest** — remove the `hashFiles(...) != ''` guard so the test step always runs and fails loudly when no tests are found, or add an explicit job that fails with a clear message stating the suite is intentionally untracked.

Either way, record the decision in the repository documentation so the next contributor does not read a green pipeline as green tests.

### Blocker B — the `implemented` flag does not gate entitlements

`implemented: false` is **informational only**. It does not remove a module from any plan's entitlement set, so flipping it to `true` is a no-op on its own.

Entitlement path:

1. `User::hasModuleAccess()` (`app/Models/User.php:205`) delegates to `SubscriptionPlan::hasFeatureKey()` (`app/Models/SubscriptionPlan.php:108`), which checks membership in the plan's stored `feature_keys` JSON. It never reads the `implemented` flag.
2. `SubscriptionPlanSeeder` (`database/seeders/SubscriptionPlanSeeder.php:12-37`) builds those `feature_keys` by merging `config('modules.tiers')` and `config('modules.catalog')` wholesale. It never filters on `implemented`.

Consequences in the current configuration:

| Plan | Price | Unimplemented modules granted |
|---|---|---|
| Free Trial | $0.00 | all 9 |
| Starter | $29.00 | none |
| Professional | $79.00 | 5 — `vendor_support_module`, `seller_badge`, `report_abuse`, `eu_compliance_fields`, `store_support` |
| Business | $129.00 | 4 — `request_for_quotation`, `vendor_staff_manager`, `wholesale`, `product_qa` |
| Enterprise | $199.00 | all 9 |

`enterprise` is granted every catalog key (`$enterpriseKeys = $premiumModuleKeys`, seeder line 25) and `free-trial` is likewise granted every catalog key (line 49), so both include all nine unimplemented modules.

`ModuleAccessController` (`app/Http/Controllers/Api/Vendor/ModuleAccessController.php:23-33`) reports `implemented` and `granted` as separate fields but computes `granted` from `hasModuleAccess()`, so the API returns `implemented: false, granted: true` for the same key. `AdminSubscriptionPlanController` (`app/Http/Controllers/Admin/SubscriptionPlanController.php:25-27, 63-65`) exposes the full catalog to the plan editor with no `implemented` filter, so an admin can also grant an unimplemented module by hand.

**Fix:** ISSUE-00 below. Until it lands, the correct action for each module issue is to remove the module from its tier lists and from the enterprise/free-trial key sets — not merely to flip a flag.

### ISSUE-00 — Gate plan entitlements on the `implemented` flag

**Labels:** `bug`, `entitlements`, `billing`, `blocks-revenue`
**Prerequisite for:** ISSUE-01 … ISSUE-09

**Problem**
`implemented` is reported to the client but never enforced when building a plan's `feature_keys`. Every unimplemented module is granted to at least one paying tier, and to Free Trial and Enterprise in full. Customers are charged for features that do not exist, and `EnsureModuleAccess` grants access to a module that has no routes.

**Scope**
- [ ] Filter `config('modules.catalog')` to entries with `implemented === true` before building `$premiumModuleKeys` / `$enterpriseKeys` in `SubscriptionPlanSeeder`.
- [ ] Filter each `config('modules.tiers')` list against the implemented catalog so unimplemented keys are dropped from Professional and Business.
- [ ] Keep `implemented_route_modules` merged in as today — those entries are explicitly implemented and carry their own `min_tier`.
- [ ] Guard the admin plan editor: exclude unimplemented catalog entries from `SubscriptionPlanController::create()` / `edit()` module lists, or render them as disabled with an explicit "not implemented" marker.
- [ ] Make `ModuleAccessController` consistent so `granted: true` is never returned for an unimplemented module.
- [ ] Re-seed the plans (`php artisan db:seed --class=SubscriptionPlanSeeder`) and update existing plan rows whose `feature_keys` still contain unimplemented keys.
- [ ] Decide the commercial position for the already-granted modules: remove them from paid tiers, or ship them under ISSUE-01 … ISSUE-09.

**Acceptance criteria**
- [ ] No plan's `feature_keys` contains a module whose `implemented` flag is `false`.
- [ ] `hasModuleAccess('product_qa')` returns `false` for a Business subscriber while `product_qa` is unimplemented.
- [ ] `ModuleAccessController` never reports `implemented: false` together with `granted: true`.
- [ ] The admin plan editor cannot grant an unimplemented module.
- [ ] Free Trial no longer grants unimplemented modules.
- [ ] Automated tests cover seeder filtering, `hasModuleAccess()`, and the API contract.

**Dependencies**
`config/modules.php`, `SubscriptionPlanSeeder`, `SubscriptionPlan`, `User::hasModuleAccess()`, `ModuleAccessController`, `AdminSubscriptionPlanController`.

**Files to touch**
- `database/seeders/SubscriptionPlanSeeder.php` — filter catalog and tier lists by `implemented`.
- `app/Http/Controllers/Admin/SubscriptionPlanController.php` — exclude or disable unimplemented entries in the plan editor.
- `app/Http/Controllers/Api/Vendor/ModuleAccessController.php` — keep `implemented` and `granted` consistent.
- `config/modules.php` — no flag changes required; the flags become authoritative once the seeder honours them.
- `tests/Feature/SubscriptionPlanSeederTest.php` — assert no plan grants an unimplemented module.

**Risk notes**
- Existing subscriber plans keep their stored `feature_keys` until re-seeded; the remediation must update live rows, not only seed-time data.
- Removing a granted module can look like a downgrade to existing customers and needs a commercial decision plus customer communication.
- `free-trial` currently grants every catalog key, so filtering it changes trial behaviour and any trial copy advertising "full premium module access".

**Test plan**
- `tests/Feature/SubscriptionPlanSeederTest.php` — no plan grants an unimplemented module; implemented ones still resolve.
- `tests/Unit/Models/SubscriptionPlanTest.php` — `hasFeatureKey()` behaviour.
- `tests/Feature/Api/ModuleAccessControllerTest.php` — `implemented` and `granted` are consistent.

### ISSUE-01 — Product Q&A

**Labels:** `feature`, `module`, `premium`, `tier:business`, `effort:M`
**Module key:** `product_qa`
**Tier:** Business, Enterprise
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The paid catalog promises customers and visitors a direct way to ask product-specific questions and receive public answers from the vendor. No Product Q&A models, product-page form, vendor inbox, moderation flow, or notification path currently implement that promise.

**Scope**
- [ ] Add a `ProductQuestion` model with a migration for product, question, answer, author, status, moderation, and timestamp relationships. Use a **single table** with the answer and moderation fields carried on the question, matching the existing `ProductEnquiry` pattern.
- [ ] Add a validated question form to the product page for both guest and authenticated users.
- [ ] Add a vendor inbox listing pending questions for products owned by the current vendor.
- [ ] Add a vendor response flow that publishes an answer against the correct question.
- [ ] Add public question-and-answer display and a moderation path for hiding or rejecting content.
- [ ] Send the vendor an email notification when a question is received and the customer an email notification when an answer is published.
- [ ] Enforce product ownership and vendor isolation on every vendor-side query and action.
- [ ] Set `product_qa` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A guest or authenticated customer can submit a validated question for a specific product.
- [ ] The owning vendor can list and answer pending questions for that product; another vendor cannot access them.
- [ ] An answered, published question with its answer appears on the public product page, while pending or moderated content does not.
- [ ] A moderation action can reject or hide a question or answer from the public view.
- [ ] The vendor receives a new-question email and the question author receives an answer email.
- [ ] Automated tests prove product ownership, vendor isolation, validation, moderation, and notification behavior.

**Dependencies**
`Product`, `Vendor`, `User`, Laravel notifications, and the existing product-page and vendor-dashboard surfaces.

**Files to touch**
- `app/Models/ProductQuestion.php` — new question model and relationships, including the answer and moderation fields.
- `database/migrations/*_create_product_questions_table.php` — persist questions, answers, and moderation state in one table.
- `app/Http/Controllers/Shop/ProductQuestionController.php` — guest and authenticated question submission.
- `app/Http/Controllers/Vendor/ProductQuestionController.php` — vendor inbox and response workflow.
- `app/Http/Controllers/Admin/ProductQuestionController.php` — moderation actions.
- `app/Notifications/ProductQuestionNotification.php` — new-question and answer notifications.
- `resources/views/shop/show.blade.php` and `resources/views/shop/partials/product-qa.blade.php` — public form, answers, and moderation state.
- `resources/views/vendor/product-questions/*` — vendor inbox and response views.
- `routes/web.php` — public and vendor Q&A routes.
- `config/modules.php` — set the module implementation flag after delivery.

**Risk notes**
- Vendor isolation is mandatory: a vendor must never be able to query or answer another vendor's product questions.
- Public guest input requires validation, rate limiting, and moderation controls to limit spam or abusive content.
- Product and vendor lists can trigger N+1 queries if answers, authors, and product ownership are not eager-loaded.

**Test plan**
- `tests/Feature/ProductQuestionTest.php` — guest and authenticated submission, validation, and notification behavior.
- `tests/Feature/Vendor/ProductQuestionIsolationTest.php` — vendor inbox, response, and cross-vendor denial coverage.
- `tests/Feature/Admin/ProductQuestionModerationTest.php` — moderation visibility and authorization.
- `tests/Unit/Models/ProductQuestionTest.php` — relationships, casts, and status behavior.
- `tests/e2e/product-qa.spec.ts` — product-page question and public-answer flow.

### ISSUE-02 — Request for Quotation

**Labels:** `feature`, `module`, `premium`, `tier:business`, `effort:L`
**Module key:** `request_for_quotation`
**Tier:** Business, Enterprise
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The Business-tier catalog promises customers the ability to request a price quote and vendors the ability to respond and convert an accepted quote into an order. The current product flow has no quote request, vendor quotation response, validity handling, or order-conversion path.

**Scope**
- [ ] Add `QuoteRequest` and `QuoteResponse` models with migrations for product, requester, vendor, price, validity, and status data.
- [ ] Add a validated quote-request form to product pages.
- [ ] Add a vendor workflow to review requests and respond with a quoted price and validity period.
- [ ] Add customer visibility for vendor responses and the ability to accept a quote.
- [ ] Add an idempotent conversion path from an accepted quote to the existing order workflow.
- [ ] Send email notifications for new requests and new vendor responses.
- [ ] Enforce that a vendor can only respond to requests for products that vendor owns.
- [ ] Set `request_for_quotation` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A customer can submit a quote request tied to a product and receives a uniquely identifiable request.
- [ ] The owning vendor can respond with a quoted price and validity period; other vendors cannot view or respond to it.
- [ ] The customer can see the response and accept an eligible quote.
- [ ] Accepting a quote creates an order through the existing order workflow with the quoted commercial values preserved.
- [ ] Repeating an acceptance request does not create duplicate orders.
- [ ] Automated tests prove validation, vendor isolation, notifications, and quote-to-order conversion.

**Dependencies**
`Product`, `Order`, `Vendor`, the existing cart/order workflow, and email notifications.

**Files to touch**
- `app/Models/QuoteRequest.php` — new quote request model and relationships.
- `app/Models/QuoteResponse.php` — new quote response model and relationships.
- `database/migrations/*_create_quote_requests_table.php` — request, product, vendor, customer, and status fields.
- `database/migrations/*_create_quote_responses_table.php` — response, price, validity, and status fields.
- `app/Services/Quote/QuoteRequestService.php` — request and response workflow.
- `app/Services/Quote/QuoteConversionService.php` — idempotent quote-to-order conversion.
- `app/Http/Controllers/Shop/RequestForQuotationController.php` — request and acceptance actions.
- `app/Http/Controllers/Vendor/RequestForQuotationController.php` — vendor response workflow.
- `resources/views/shop/show.blade.php` and `resources/views/shop/partials/quotation.blade.php` — customer request and response UI.
- `resources/views/vendor/quotations/*` — vendor response views.
- `routes/web.php` — customer and vendor quote routes.
- `config/modules.php` — set the module implementation flag after delivery.

**Risk notes**
- Quote-to-order conversion touches money-path data and must be atomic and idempotent so retries cannot duplicate orders.
- A vendor must not see or respond to quotations for products owned by another vendor.
- Price and validity validation must prevent malformed or expired responses from entering order creation.

**Test plan**
- `tests/Feature/RequestForQuotationTest.php` — customer request, vendor response, acceptance, and notifications.
- `tests/Feature/Vendor/RequestForQuotationIsolationTest.php` — cross-vendor access and response denial.
- `tests/Unit/Services/QuoteConversionServiceTest.php` — conversion values, atomicity, and duplicate protection.
- `tests/Feature/QuoteOrderConversionTest.php` — integration with the existing order workflow.
- `tests/e2e/request-for-quotation.spec.ts` — customer request through vendor response and acceptance.

### ISSUE-03 — Vendor Staff Manager

**Labels:** `feature`, `module`, `premium`, `tier:business`, `effort:L`
**Module key:** `vendor_staff_manager`
**Tier:** Business, Enterprise
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The Business-tier catalog promises vendors the ability to appoint staff accounts to manage their store. No vendor-staff membership, invitation flow, permission matrix, or staff-action audit trail currently provides that delegated store management.

**Scope**
- [ ] Add a `VendorStaff` model and migration with vendor, user, role, permissions, invitation, and status data.
- [ ] Add vendor invitation creation, delivery, acceptance, and revocation flows.
- [ ] Define the role and permission matrix for store areas such as products, orders, and bookings.
- [ ] Apply the matrix to every protected vendor-dashboard action touched by staff access.
- [ ] Record auditable staff actions with the actor, vendor, and affected resource.
- [ ] Ensure a staff account can operate only within the vendor that appointed it.
- [ ] Set `vendor_staff_manager` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A vendor owner can invite a user and assign the intended store permissions before the invitation is accepted.
- [ ] An invited user can accept once and is then associated with the correct vendor.
- [ ] Each protected vendor action permits or denies access according to the assigned role and permission matrix.
- [ ] Staff from vendor A cannot access, change, or audit records belonging to vendor B.
- [ ] Staff actions are recorded in the audit log with the staff actor and vendor context.
- [ ] Automated tests cover invitation lifecycle, authorization, permissions, audit entries, and vendor isolation.

**Dependencies**
`Vendor`, `User`, `spatie/laravel-permission`, the vendor-dashboard authorization layer, and the existing audit-log mechanism.

**Files to touch**
- `app/Models/VendorStaff.php` — new vendor-staff membership model.
- `database/migrations/*_create_vendor_staff_table.php` — membership, role, permission, invitation, and status fields.
- `app/Http/Controllers/Vendor/VendorStaffController.php` — staff listing, invitation, and revocation actions.
- `app/Http/Controllers/Auth/VendorStaffInvitationController.php` — invitation acceptance.
- `app/Policies/VendorStaffPolicy.php` — vendor-scoped authorization rules.
- `app/Mail/VendorStaffInvitation.php` — invitation email.
- `app/Models/ActivityLog.php` — staff-action audit context.
- `resources/views/vendor/staff/*` — invitation and staff-management UI.
- `routes/web.php` and `routes/auth.php` — invitation and management routes.
- `config/modules.php` — set the module implementation flag after delivery.

**Risk notes**
- Permission escalation is the primary risk: staff assignments and vendor ownership must be checked server-side on every action.
- Invitation links and acceptance must be scoped, expiring or revocable, and bound to the intended vendor and user.
- Broad permission queries can create N+1 lookups or expose another vendor's records when filters are omitted.

**Test plan**
- `tests/Feature/VendorStaffInvitationTest.php` — invitation, acceptance, revocation, and duplicate-use behavior.
- `tests/Feature/Vendor/VendorStaffPermissionTest.php` — role and permission matrix enforcement.
- `tests/Feature/Vendor/VendorStaffIsolationTest.php` — cross-vendor access and action denial.
- `tests/Feature/Vendor/VendorStaffAuditLogTest.php` — audit entries for staff actions.
- `tests/e2e/vendor-staff-management.spec.ts` — invite, accept, and manage a staff account.

### ISSUE-04 — Wholesale

**Labels:** `feature`, `module`, `premium`, `tier:business`, `effort:L`
**Module key:** `wholesale`
**Tier:** Business, Enterprise
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The Business-tier catalog promises vendors wholesale pricing with quantity breaks and wholesale-only checkout. The current product, cart, and order pricing paths are retail-only and do not represent an authorized wholesale customer, tier-specific price, minimum quantity, or wholesale checkout restriction.

**Scope**
- [ ] Add wholesale price-tier persistence for products and variants, including minimum quantity and price.
- [ ] Add the vendor UI for defining and editing wholesale tiers.
- [ ] Add a wholesale-customer flag or group mechanism to `User` and a vendor workflow to manage eligibility.
- [ ] Add pricing logic that selects the applicable wholesale tier for an eligible customer and quantity.
- [ ] Apply wholesale pricing to cart lines and totals before checkout.
- [ ] Enforce wholesale-only and minimum-quantity restrictions in checkout and order creation.
- [ ] Persist the applied wholesale price and quantity-break context on order items.
- [ ] Set `wholesale` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A vendor can create a wholesale price tier with a minimum quantity and price for a product or variant.
- [ ] An eligible wholesale customer receives the correct tier price for the requested quantity.
- [ ] A non-wholesale customer cannot purchase a wholesale-only product.
- [ ] Cart and checkout totals use the same wholesale price that is persisted on the resulting order item.
- [ ] Quantities below the applicable minimum are rejected with a clear validation message.
- [ ] Automated tests prove price-tier selection, eligibility, restrictions, total consistency, and vendor isolation.

**Dependencies**
`Product`, `ProductVariation`, `Cart`, `Order`, `User`, and the existing checkout and order-item pricing paths.

**Files to touch**
- `app/Models/Product.php` — wholesale product-tier relationships and accessors.
- `app/Models/ProductVariation.php` — wholesale variant-tier relationships and accessors.
- `app/Models/User.php` — wholesale eligibility flag or group relationship.
- `app/Models/Cart.php` and `app/Models/CartItem.php` — wholesale-aware totals.
- `app/Models/Order.php` and `app/Models/OrderItem.php` — persisted applied wholesale pricing context.
- `app/Services/Wholesale/WholesalePricingService.php` — tier selection and price calculation.
- `app/Http/Controllers/Vendor/ProductController.php` — vendor wholesale-tier management.
- `app/Http/Controllers/CartController.php` and `app/Http/Controllers/CheckoutController.php` — cart and checkout enforcement.
- `database/migrations/*_add_wholesale_pricing_to_products_table.php` — product and variant tier persistence.
- `resources/views/vendor/products/*` and `resources/views/cart/*` — tier management and wholesale price display.
- `config/modules.php` — set the module implementation flag after delivery.

**Risk notes**
- Wholesale pricing is a money path: cart, checkout, and order persistence must use one deterministic pricing source.
- Vendor eligibility and pricing rules must be tenant-scoped so one vendor cannot alter another vendor's wholesale terms.
- Quantity and product-type validation must prevent bypass of minimums, retail-price substitution, or invalid variant combinations.

**Test plan**
- `tests/Feature/WholesalePricingTest.php` — tier selection, minimum quantity, and eligible-customer pricing.
- `tests/Feature/WholesaleCheckoutTest.php` — cart totals, checkout restrictions, and persisted order-item prices.
- `tests/Feature/Vendor/WholesaleIsolationTest.php` — vendor-owned pricing and customer eligibility boundaries.
- `tests/Unit/Services/WholesalePricingServiceTest.php` — quantity-break and price-boundary cases.
- `tests/e2e/wholesale.spec.ts` — vendor tier setup through wholesale cart and checkout.

### ISSUE-05 — EU Compliance Fields

**Labels:** `feature`, `module`, `premium`, `tier:professional`, `effort:S`
**Module key:** `eu_compliance_fields`
**Tier:** Professional, Business, Enterprise (Starter also listed in `subscription-modules.md`)
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The catalog promises the VAT/TAX ID and company fields required for EU-law compliance for both customers and vendors. The platform has a vendor `tax_id` field, but it does not provide the complete customer/vendor compliance field set, checkout and order display, or an admin review and approval flow.

**Scope**
- [ ] Add the missing `tax_id`, `vat_number`, and `company_name` fields to the applicable `User` and `Vendor` records, reusing the existing vendor `tax_id` column rather than duplicating it.
- [ ] Add validation to registration, customer profile, vendor application, and vendor profile update paths.
- [ ] Display the relevant customer and vendor compliance fields during checkout and order confirmation.
- [ ] Add an admin review and approval workflow for submitted compliance details.
- [ ] Record the review state and reviewer context without exposing sensitive fields to unauthorized users.
- [ ] Align the module's catalog tier with the entitlement source of truth; `subscription-modules.md` additionally lists Starter.
- [ ] Set `eu_compliance_fields` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A customer can save valid company and TAX/VAT details and sees clear validation errors for invalid input.
- [ ] A vendor can submit the required company and TAX/VAT details during application or profile management.
- [ ] The applicable compliance values appear at checkout and on the resulting order confirmation.
- [ ] An admin can review and approve submitted compliance details, and the decision is persisted.
- [ ] Unauthorized users cannot view or change another party's compliance fields.
- [ ] Automated tests prove validation, review transitions, display, and authorization.

**Dependencies**
`User`, `Vendor`, `Order`, `Payment`, checkout, order confirmation, and admin user/vendor management.

**Files to touch**
- `database/migrations/*_add_eu_compliance_fields_to_users_and_vendors_table.php` — missing compliance fields and review state.
- `app/Models/User.php` — customer compliance fields, casts, and review state.
- `app/Models/Vendor.php` — vendor compliance fields and review state, preserving existing `tax_id`.
- `app/Http/Controllers/Auth/RegisteredUserController.php` — registration validation and persistence.
- `app/Http/Controllers/Customer/ProfileController.php` — customer profile compliance fields.
- `app/Http/Controllers/VendorApplicationController.php` and `app/Http/Controllers/VendorProfileController.php` — vendor compliance fields.
- `app/Http/Controllers/Admin/UserManagementController.php` and `app/Http/Controllers/Admin/VendorController.php` — review and approval actions.
- `resources/views/profile/edit.blade.php` and `resources/views/vendor/apply.blade.php` — customer and vendor field entry.
- `resources/views/checkout/index.blade.php` and `resources/views/emails/order-placed.blade.php` — compliance display.
- `config/modules.php` — set the module implementation flag after delivery and reconcile the Starter catalog discrepancy.

**Risk notes**
- TAX/VAT identifiers are sensitive business data and must be visible only to authorized parties.
- Admin approval and profile updates need audit context so a compliance decision cannot be silently changed.
- Validation and display must stay consistent with order and payment records to avoid incorrect tax or invoice data.

**Test plan**
- `tests/Feature/EuComplianceFieldsTest.php` — customer and vendor field persistence and validation.
- `tests/Feature/Admin/EuComplianceReviewTest.php` — admin review and approval transitions.
- `tests/Feature/CheckoutComplianceDisplayTest.php` — checkout and order-confirmation display.
- `tests/Feature/EuComplianceAuthorizationTest.php` — cross-account and unauthorized access denial.
- `tests/e2e/eu-compliance-fields.spec.ts` — profile entry through checkout display.

### ISSUE-06 — Report Abuse

**Labels:** `feature`, `module`, `premium`, `tier:professional`, `effort:S`
**Module key:** `report_abuse`
**Tier:** Professional, Business, Enterprise
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The catalog promises customers a way to report abusive products or vendors and promises admins a moderation queue with response actions. No abuse-report record, report form, moderation queue, hide or suspend action, or reporter acknowledgement currently implements that promise.

**Scope**
- [ ] Add an `AbuseReport` model and migration for reporter, target type, target ID, reason, status, and moderation metadata.
- [ ] Add validated report forms for product and vendor/store pages.
- [ ] Add an admin moderation queue with status filtering and target details.
- [ ] Add admin actions to hide a product or suspend a vendor according to the moderation decision.
- [ ] Send an acknowledgement email to the reporter when the report is received.
- [ ] Ensure reports and moderation actions are authorized and target records are resolved safely.
- [ ] Set `report_abuse` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A customer can report a product or a vendor with a validated reason, and the report is recorded against the correct target.
- [ ] An admin can list reports by status and inspect the reporter, reason, and target details.
- [ ] An authorized admin can hide a reported product or suspend a reported vendor, and the action is persisted.
- [ ] The reporter receives an acknowledgement email after submission.
- [ ] A non-admin cannot access the moderation queue or invoke moderation actions.
- [ ] Automated tests prove report validation, target handling, moderation actions, acknowledgement, and authorization.

**Dependencies**
`Product`, `Vendor`, `User`, the existing admin moderation surfaces, and email notifications.

**Files to touch**
- `app/Models/AbuseReport.php` — new abuse-report model and target relationships.
- `database/migrations/*_create_abuse_reports_table.php` — report, target, reason, status, and moderation fields.
- `app/Http/Controllers/Shop/ReportAbuseController.php` — customer report submission.
- `app/Http/Controllers/Admin/AbuseReportController.php` — moderation queue and actions.
- `app/Policies/AbuseReportPolicy.php` — admin and reporter authorization.
- `app/Mail/AbuseReportAcknowledged.php` — reporter acknowledgement email.
- `resources/views/shop/show.blade.php` and `resources/views/vendor-store/show.blade.php` — product and vendor report forms.
- `resources/views/admin/abuse-reports/*` — moderation queue and detail views.
- `routes/web.php` — public report and admin moderation routes.
- `config/modules.php` — set the module implementation flag after delivery.

**Risk notes**
- Moderation actions must be admin-only and target-aware so one report cannot affect an unrelated product or vendor.
- Public reporting can be abused or used for retaliation; validation, rate limiting, and audit context are required.
- Polymorphic target resolution and status filters need indexes to avoid slow queues or N+1 target lookups.

**Test plan**
- `tests/Feature/ReportAbuseTest.php` — product and vendor submission, validation, and acknowledgement.
- `tests/Feature/Admin/AbuseReportModerationTest.php` — queue filtering, hide, suspend, and authorization.
- `tests/Unit/Models/AbuseReportTest.php` — target relationships and status transitions.
- `tests/e2e/report-abuse.spec.ts` — customer report through admin moderation.

### ISSUE-07 — Seller Badge

**Labels:** `feature`, `module`, `premium`, `tier:professional`, `effort:S`
**Module key:** `seller_badge`
**Tier:** Professional, Business, Enterprise
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The catalog promises vendor badges for marketplace accomplishments such as top-seller, verified, and fast-ship status. No badge record, criterion definition, scheduled award process, manual admin action, or storefront and listing display currently provides those recognitions.

**Scope**
- [ ] Add a `VendorBadge` model and migration with vendor, badge key, awarded timestamp, and revoked timestamp.
- [ ] Define badge criteria for sales volume, rating, and fulfillment speed using marketplace performance data.
- [ ] Add a scheduled award job that evaluates eligible vendors and records awarded badges.
- [ ] Add admin controls to award and revoke a badge manually.
- [ ] Display active badges in the vendor store header and on product listings.
- [ ] Exclude revoked badges from all public badge displays.
- [ ] Set `seller_badge` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A badge award records the vendor, badge key, and award timestamp, and a revoked badge records its revocation timestamp.
- [ ] The scheduled award process applies the defined criteria and is repeatable without creating duplicate active awards.
- [ ] An admin can manually award and revoke a badge for a vendor.
- [ ] Active badges appear in the vendor store header and on that vendor's product listings.
- [ ] Revoked badges disappear from all public display locations.
- [ ] Automated tests prove criterion evaluation, idempotent awards, admin actions, display filtering, and vendor isolation.

**Dependencies**
`Vendor`, sales-volume data, rating data, fulfillment-speed data, and the scheduled-job infrastructure.

**Files to touch**
- `app/Models/VendorBadge.php` — new badge model and relationships.
- `database/migrations/*_create_vendor_badges_table.php` — badge, vendor, award, and revocation fields.
- `app/Models/Vendor.php` — active badge relationship.
- `app/Services/VendorBadgeService.php` — criterion evaluation, award, and revoke behavior.
- `app/Console/Commands/AwardVendorBadges.php` — scheduled award command.
- `app/Http/Controllers/Admin/VendorBadgeController.php` — manual award and revoke actions.
- `config/vendor-badges.php` — badge criteria and badge-key definitions.
- `resources/views/vendor-store/show.blade.php` and `resources/views/shop/index.blade.php` — public badge display.
- `routes/console.php` — scheduled badge-award registration.
- `config/modules.php` — set the module implementation flag after delivery.

**Risk notes**
- Badge criteria must use authoritative vendor-scoped data so one vendor's performance cannot award or revoke another vendor's badge.
- Manual awards and revocations need an auditable reason and actor context.
- Listing views can create N+1 queries unless active badges are eager-loaded or cached consistently with revocation.

**Test plan**
- `tests/Feature/SellerBadgeDisplayTest.php` — store-header and product-listing display.
- `tests/Feature/Admin/SellerBadgeManagementTest.php` — manual award, revoke, and authorization.
- `tests/Feature/SellerBadgeAwardJobTest.php` — scheduled criteria evaluation and idempotency.
- `tests/Unit/Services/VendorBadgeServiceTest.php` — criterion boundaries and active-badge selection.
- `tests/Feature/Vendor/SellerBadgeIsolationTest.php` — vendor-scoped badge access.

### ISSUE-08 — Vendor Support

**Labels:** `feature`, `module`, `premium`, `tier:professional`, `effort:M`
**Module key:** `vendor_support_module`
**Tier:** Professional, Business, Enterprise
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The catalog promises vendors an in-dashboard channel to open support tickets, communicate with admins, change status, assign agents, and receive reply notifications. The existing Support module is scoped around customers, chatbot sessions, and admin agents, but it has no vendor scope or vendor-facing ticket workflow.

**Scope**
- [ ] Extend `SupportTicket` with a nullable vendor scope and migration while preserving existing customer and chatbot-ticket behavior.
- [ ] Add vendor relationships to `SupportTicket` and vendor-facing authorization to `SupportTicketPolicy`.
- [ ] Add a vendor support controller for opening, listing, and viewing vendor tickets.
- [ ] Add vendor reply actions scoped to the current vendor's tickets.
- [ ] Extend the admin support workflow to display vendor context and explicitly support assignment, status changes, and replies.
- [ ] Send an email notification to the vendor on each new admin or support reply, using the existing support-mail surface.
- [ ] Set `vendor_support_module` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A vendor can open a support ticket from the vendor dashboard and see it in a vendor ticket list.
- [ ] A vendor can reply only to tickets belonging to that vendor.
- [ ] An admin can reply to a vendor ticket, change its status, and assign an agent.
- [ ] The vendor receives an email notification for every new reply.
- [ ] Existing customer and chatbot support tickets continue to work without exposing vendor tickets to customers.
- [ ] Automated tests prove ticket creation, vendor isolation, admin actions, notifications, and backward compatibility.

**Dependencies**
The existing Support module, `SupportTicket`, `SupportMessage`, `User`, `Vendor`, admin support ticket controller, and email notifications.

**Files to touch**
- `app/Models/SupportTicket.php` — vendor scope and vendor relationship.
- `app/Models/SupportMessage.php` — support-message author and ticket behavior for vendor messages.
- `database/migrations/*_add_vendor_id_to_support_tickets_table.php` — nullable vendor scope and indexes.
- `app/Policies/SupportTicketPolicy.php` — vendor ownership and admin access rules.
- `app/Http/Controllers/Vendor/SupportController.php` — vendor ticket and reply workflow.
- `app/Http/Controllers/Admin/SupportTicketController.php` — vendor context, assignment, status, and reply actions.
- `app/Mail/SupportTicketCreated.php` and new support-reply mail — ticket and reply notifications.
- `resources/views/vendor/support/*` — vendor ticket list and conversation views.
- `routes/web.php` — vendor support routes.
- `config/modules.php` — set the module implementation flag after delivery.

**Risk notes**
- Vendor isolation is critical because adding `vendor_id` to a shared ticket table must not weaken existing customer or chatbot-ticket policies.
- Reply notifications can become an abuse or spam vector unless sender, ticket, and recipient are scoped correctly.
- Ticket lists and conversations can trigger N+1 queries for messages, assignees, and vendor details unless relations are eager-loaded.

**Test plan**
- `tests/Feature/VendorSupportTest.php` — vendor ticket creation, reply, and notifications.
- `tests/Feature/Vendor/VendorSupportIsolationTest.php` — cross-vendor ticket and message denial.
- `tests/Feature/Admin/VendorSupportModerationTest.php` — assignment, status changes, and admin replies.
- `tests/Feature/SupportTicketBackwardCompatibilityTest.php` — existing customer and chatbot-ticket behavior.
- `tests/e2e/vendor-support.spec.ts` — vendor opens a ticket and exchanges an admin reply.

### ISSUE-09 — Store Support

**Labels:** `feature`, `module`, `premium`, `tier:professional`, `effort:S`
**Module key:** `store_support`
**Tier:** Professional, Business, Enterprise
**Current state:** `config/modules.php` → `implemented: false`; still listed in paid tier entitlements.

**Problem**
The catalog promises customers a frontend support form on vendor store pages so they can get help from the vendor. There is currently no store-level support entry point, and the shared support records cannot yet be routed to a vendor because the vendor-scoped support workflow is delivered by ISSUE-08.

**Scope**
- [ ] Add a store-level support form to vendor storefront pages with the vendor selected from the storefront context.
- [ ] Reuse `SupportTicket` and `SupportMessage` with the vendor scope introduced by ISSUE-08.
- [ ] Route each store support request to the relevant vendor staff or vendor owner.
- [ ] Add customer access to the resulting support conversation and reply notifications.
- [ ] Enforce module access and vendor ownership on store support submission and display.
- [ ] Keep third-party ticketing integration optional so the core flow remains the existing Support module.
- [ ] Set `store_support` to `implemented: true` in `config/modules.php` only after the acceptance criteria pass.

**Acceptance criteria**
- [ ] A customer can open a support request from a vendor store page and the request is linked to that vendor.
- [ ] The request appears in the vendor support workflow and is routed to the vendor's staff or owner.
- [ ] A vendor reply is visible to the customer and triggers a customer notification.
- [ ] A support request cannot be submitted to or displayed for a different vendor.
- [ ] The storefront support form is unavailable when the vendor does not have the module entitlement.
- [ ] Automated tests prove store submission, routing, notifications, module access, and vendor isolation.

**Dependencies**
ISSUE-08 — Vendor Support; `SupportTicket`, `SupportMessage`, `Vendor`, `User`, vendor storefront, and the vendor support workflow.

**Files to touch**
- `app/Http/Controllers/Shop/StoreSupportController.php` — customer store-support submission and conversation access.
- `app/Http/Controllers/Vendor/SupportController.php` — reuse the vendor support inbox and reply workflow from ISSUE-08.
- `app/Models/SupportTicket.php` — store-support metadata and vendor-scoped relationships, if required by the shared ticket design.
- `resources/views/vendor-store/show.blade.php` — store support entry point.
- `resources/views/components/store-support.blade.php` — reusable support form and conversation partial.
- `app/Mail/StoreSupportNotification.php` — customer and vendor notifications, or extend the existing support mail classes.
- `routes/web.php` — storefront support routes.
- `config/modules.php` — set the module implementation flag after delivery.

**Risk notes**
- The vendor must be derived from the storefront context and revalidated server-side to prevent cross-vendor ticket creation.
- Public store support input needs validation, rate limiting, and abuse handling to prevent spam.
- An optional ticketing integration must not bypass the vendor-isolation rules or the existing support notification flow.

**Test plan**
- `tests/Feature/StoreSupportTest.php` — storefront submission, routing, replies, and notifications.
- `tests/Feature/StoreSupportIsolationTest.php` — cross-vendor submission and conversation denial.
- `tests/Feature/StoreSupportModuleAccessTest.php` — entitlement enforcement for the storefront form.
- `tests/e2e/store-support.spec.ts` — customer opens a store request and receives a vendor reply.

## Cross-cutting gaps

These gaps are outside the nine module issues above and remain represented as backlog or technical-debt work.

| Feature | Source | Status | Notes |
|---|---|---|---|
| Installer System (CMS-style web wizard) | PRD §3 | Not implemented (documented) | README explicitly states not implemented; deploy via runbook |
| Virtual Try-On (AR) | PRD §2.1.2 (Phase 2) | Deferred | Phase 2 scope |
| Multi-language (Swahili, Portuguese, Arabic) | PRD §2.4.2 | Partial (only en/es/fr) | Add `sw`, `pt`, `ar` to `lang/` |
| Multi-currency switcher | PRD §2.4.2 | Partial (per-transaction currency only) | Add currency config + switcher + rates |
| PDF Invoice generation | `subscription-modules.md` | Not implemented | Add invoice template + PDF lib + email attachment |
| Printful Integration | `subscription-modules.md` | Not implemented | Optional fulfillment adapter |
| ShipStation Integration | `subscription-modules.md` | Not implemented | Optional shipping adapter |
| Wirecard | `subscription-modules.md` | Not implemented | Deprecated (Wirecard insolvent) — recommend removing from catalog |
| Smart Migrator Tool | `subscription-modules.md` | Not implemented | Optional migration tooling |
| Product Duplicator | `subscription-modules.md` | Not implemented | Add duplicate action |
| Single Product Multivendor | `subscription-modules.md` | Not implemented | Shared listings + vendor comparison |
| Vendor Vacation | `subscription-modules.md` | Not implemented | Vacation flag + auto-hide |
| Product Addon / upsells | `subscription-modules.md` | Not implemented | Addon templates + pricing |
| Table Rate Shipping | `subscription-modules.md` | Partial (basic shipping exists) | Complex rate tables per vendor |
| Minimum/Maximum Order | `subscription-modules.md` | Not implemented | Quantity/value limits |
| Product Subscription (customer subscribes to a product) | `subscription-modules.md` | Not implemented | Recurring product subscriptions |
| Delivery Time (date/time slots) | `subscription-modules.md` | Not implemented | Slot selection in checkout |
| Stripe Connect (vendor payouts) | `subscription-modules.md` | Partial (`StripePaymentService` exists; Connect onboarding not) | Platform onboarding + automated payouts |
| Duplicate auth middleware (`AdminMiddleware` vs `EnsureUserIsAdmin`) | `overview.md` debt #2 | Tech debt | Consolidate |
| `env()` outside config (2 sites) | `overview.md` debt #3 | Tech debt | `ConfigurationService`, `VerifyReadinessCommand` |
| Test coverage (6 files vs 146 controllers) | `overview.md` debt #5 | Tech debt | Expand per roadmap Phase 2 |
| Application-enforced tenancy (not DB-level) | `overview.md` | Architectural risk | Add DB-level scoping or strengthen tests |

## Sequencing recommendation

> The platform is **feature-rich and under-verified**. Prefer making the existing surface trustworthy before adding modules.

1. **First:** Land Blocker A (CI executes zero tests — decide whether to re-track the suite or make CI honest) and ISSUE-00 (gate plan entitlements on the `implemented` flag), so unimplemented modules stop being granted to paying tiers. Then remove the unimplemented modules from the tier lists and marketing catalog, or deliver them under ISSUE-01 … ISSUE-09.
2. **Then:** Establish the Phase 2 test foundation for vendor isolation and money paths from `roadmap.md`; apply the same gates to each issue in this backlog.
3. **Then:** Deliver the nine issues in this order: Product Q&A → Request for Quotation → Vendor Staff Manager → Wholesale → EU Compliance Fields → Report Abuse → Seller Badge → Vendor Support → Store Support.
4. **Optional/deferred:** Printful, ShipStation, PDF Invoice, Smart Migrator, Virtual Try-On (Phase 2), Installer wizard, multi-language expansion (`sw`, `pt`, `ar`), multi-currency, and middleware consolidation.
