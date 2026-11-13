# Multivendor Marketplace Modules — Documented

**Source:** Website Modules page — summarized for implementation planning.

---

## Overview

This document lists the app subscription modules that needs to be shown on the official site "Modules" page and provides: a short descriptive summary, availability tier (as expected on the page), and practical implementation notes / configuration suggestions for adding each module to the marketplace.

> Note: this is a technical implementation-focused summary intended to help you prioritize and integrate modules on the marketplace website. It is a direct summary of the modules required on the official modules page.

---

## How to use this file

1. Review the modules grouped below.
2. For modules you want, follow the Implementation Notes to prepare credentials, plugin settings, vendor onboarding flows, and testing steps.
3. Use the "Quick implementation checklist" at the end of this file when you start enabling modules.

---

## Premium / Core Modules

Each module entry follows this format:
- **Module name** — *Short description.*  
  **Available in:** tier(s)  
  **Implementation notes:** short checklist / tips.


- **Smart Booking System** — Allow vendors to create bookable products and allow customers to customize bookings.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Implement booking add-on and Smart Booking System; provide UI in vendor dashboard for creating bookable slots, set booking rules (buffer times, cancellation window), test booking flow and vendor calendar sync.

- **Smart Auction System** — Add auction functionality so vendors can list auction-style products.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Ensure auction system compatibility with the website; configure bidding rules, reserve price, auction duration, and notifications to bidders and sellers.

- **Paystack Module** — Integrate Paystack payment gateway (popular in Africa).  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Obtain Paystack API keys per environment, enable Paystack in the payment options, set webhook endpoints, verify test transactions and payouts.

- **Vendor Support Module** — In-dashboard support channel to make communication between vendors and admins easy.  
  **Available in:** Starter, Professional, Business, Enterprise  
  **Implementation notes:** Decide SLA for support replies, enable ticketing or messaging in vendor dashboard, train support staff, and create templated responses.

- **Printful Integration** — Connect vendor stores with Printful for print-on-demand fulfillment.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Vendors will need Printful accounts and API keys; validate product sync, mock orders in sandbox, and map shipping rules.

- **Seller Badge** — Award badges to vendors to acknowledge achievements (e.g., top-seller, verified, fast-ship).  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Define badge criteria, display locations (store header, listings), and automation rules (e.g., based on sales volume or review rating).

- **Stripe Express** — Stripe Express onboarding for vendors (simplified Stripe onboarding).  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Register your platform with Stripe, configure Express onboarding flows, map vendor payout schedules and commission splits.

- **Request for Quotation** — Allow customers to request price quotes from vendors.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Add quote request forms on product pages, create admin/vendor workflows to respond and convert quotes to orders.

- **Report Abuse** — Provide customers a way to report abusive products/vendors or listings.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Route abuse reports to moderation queue, define auto-actions (hide listing, suspend vendor), add acknowledgement emails.

- **Elementor** — Extension for better customization control over vendor-facing templates using Elementor.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Ensure Elementor and theme compatibility; create vendor template parts and document how vendors can customize store pages.

- **Stripe Connect** — Full Stripe Connect integration to handle payments and automated vendor payouts.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Configure Connect platform settings, handle fees/commissions, onboarding, and compliance (KYC) flows. Test payouts in Stripe test mode.

- **Table Rate Shipping** — Give vendors flexibility for complex shipping rates (by weight, classes, destination).  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Define shipping tables per vendor (zones, classes, weight breaks) and provide vendor UI to manage shipping rules.

- **Product Advertising** — Vendors can highlight or boost products (promoted listings).  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Decide ad placement, pricing model (CPC, flat fee, featured slot), and reporting metrics. Implement vendor purchase flow for advertising credits.

- **Minimum Maximum Order** — Admin and vendors can create restrictions on order quantities and values.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Decide whether limits are global or per-vendor; test validation messages and cart behavior.

- **Subscriptions (Vendor Packs)** — Create subscription packs to charge vendors recurring fees for store access/benefits.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Configure subscription products, tier benefits, renewal notices, and restrict features per pack.

- **Product Subscription** — Allow vendors to create subscription-based products customers can subscribe to.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Ensure compatibility with payment gateways that support recurring billing; test renewals, cancellations, and proration policies.

- **Follow Store** — Let customers follow vendors/stores to receive updates.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Add follow buttons on stores, set up notification preferences (email/push) and a followed-stores feed.

- **Geolocation** — Let customers search and discover vendors/products by location.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Integrate a map/search by radius UI, gather vendor location data fields, and consider privacy/EU rules for location data.

- **EU Compliance Fields** — Provide required fields for EU law compliance for customers and vendors (e.g., VAT fields).  
  **Available in:** Starter, Professional, Business, Enterprise  
  **Implementation notes:** Map required fields (VAT ID, company details), ensure admin review/validation and adapt checkout/invoice templates.

- **Delivery Time** — Allow customers to choose delivery times (date/time slots).  
  **Available in:** Starter, Professional, Business, Enterprise  
  **Implementation notes:** Configure available time slots per vendor, tie into shipping and order processing calendar.

- **Product Addon** — Customers can add extra items/options to a product and be billed separately (upsells and addons).  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Define addon templates (checkbox, select), pricing rules, and order metadata for vendors.

- **Ajax Live Search** — Turn standard search into instant, advanced product search with live results.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Tune search indexing, autocomplete suggestions, highlighting and performance testing (caching, throttling).

