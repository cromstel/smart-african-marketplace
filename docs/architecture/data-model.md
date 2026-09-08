# Data Model

**Status: Current** — relationships extracted from `app/Models/` on 2026-09-08.

[Documentation Hub](../README.md) › Architecture › Data Model

The 98 Eloquent models, grouped by domain. Relationships below were read from the
model classes, not inferred. For field-level detail read the migrations in
`database/migrations/` (148 of them) — this page is the map, not the territory.

---

## Contents

- [Reading this document](#reading-this-document)
- [Identity and access](#identity-and-access)
- [Catalogue](#catalogue)
- [Commerce](#commerce)
- [Multi-vendor](#multi-vendor)
- [Delivery and fulfilment](#delivery-and-fulfilment)
- [Salon and booking](#salon-and-booking)
- [Auctions](#auctions)
- [Subscriptions](#subscriptions)
- [Content and CMS](#content-and-cms)
- [AI chatbot](#ai-chatbot)
- [Marketing and loyalty](#marketing-and-loyalty)
- [Support and returns](#support-and-returns)
- [Trust, safety and compliance](#trust-safety-and-compliance)
- [Configuration and platform](#configuration-and-platform)
- [Conventions and cautions](#conventions-and-cautions)

---

## Reading this document

Every model declares `$fillable` — no model uses `$guarded = []`, so
mass-assignment is closed by default. Keep it that way.

The database is **single-tenant with application-level vendor separation**. There
is no row-level security and no per-tenant schema: a query that forgets its
vendor constraint returns another vendor's data. See
[Conventions and cautions](#conventions-and-cautions).

---

## Identity and access

| Model | Notes |
|---|---|
| `User` | Central identity for every role |
| `Role` | Local role model, alongside `spatie/laravel-permission` |
| `Address` | Shipping and billing addresses |
| `UserNotification` | In-app notifications |
| `ActivityLog` | Audit trail (`spatie/laravel-activitylog`) |

`User` relates to: `Address`, `Booking`, `Cart`, `Order`, `Review`, `Role`,
`UserNotification`, `UserSubscription`, `Vendor`, `VendorFollower`, `Wishlist`,
`Withdrawal`.

One `User` may *be* a `Vendor` and also *follow* vendors — role is a property of
the relationship, not a separate account.

---

## Catalogue

| Model | Notes |
|---|---|
| `Product` | Central catalogue entity |
| `ProductVariant` / `ProductVariation` | ⚠️ **Two similarly named models** — see cautions |
| `ProductImage` | Gallery images |
| `ProductAttribute` | Structured attributes |
| `ProductTag` | Free-form tagging |
| `Category` | Hierarchical categories |
| `Brand` | Manufacturer/brand |
| `ProductEnquiry` | Buyer questions |
| `ProductImportHistory` / `ProductExportLog` | Bulk operations |

`Product` relates to: `Brand`, `CartItem`, `Category`, `OrderItem`,
`ProductImage`, `ProductTag`, `ProductVariation`, `Review`, `Vendor`, `Wishlist`.

---

## Commerce

| Model | Notes |
|---|---|
| `Cart` / `CartItem` | Pre-purchase basket |
| `Order` | A customer purchase |
| `OrderGroup` | Groups orders split across vendors in one checkout |
| `OrderItem` | Line items; carries the vendor link |
| `OrderNote` | Internal and customer-facing notes |
| `Payment` | Payment attempts and outcomes |
| `Coupon` | Discounts |
| `Wishlist` / `WishlistShare` | Saved and shared items |

`Order` relates to: `Address`, `Delivery`, `OrderGroup`, `OrderItem`,
`OrderNote`, `Payment`, `Shipment`, `User`.

**`OrderGroup` is the multi-vendor pivot.** One customer checkout containing
items from three vendors produces one `OrderGroup` and three `Order` rows, so
each vendor sees and fulfils only their own. Any change to checkout must preserve
that split.

`Payment` belongs to `Order`. Webhook handlers must be idempotent — a replayed
provider event must not create a second `Payment`
([Testing Guide](../guides/testing.md#testing-webhooks)).

---

## Multi-vendor

| Model | Notes |
|---|---|
| `Vendor` | A selling entity, owned by a `User` |
| `VendorBalance` | Current balance |
| `VendorLedger` | Append-only financial history |
| `Payout` | Platform → vendor disbursement |
| `Withdrawal` | Vendor-initiated request |
| `VendorVerification` | KYC / onboarding checks |
| `VendorSubscription` | Vendor's plan |
| `VendorTheme` | Storefront theming |
| `VendorCategory` | Categories a vendor sells in |
| `VendorAvailability` | Trading hours |
| `VendorFollower` | Customers following a vendor |

`Vendor` relates to: `OrderItem`, `Payout`, `Product`, `User`,
`VendorAvailability`, `VendorBalance`, `VendorCategory`, `VendorFollower`,
`VendorLedger`, `VendorSubscription`, `VendorTheme`, `VendorVerification`.

`VendorBalance` is derived state; `VendorLedger` is the source of truth. When
they disagree, the ledger is right — reconcile toward it, and never adjust a
balance without a corresponding ledger entry.

---

## Delivery and fulfilment

| Model | Notes |
|---|---|
| `Delivery` | A delivery job against an order |
| `DeliveryDriver` | Courier |
| `DeliveryZone` | Geographic zone with pricing |
| `Shipment` | Shipment record and tracking |

Pricing combines `BASE_DELIVERY_FEE` and `PER_KM_RATE` with zone rules
([Configuration](../reference/configuration.md#commerce-and-delivery)). Proof of
delivery is gated by `POD_PHOTO_REQUIRED` and `POD_SIGNATURE_REQUIRED`.

---

## Salon and booking

| Model | Notes |
|---|---|
| `Salon` | Salon business |
| `SalonService` / `SalonServiceCategory` | Bookable services |
| `SalonStylist` | Practitioner |
| `Booking` | An appointment |
| `BookingType` | Booking category |
| `BookingResource` | Scheduled resource (room, chair, equipment) |
| `ServicePricingTier` | Tiered pricing |
| `VendorAvailability` | Bookable windows |

`Booking` relates to: `Booking` (self-referential — reschedules or linked
appointments), `BookingResource`, `BookingType`, `SalonService`, `SalonStylist`,
`User`.

Slot integrity is the risk here: two customers must not hold the same stylist and
resource at the same time. Concurrency, not validation, is what breaks it.

---

## Auctions

| Model | Notes |
|---|---|
| `Auction` | A listing on auction |
| `AuctionBid` | An individual bid |
| `AuctionAutoBid` | Proxy bidding with a maximum |
| `AuctionWinner` | Settled outcome |
| `AuctionNotification` | Outbid and closing alerts |

`Auction` relates to: `AuctionAutoBid`, `AuctionBid`, `AuctionNotification`,
`AuctionWinner`, `Product`, `User`, `Vendor`.

Settled by `php artisan auction:process`. Bidding is the most
concurrency-sensitive path in the platform — two bids arriving in the same
instant must produce one deterministic winner.

---

## Subscriptions

| Model | Notes |
|---|---|
| `SubscriptionPlan` | Plan definition and limits |
| `UserSubscription` | A customer's subscription |
| `VendorSubscription` | A vendor's subscription |
| `SubscriptionPayment` | Billing history |

Enforced by `CheckSubscription`, `CheckSubscriptionLimits` and
`EnsureModuleAccess`, resolved through `EffectiveSubscriptionResolver`. Gating
must be server-side — hiding a menu item is not entitlement.

---

## Content and CMS

| Model | Notes |
|---|---|
| `BlogPost` / `BlogCategory` / `BlogTag` / `BlogComment` | Blog |
| `BlogPostRevision` | Version history |
| `SitePage` | Static pages |
| `AboutPageContent` / `HeroSection` | Homepage content blocks |
| `Widget` | Configurable UI widgets |
| `MediaLibrary` / `MediaFolder` | Uploaded assets |
| `SeoMeta` | Per-entity SEO metadata |
| `Announcement` / `AnnouncementRead` | Site-wide notices with read tracking |

---

## AI chatbot

| Model | Notes |
|---|---|
| `ChatbotSession` | A conversation |
| `ChatbotMessage` | A turn within it |
| `KnowledgeDocument` | Source document |
| `KnowledgeChunk` | Retrievable chunk |
| `AiSetting` | Model and behaviour configuration |

A retrieval-augmented setup: documents are chunked into `KnowledgeChunk`, and
`chatbot:reindex-knowledge` rebuilds the index. `KnowledgeBaseObserver` keeps
chunks in step with their documents.

---

## Marketing and loyalty

| Model | Notes |
|---|---|
| `Campaign` / `CampaignRecipient` / `CampaignEvent` | Campaigns and engagement |
| `LeadAttribution` | Source attribution (`CaptureCampaignContext`) |
| `Newsletter` | Subscribers |
| `Referral` | Referral tracking |
| `LoyaltyPoint` | Points balances and expiry |
| `SearchQuery` | Search terms, for merchandising |
| `AnalyticsEvent` | Product analytics |

---

## Support and returns

| Model | Notes |
|---|---|
| `SupportTicket` / `SupportMessage` | Support threads |
| `Inquiry` | General enquiries |
| `RefundRequest` | Customer refund request |
| `RmaRequest` | Return merchandise authorisation |

---

## Trust, safety and compliance

| Model | Notes |
|---|---|
| `Review` / `ReviewImage` / `ReviewHelpfulVote` | Ratings and reviews |
| `ActivityLog` | Audit trail |
| `DataExportRequest` | GDPR subject access / portability |
| `ConfigurationAudit` | Who changed which setting |

---

## Configuration and platform

| Model | Notes |
|---|---|
| `Setting` | Key/value runtime settings |
| `ConfigurationSetting` / `ConfigurationCategory` | Structured admin configuration |
| `ConfigurationAudit` | Change history |

Encrypted values are handled by `ConfigurationEncryptionService`. Note this sits
*alongside* `.env`-based configuration; see
[Conventions and cautions](#conventions-and-cautions).

---

## Conventions and cautions

**1. Vendor scoping is a security control.** No row-level security exists. Every
query touching vendor-owned data must constrain by ownership, and every
vendor-scoped endpoint needs an isolation test
([pattern](../guides/testing.md#the-vendor-isolation-pattern)).

**2. `ProductVariant` vs `ProductVariation`.** Two models with near-identical
names. `Product` relates to `ProductVariation`. Check which one you need before
writing a query — this is a live trap for newcomers and a candidate for
consolidation.

**3. Three configuration systems coexist.** `.env` → `config/`, the `Setting`
model, and `ConfigurationSetting`. Know which owns the value you are changing;
`ConfigurationSetting` is audited, `Setting` is not.

**4. Ledger over balance.** `VendorLedger` is authoritative; `VendorBalance` is a
projection.

**5. `OrderGroup` carries the multi-vendor split.** Preserve it in any checkout
change.

**6. Read the migration, not just the model.** Constraints, indexes and defaults
live in `database/migrations/`. `$fillable` tells you what is writable, not what
is valid.

---

## See also

- [Architecture Overview](overview.md)
- [Testing Guide](../guides/testing.md)
- [Database Optimization Runbook](../DATABASE_OPTIMIZATION_AND_CLEANUP_RUNBOOK.md)
- [Configuration Reference](../reference/configuration.md)
