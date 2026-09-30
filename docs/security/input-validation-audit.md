# Input Validation Audit — Form Requests and Controller Validation

**Status: Current** — 2026-09-15.

[Documentation Hub](../README.md) › Security › Input Validation Audit

This is the discrete audit deliverable for the externally-tracked todo item
"Input validation audit across Form Request classes." As worded, that item
doesn't map cleanly onto this codebase: the dominant convention here is
inline `$request->validate()` / `Validator::make()` inside controllers (188
call sites across roughly 50 controllers), not dedicated Form Request
classes. Only three exist app-wide: `Auth/LoginRequest`,
`ProfileUpdateRequest`, `UpdateConfigurationRequest`. This audit therefore
covers validation coverage generally, not just the three Form Request
classes literally named by the todo item's wording.

## Methodology

A naive `grep -r "->validate("` search produces both false positives and
false negatives, so every ambiguous result was verified by reading the
actual controller before being counted as a gap or cleared as fine:

- `$request->validateWithBag('bag', [...])` is a real, distinct Laravel
  validation call that a plain `->validate\(` regex misses entirely —
  `Auth/PasswordController::update()` uses this and is correctly validated.
- A type-hinted Form Request parameter (`LoginRequest $request`) runs
  validation before the controller body executes at all —
  `Auth/AdminLoginController::store()` is correctly validated this way.
- Several controllers share a private validation helper method further down
  the file than a naive line-proximity check expects
  (`Vendor/ProductAttributeController`, `Admin/ProductAttributeController`).
- Some flagged "missing validation" methods are deliberately disabled stubs
  with explanatory redirect messages, not bugs
  (`Vendor/BrandController::update()`/`destroy()`, `Admin/OrderController::store()`).

None of the above were gaps. All investigated and ruled out during this
audit pass.

## Genuine findings (fixed)

### 1. `app/Http/Controllers/AddressController.php` was entirely unimplemented

This is the customer-facing "Address Book" controller
(`Route::resource('addresses', AddressController::class)`, inside the
authenticated `web` group). Every method except `index()` was a literal
empty stub (`//`), and `resources/views/addresses/` did not exist at all —
so even `index()` would have thrown a `ViewNotFoundException`. This is not a
theoretical gap: `resources/views/checkout/index.blade.php` links to
`route('addresses.create')` as the "Add Address" call to action shown to any
customer with no saved shipping/billing address, meaning first-time
customers on the checkout money path already hit this broken controller in
production.

**Fixed:** full CRUD implemented (`index`, `create`, `store`, `show` —
redirects to `edit` since there's no separate read-only view to duplicate,
`edit`, `update`, `destroy`), matching `Api\AddressController`'s validation
rules and ownership-check pattern (manual `abort_if($address->user_id !==
auth()->id(), 403)`, the same style already used elsewhere in this codebase,
e.g. `WishlistController::destroy()`). New views:
`resources/views/addresses/{index,create,edit}.blade.php` and a shared
`resources/views/addresses/partials/form.blade.php`, following the existing
dark-theme `layouts.shop` conventions used by
`resources/views/orders/index.blade.php` and
`resources/views/checkout/index.blade.php`. New factory: `database/factories/AddressFactory.php`
(the model declared `HasFactory` but no factory file existed). New test:
`tests/Feature/AddressBookTest.php` — create/list/update/delete, validation
of required fields, default-address unsetting, ownership isolation (403 on
another user's address for show/edit/update/destroy), guest redirect.

### 2. `Api\AddressController`'s validation didn't match its own database schema

`database/migrations/2026_02_15_131429_create_addresses_table.php` defines `state`,
`postal_code` and `phone` as `NOT NULL` with no default. Both
`Api\AddressController::store()`/`update()` validated these three fields as
`nullable`. A request that omitted them would pass validation and then fail
with a raw `QueryException` at the database layer — an ugly 500 instead of a
clean 422. **Fixed:** changed to `required` (store) /
`sometimes|required` (update), matching the schema, and matching the new web
`AddressController`'s rules so both surfaces enforce identical constraints.
This is not a behavior regression for any previously-working request: any
request that omitted these fields was already failing, just with the wrong
error shape.

## Verification status

`tests/Feature/AddressBookTest.php` has been written but **not yet
executed**, blocked by the same MariaDB grant-table outage described in
`docs/security/rate-limiting-audit.md` and in project memory
(the engineering-conventions notes, not part of this repository). Treat this fix as **pending first
run**, not complete, until the suite executes green on this machine or in
CI.

## Conclusion

Validation coverage across the codebase's dominant inline-validation
convention is broad; the audit's real value was in the two items above,
both now fixed rather than merely flagged.
