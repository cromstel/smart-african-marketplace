# Documentation

The single entry point for every document in this repository. If you are looking
for something and it is not linked from this page, it is either not written yet
or it belongs in [Not yet written](#not-yet-written).

> **New here?**
> - **Customers, vendors and admins** start at the [Customer Guide](customer-guide.md),
>   [Vendor Guide](vendor-guide.md) or [Admin Guide](admin-guide.md).
> - **Developers** start at [Getting Started](guides/getting-started.md).
> - **Operators** start at the [Deployment Runbook](operations/deployment-runbook.md).

---

## Contents

- [Choose your path](#choose-your-path)
- [End-user documentation](#end-user-documentation)
- [Product](#product)
- [Architecture](#architecture)
- [Reference](#reference)
- [Developer guides](#developer-guides)
- [Operations runbooks](#operations-runbooks)
- [Security and compliance](#security-and-compliance)
- [Growth, content and support](#growth-content-and-support)
- [Module and feature plans](#module-and-feature-plans)
- [Mobile apps](#mobile-apps)
- [Document status legend](#document-status-legend)
- [Not yet written](#not-yet-written)

---

## Repository layout

All documentation lives under `user-guide/`, grouped by who reads it. Only
`README.md`, `CONTRIBUTING.md`, `SECURITY.md` and `LICENSE` remain at the
repository root, where GitHub expects them.

```
user-guide/
├── README.md          ← you are here
├── customer-guide.md  end-user guides (role based)
├── vendor-guide.md
├── admin-guide.md
├── getting-started.md end-user onboarding
├── faq.md
├── troubleshooting.md
├── architecture/      how the system is built, decisions, tech stack
├── reference/         env vars, API, commands — look-up material
├── guides/            developer how-to: setup, testing, local tooling
├── operations/        deploy, monitoring, backups, queues, runbooks
├── security/          policies, audits, patch log, findings
├── compliance/        GDPR, payments, tax, regional expansion
├── growth/            marketing, SEO, content calendars
├── support/           customer support SOPs and policies
├── product/           PRD, roadmap, backlog, audits
├── modules/           per-feature module documentation
├── plans/             design documents for unbuilt or recent work
├── mobile/            Android and iOS
├── v2/                polished, route-level walkthroughs
└── tools/             the documentation link checker
```

Filenames are `kebab-case.md` throughout.

---

## Choose your path

| I want to… | Go to |
|---|---|
| Buy something on the marketplace | [Customer Guide](customer-guide.md) |
| Sell on the marketplace | [Vendor Guide](vendor-guide.md) |
| Administer the platform | [Admin Guide](admin-guide.md) |
| Run the project locally for the first time | [Getting Started](guides/getting-started.md) |
| Understand how the system is put together | [Architecture Overview](architecture/overview.md) |
| Look up an environment variable | [Configuration Reference](reference/configuration.md) |
| Call the REST API | [API Reference](reference/api.md) |
| Run or write tests | [Testing Guide](guides/testing.md) |
| Ship to production | [Deployment Runbook](operations/deployment-runbook.md) |
| Understand a past technical decision | [Decision Records](architecture/decisions/README.md) |
| Contribute a change | [Contributing](../CONTRIBUTING.md) |
| Report a vulnerability | [Security Policy](../SECURITY.md) |

---

## End-user documentation

Written for customers, vendors, admins and support staff rather than developers.

| Document | What it covers |
|---|---|
| [Customer Guide](customer-guide.md) | Shopping, checkout, orders, returns |
| [Vendor Guide](vendor-guide.md) | Store setup, products, orders, payouts |
| [Admin Guide](admin-guide.md) | Platform administration and moderation |
| [Getting Started](getting-started.md) | First steps for a new account |
| [FAQ](faq.md) | Common questions |
| [Troubleshooting](troubleshooting.md) | Fixing common problems |
| [Payments and Checkout](payments-and-checkout.md) | Payment rails and checkout flow |
| [Subscriptions and Plans](subscriptions-and-plans.md) | Plans, trials and entitlements |
| [Enterprise Custom Domains](enterprise-custom-domains.md) | Domain setup for Enterprise vendors |
| [Documentation v2 Index](v2/README.md) | Route-level walkthroughs and checklists |
| [UI Flows v2](v2/ui-flows.md) | Route-level navigation guidance |
| [Customer Onboarding v2](v2/onboarding-customer.md) | Customer checklist |
| [Vendor Onboarding v2](v2/onboarding-vendor.md) | Vendor checklist |
| [Admin Onboarding v2](v2/onboarding-admin.md) | Admin checklist |

---

## Product

| Document | What it covers |
|---|---|
| [PRD](product/prd.md) | Product requirements, personas, scope |
| [Implementation Plan](product/implementation-plan.md) | Phased delivery roadmap |
| [TODO](product/todo.md) | Task-level development backlog |
| [Roadmap & Enhancement Plan](product/roadmap.md) | Current prioritised plan of work |
| [Documentation Audit](product/documentation-audit.md) | Findings from the docs-vs-code audit |
| [System Upgrade & Enhancement Plan](product/system-upgrade-plan.md) | Longer-horizon platform upgrades |

---

## Architecture

| Document | What it covers |
|---|---|
| [Architecture Overview](architecture/overview.md) | Request lifecycle, layers, module map |
| [Data Model](architecture/data-model.md) | The Eloquent models grouped by domain |
| [Decision Records](architecture/decisions/README.md) | Why the system is the way it is |
| [Tech Stack](architecture/tech-stack.md) | Frameworks, libraries and versions |

---

## Reference

| Document | What it covers |
|---|---|
| [Configuration Reference](reference/configuration.md) | Every environment variable, grouped and explained |
| [API Reference](reference/api.md) | REST endpoints, auth, rate limits, errors |
| [Artisan Commands](reference/commands.md) | Custom console commands and schedules |
| [Dependencies](reference/dependencies.md) | Packages, versions, extensions, security posture |
| [Command Lines](reference/command-lines.md) | Frequently used shell snippets |
| [Demo User Credentials](reference/demo-user-credentials.md) | Seeded accounts for local testing |

---

## Developer guides

| Document | What it covers |
|---|---|
| [Getting Started](guides/getting-started.md) | Local setup from clone to running app |
| [Testing Guide](guides/testing.md) | Running, writing and structuring tests |
| [Contributing](../CONTRIBUTING.md) | Branching, commits, review, definition of done |
| [Setup Guide](guides/setup-guide.md) | Extended environment setup notes |
| [Quickstart](guides/quickstart.md) | Condensed setup path |
| [Localhost on Apache (Windows)](guides/localhost-apache-windows.md) | Windows/Apache specifics |
| [Soketi WebSockets Setup](guides/soketi-websockets-setup.md) | Realtime broadcasting |
| [Generating App Keys & Tokens](guides/generating-keys-and-tokens.md) | Key and token generation |
| [AI Agent Instructions](../.github/agents.md) | Conventions for AI-assisted contributions |

---

## Operations runbooks

| Document | What it covers |
|---|---|
| [Deployment Runbook](operations/deployment-runbook.md) | Production release procedure |
| [Staging Deployment Checklist](operations/staging-deployment-checklist.md) | Pre-production gate |
| [Webhook E2E Verification](operations/webhook-e2e.md) | Deterministic webhook replay checks |
| [Queue Workers & Background Jobs](operations/queue-workers.md) | Worker topology and supervision |
| [Backup Verification Runbook](operations/backup-verification.md) | Proving backups restore |
| [Database Optimization & Cleanup](operations/database-optimization.md) | Index and data hygiene |
| [SSL Certificate Renewal](operations/ssl-certificate-renewal.md) | Certificate rotation |
| [Monitoring & Alerting Dashboards](operations/monitoring-and-alerting.md) | What is watched and how |
| [Performance Monitoring Baseline](operations/performance-baseline.md) | Baseline metrics |
| [Error Tracking & Resolution](operations/error-tracking.md) | Triage workflow |
| [Local Performance & Accessibility Checks](operations/performance-accessibility-audit-2026-03-03.md) | Point-in-time audit |

---

## Security and compliance

| Document | What it covers |
|---|---|
| [Security Policy](../SECURITY.md) | Reporting and disclosure |
| [Security Policies Baseline](security/policies-baseline.md) | Controls the platform commits to |
| [Security Patch Log](security/patch-log.md) | History of applied patches |
| [Security Audit Schedule](security/audit-schedule.md) | Cadence of reviews |
| [Penetration Testing Runbook](security/penetration-testing.md) | Pentest procedure |
| [Bug Bounty Program Policy](security/bug-bounty-policy.md) | Researcher terms |
| [Suspicious Activity Monitoring](security/suspicious-activity-monitoring.md) | Fraud and abuse detection |
| [Pending Closure Matrix](security/pending-closure-matrix.md) | Audit trail of closed items |
| [GDPR Compliance Checklist](compliance/gdpr-checklist.md) | Data protection obligations |
| [Payment Compliance Audit](compliance/payment-compliance-audit.md) | Payment handling review |
| [Regional Compliance Expansion](compliance/regional-expansion.md) | Entering new markets |
| [Tax Regulation Updates](compliance/tax-regulation-updates.md) | Keeping tax rules current |
| [Terms & Policies Review Cadence](compliance/terms-review-cadence.md) | Legal document upkeep |
| [Snyk Rules](../.github/instructions/snyk_rules.instructions.md) | Static analysis conventions |

---

## Growth, content and support

| Document | What it covers |
|---|---|
| [SEO Content Creation Workflow](growth/seo-content-workflow.md) | Publishing pipeline |
| [Blog Content Calendar](growth/blog-content-calendar.md) | Editorial schedule |
| [Social Media Posting Calendar](growth/social-media-calendar.md) | Channel cadence |
| [Email & Newsletter Campaigns](growth/email-campaigns.md) | Lifecycle messaging |
| [Seasonal Campaign Playbook](growth/seasonal-campaigns.md) | Peak-period campaigns |
| [Paid Advertising Campaigns](growth/paid-advertising.md) | Acquisition spend |
| [Influencer Partnerships](growth/influencer-partnerships.md) | Partnership program |
| [Community Engagement Playbook](growth/community-engagement.md) | Community management |
| [Homepage Content Refresh](growth/homepage-content-refresh.md) | Keeping the storefront current |
| [Product Catalog Refresh Schedule](growth/catalog-refresh-schedule.md) | Catalog hygiene |
| [Customer Inquiry Response SOP](support/inquiry-response-sop.md) | Support response standards |
| [Order Issue Handling](support/order-issue-handling.md) | Order exception handling |
| [Refund Request Process](support/refund-request-process.md) | Refund workflow |
| [Review Moderation Policy](support/review-moderation-policy.md) | UGC moderation |
| [Support Team Training](support/team-training.md) | Onboarding support staff |
| [Help Documentation](support/help-documentation.md) | Help-centre source content |
| [FAQ Update Workflow](support/faq-update-workflow.md) | Keeping the FAQ accurate |
| [User Feedback Collection](support/user-feedback-collection.md) | Feedback channels |
| [Feature Usage Analytics Baseline](growth/feature-usage-analytics.md) | What is measured |

---

## Module and feature plans

Design documents for features. Each carries a status banner at the top — read it
before treating the document as a description of what exists. Index:
[plans/README.md](plans/README.md).

| Document | What it covers |
|---|---|
| [Booking System Plan (full)](plans/booking-system.md) | Salon and service booking design |
| [Booking System Plan (summary)](plans/booking-system-summary.md) | Condensed booking plan |
| [Auction Module Plan](plans/auction-module.md) | Auction and bidding design |
| [Configuration Management Plan](plans/configuration-management.md) | Admin-managed configuration design |
| [App Features](modules/app-features.md) | Feature catalogue |
| [Subscription Modules](modules/subscription-modules.md) | Subscription tiers and gating |
| [Modules Implementation Guide](modules/implementation-guide.md) | How modules are built |
| [Premium Modules](modules/premium-modules.md) | Paid module concepts |
| [Starter Module](modules/starter-module.md) | Baseline module template |
| [Swish Payment Integration](modules/swish-payment-integration.md) | Swedish payment rail |
| [Elasticsearch Evaluation](modules/elasticsearch-evaluation.md) | Search engine assessment |
| [Sidebar UI Plan](modules/sidebar-plan.md) | Admin/vendor navigation |
| [Dashboard Sidebar UI Audit](modules/sidebar-ui-audit-2026-03-03.md) | Point-in-time UI audit |
| [Authentication Form](modules/authentication-form/README.md) | Auth screen design |
| [Update Plan](product/update-plan.md) | Historical upgrade notes |

---

## Mobile apps

| Document | What it covers |
|---|---|
| [Mobile App Implementation](mobile/implementation.md) | Android + iOS approach |
| [Mobile App Build Guide](mobile/build-guide.md) | Building releases |
| [Android App Development](mobile/android-development.md) | Android specifics |
| [Android Studio Setup (Windows)](mobile/android-studio-setup.md) | Toolchain setup |
| [iOS App Development](mobile/ios-development.md) | iOS status |
| [Android Module README](../mobile/android/README.md) | Native module overview |

---

## Document status legend

Documents in this repository carry one of these banners:

| Banner | Meaning |
|---|---|
| **Status: Current** | Verified against the codebase; safe to rely on |
| **Status: Plan — not yet implemented** | Design intent. Files it references may not exist |
| **Status: Historical** | Kept for the record; describes a past state |
| **Status: Needs review** | Known to contain claims that have not been re-verified |

A document with no banner has not yet been triaged. See the
[Documentation Audit](product/documentation-audit.md) for the triage backlog.

---

## Not yet written

Tracked gaps, in priority order. See the [Roadmap](product/roadmap.md) for scheduling.

- Vendor payout and ledger reference
- Delivery and logistics domain guide
- Notification and email catalogue
- Frontend/Blade component conventions
- Database index and query performance guide
- Disaster recovery plan

---

*Maintained as part of the repository. When you add a document, link it here in
the same commit — an unlinked document is an unread document.*