- **Color Scheme Customizer** — Let admin change vendor dashboard colors to match your marketplace theme.  
  **Available in:** Starter, Professional, Business, Enterprise  
  **Implementation notes:** Provide a palette interface, define defaults per theme, and ensure color contrast/accessibility.

- **Export Import** — Allow vendors to export & import products and details via .xml / CSV.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Provide import templates, field mapping, and validation error reporting. Offer demo files for vendors to test.

- **Live Chat** — Real-time chat for vendors to support customers.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Decide between built-in chat or third-party (configure web sockets), set availability hours, and integrate chat transcripts with tickets.

- **Wirecard** — Wirecard payment network integration (note: assess current Wirecard status / legality before enabling).  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Confirm Wirecard service availability in your jurisdiction, gather API keys, and run compliance checks.

- **Product Enquiry** — Let visitors contact the vendor directly from product pages to ask questions.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Add enquiry form on product pages, route enquiries to vendor mailbox/dashboard, and create response SLA.

- **Return and Warranty Request** — Allow vendors to offer customized return and warranty options and allow customers to request returns/warranty.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Create return request workflows, define vendor return policies, and provide admin override controls.

- **Seller Verification** — Allow admin to verify vendor authenticity via social profiles, phone & photo checks.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Define verification steps (ID upload, phone verification), display verified badge, and keep audit logs.

- **ShipStation** — Integrate ShipStation to manage shipments and labels (import/manage/ship orders).  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Connect ShipStation account, configure order import rules, map carrier accounts, and test label generation and tracking sync.

- **Single Product Multivendor** — Allow multiple vendors to sell the same product (shared listings) so customers can compare vendor prices for the same SKU.  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Decide master product-to-vendor mapping, handle inventory per vendor, and show vendor comparison UI.

- **Store Support** — Frontend support features for customers to get help from vendors (support widget on store pages).  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Add store-level support forms, route to vendor staff, and integrate with ticketing if available.

- **Vendor Review** — Allow customers to rate and post reviews on store pages (not just products).  
  **Available in:** Professional, Business, Enterprise  
  **Implementation notes:** Add review moderation, tie reviews to purchases if needed, and enable vendor responses.

- **Vendor Staff Manager** — Let sellers appoint unlimited staff accounts to manage their store.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Add role/permission matrix for staff, audit logs, and staff invitation flows.

- **Vendor Vacation** — Allow vendors to put their store/products offline temporarily when on vacation.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Vendor UI to set vacation dates, automatic product status changes, and notice to customers.

- **Wholesale** — Enable vendors to offer wholesale pricing options to customers.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Configure tiered pricing, quantity breaks, and wholesale-only checkout restrictions.

- **Vendor Analytics** — Frontend analytics dashboard for vendors to view store metrics.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Expose sales, traffic, conversion, and top-products. Consider integrating with Google Analytics for deeper reporting.

- **Product Q&A** — Allow customers to ask product-specific questions and vendors to answer publicly on the product page.  
  **Available in:** Business, Enterprise  
  **Implementation notes:** Add Q&A moderation tools, email notifications to followers, and caching strategy for frequently asked questions.

---

## Supportive Plugins (scanned)

- **Smart Migrator Tool** — Tools to migrate marketplaces from other multi-vendor platforms (WCFM, WC Vendors, YITH) into this platform.
  **Implementation notes:** Run migration on staging first, validate data, and map custom fields.

- **Vendor Dashboard** — Alternative cleaned, organized vendor dashboard UI.
  **Implementation notes:** Test dashboard UX with sample vendors, ensure widgets match enabled modules.

- **Product Duplicator** — Allow vendors to copy existing products created by another seller (helpful for same-product sharing).
  **Implementation notes:** Add permission checks and duplication metadata.

- **WPML Integration** — Multilingual plugin support so products/pages can be displayed in multiple languages.
  **Implementation notes:** Ensure WPML strings are registered, provide vendor guidance for translations.

- **PDF Invoice** — Create automated multi-seller invoices with templates and PDF downloads.
  **Implementation notes:** Configure invoice templates, VAT/tax fields, and automated email attachments.

- **Coupons** — (Referenced site-wide) Allow vendor-level coupon creation and management (if enabled on the platform).

---

## Quick implementation checklist

1. Create a staging environment to enable and test modules.  
2. Prioritize payment & shipping modules (Stripe/Paystack/Sweish/MOMO Pay/ShipStation) — these affect money flow.  
3. Prepare vendor onboarding docs (how to enable payment, verification, subscription packs).  
4. Test key flows end-to-end: vendor signup → product listing → checkout → payout.  
5. Enable monitoring & logging for webhooks and third-party integrations.  
6. Document feature toggles per tenant (if you operate multi-tenant) and rollback steps.

---

## Recommended order to roll out (high-level)

1. Core payments & payouts (Stripe Connect / Paystack/ Swish / MOMO Pay / Stripe Express)
2. Shipping & logistics (Table Rate Shipping / ShipStation / Delivery Time)
3. Vendor onboarding & trust (Seller Verification / Vendor Support / Vendor Staff Manager)
4. Vendor monetization features (Subscriptions / Product Advertising / Product Addon / Wholesale)
5. Customer engagement (Live Chat / Product Q&A / Follow Store / Vendor Review)

---

## Notes & assumptions

- This document is a high-level implementation map based on the required modules page content and short module summaries that must appear there.
- For each third-party integration, you'll need provider API keys and platform approval (Stripe/Paystack often require KYC for marketplace payout flows).
- Always test on a staging site before enabling on production.

---
