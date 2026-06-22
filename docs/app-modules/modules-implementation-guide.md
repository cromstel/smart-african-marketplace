# MASTER IMPLEMENTATION PROMPT — MULTIVENDOR MARKETPLACE MODULES

## 🎯 Objective

You are a senior WordPress + WooCommerce + E-commerce developer architect.

Your task is to **plan, configure, and implement App modules** on the existing multivendor marketplace to achieve a **production-ready, scalable, secure marketplace**.

You must:

* Configure modules correctly
* Respect dependencies
* Optimize UX for vendors and customers
* Ensure payment and payout flows are safe
* Follow WordPress best practices
* Avoid breaking existing functionality

---

# 🧩 Project Context

## Platform Stack

* Wordpress Style
* Woocommerce Style
* Theme: (detect or develop automatically if not found)
* Hosting: (auto-detect environment local or production)
* Region focus: African and EU-friendly (VAT & GDPR aware)

## Business Model

Marketplace type: multi-vendor ecommerce

Primary goals:

* Smooth vendor onboarding
* Reliable split payments
* Strong vendor tools
* High conversion UX
* Scalable architecture

---

# 📦 Modules to Implement

You must work from the provided module list (CSV provided separately) and implement them **in the correct dependency order**.

## 🔴 Phase 1 — Critical Infrastructure (MUST DO FIRST)

These affect money flow and core operations.

### Payments & Payouts

Implement and configure:

* Stripe Connect
* Stripe Express
* Paystack (Africa Alternative)
* Swish (EU alternative)
* MOMO Pay (Africa alternative) 

### Requirements

You must:

* Enable sandbox first
* Configure webhooks
* Configure split commissions
* Verify vendor onboarding flow
* Test payout flow end-to-end
* Log all payment events

### Deliverables

* Payment architecture diagram
* Test transaction results
* Webhook verification report

---

## 🟠 Phase 2 — Shipping & Logistics

Implement:

* Table Rate Shipping
* ShipStation
* Delivery Time

### Requirements

You must:

* Support per-vendor shipping
* Support shipping zones
* Support weight-based rules
* Ensure checkout accuracy
* Test multi-vendor cart shipping

### Deliverables

* Shipping rule matrix
* Multi-vendor checkout test

---

## 🟡 Phase 3 — Vendor Trust & Operations

Implement:

* Seller Verification
* Vendor Support
* Vendor Staff Manager
* Vendor Analytics

### Requirements

You must:

* Design vendor onboarding flow
* Define verification workflow
* Configure staff permissions matrix
* Ensure analytics accuracy

---

## 🟢 Phase 4 — Monetization Features

Implement:

* Subscriptions (Vendor Packs)
* Product Advertising
* Wholesale
* Product Addon
* Minimum Maximum Order

### Requirements

You must:

* Design pricing tiers
* Configure recurring billing
* Prevent feature leakage across tiers
* Test subscription lifecycle

---

## 🔵 Phase 5 — Customer Experience

Implement:

* Ajax Live Search
* Follow Store
* Vendor Review
* Product Q&A
* Live Chat
* Product Enquiry
* Request for Quotation
* Return and Warranty Request

### Requirements

You must:

* Optimize performance
* Prevent spam/abuse
* Ensure mobile usability
* Enable moderation tools

---

## 🟣 Phase 6 — Growth & Advanced Features

Implement:

* Geolocation
* Single Product Multivendor
* Best SEO
* Export/Import
* Multilingual enabled
* Social Login

---

# 🔒 Security & Compliance Requirements

You MUST ensure:

## Payments

* No raw card data stored
* Webhooks validated
* Vendor payout safety checks
* Fraud prevention basics

## EU Compliance

Implement and verify:

* EU Compliance Fields
* VAT handling
* GDPR readiness
* Cookie compliance compatibility

---

# ⚙️ Technical Standards

You must follow:

## Best Practices

* No core file edits
* Use hooks/filters
* Use theme if needed

---

## Performance

You must:

* Avoid heavy queries
* Enable caching compatibility
* Test with 100+ vendors scenario
* Lazy-load where possible

## Code Quality

* Modular
* Documented
* Reversible
* Version controlled

---

# 🧪 Testing Requirements

You MUST perform:

## Vendor Flow Test

1. Vendor registration
2. Verification
3. Product upload
4. Shipping setup (multi-vendors)
5. Order received
6. Commission calculated
7. Payout processed

## Customer Flow Test

1. Browse
2. Search
3. Add to cart (multi-vendors)
4. Checkout
5. Payment
6. Delivery selection
7. Review/Q&A

## Edge Cases

Test:

* Multiple vendors same product
* Subscription expiry
* Failed payments
* Refund + return flow

---

# 📊 Required Final Deliverables

You must output:

## 1. Implementation Report

Include:

* What was configured
* What requires manual keys
* Risks found
* Performance notes

## 2. Configuration Checklist

Step-by-step admin checklist.

## 3. Vendor Guide

Short guide explaining:

* How vendors onboard
* How payouts work
* How shipping works

## 4. Known Limitations

Be honest and explicit.

---

# 🚨 Guardrails

You MUST NOT:

* Break existing products
* Disable WooCommerce defaults without reason
* Enable region-locked gateways unnecessarily
* Leave sandbox keys in production
* Ignore webhook failures
* Ignore EU compliance

If something is uncertain, **flag it clearly instead of guessing**.

---

# ✅ Definition of Done

The implementation is complete ONLY when:

* Multi-vendor checkout works
* Split payments verified
* Vendors can operate independently
* Admin has full oversight
* No critical console errors
* Performance is acceptable
* Security checks pass

---

# 🧠 Execution Mode

Work methodically.

Order of work:

1. Audit environment
2. Map dependencies
3. Implement by phases
4. Test each phase
5. Produce final report

Do not rush to configuration before understanding the system.

---

**END OF PROMPT**
