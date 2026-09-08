# Documentation Hub

The single entry point for every document in this repository. If you are looking
for something and it is not linked from this page, it is either not written yet
or it belongs in [Not yet written](#not-yet-written).

> **New here?** Developers start at [Getting Started](guides/getting-started.md).
> Customers, vendors and admins start at [User Documentation](../documentation/README.md).

---

## Contents

- [Choose your path](#choose-your-path)
- [Product](#product)
- [Architecture](#architecture)
- [Reference](#reference)
- [Developer guides](#developer-guides)
- [Operations runbooks](#operations-runbooks)
- [Security and compliance](#security-and-compliance)
- [Growth, content and support](#growth-content-and-support)
- [Module and feature plans](#module-and-feature-plans)
- [Mobile apps](#mobile-apps)
- [End-user documentation](#end-user-documentation)
- [Document status legend](#document-status-legend)
- [Not yet written](#not-yet-written)

---

## Choose your path

| I want to… | Go to |
|---|---|
| Run the project locally for the first time | [Getting Started](guides/getting-started.md) |
| Understand how the system is put together | [Architecture Overview](architecture/overview.md) |
| Look up an environment variable | [Configuration Reference](reference/configuration.md) |
| Call the REST API | [API Reference](reference/api.md) |
| Run or write tests | [Testing Guide](guides/testing.md) |
| Ship to production | [Deployment Runbook](DEPLOYMENT_RUNBOOK.md) |
| Understand a past technical decision | [Decision Records](architecture/decisions/README.md) |
| Contribute a change | [Contributing](../CONTRIBUTING.md) |
| Report a vulnerability | [Security Policy](../SECURITY.md) |

---

## Product

| Document | What it covers |
|---|---|
| [PRD](../PRD.md) | Product requirements, personas, scope |
| [Implementation Plan](../IMPLEMENTATION_PLAN.md) | Phased delivery roadmap |
| [TODO](../TODO.md) | Task-level development backlog |
| [Roadmap & Enhancement Plan](ROADMAP.md) | Current prioritised plan of work |
| [Documentation Audit](DOCUMENTATION_AUDIT.md) | Findings from the docs-vs-code audit |
| [System Upgrade & Enhancement Plan](SYSTEM_UPGRADE_ENHANCEMENT_PLAN.md) | Longer-horizon platform upgrades |

---

## Architecture

| Document | What it covers |
|---|---|
| [Architecture Overview](architecture/overview.md) | Request lifecycle, layers, module map |
| [Data Model](architecture/data-model.md) | The 98 Eloquent models grouped by domain |
| [Decision Records](architecture/decisions/README.md) | Why the system is the way it is |
| [Tech Stack](../TECH_STACK.md) | Frameworks, libraries and versions |

---

## Reference

| Document | What it covers |
|---|---|
| [Configuration Reference](reference/configuration.md) | Every environment variable, grouped and explained |
| [API Reference](reference/api.md) | REST endpoints, auth, rate limits, errors |
| [Artisan Commands](reference/commands.md) | Custom console commands and schedules |
| [Command Lines](command-lines.md) | Frequently used shell snippets |
| [Demo User Credentials](demo-user-credentials.md) | Seeded accounts for local testing |

---

## Developer guides

| Document | What it covers |
|---|---|
| [Getting Started](guides/getting-started.md) | Local setup from clone to running app |
| [Testing Guide](guides/testing.md) | Running, writing and structuring tests |
| [Contributing](../CONTRIBUTING.md) | Branching, commits, review, definition of done |
| [Setup Guide](../SETUP_GUIDE.md) | Extended environment setup notes |
| [Quickstart](../QUICKSTART.md) | Condensed setup path |
| [Localhost on Apache (Windows)](../documentation/localhost-apache-windows.md) | Windows/Apache specifics |
| [Soketi WebSockets Setup](../documentation/soketi-websockets-setup.md) | Realtime broadcasting |
| [Generating App Keys & Tokens](../commands/How%20to%20Generate%20App%20Keys%20%26%20Tokens.md) | Key and token generation |
| [AI Agent Instructions](../.github/agents.md) | Conventions for AI-assisted contributions |

---

## Operations runbooks

| Document | What it covers |
|---|---|
| [Deployment Runbook](DEPLOYMENT_RUNBOOK.md) | Production release procedure |
| [Staging Deployment Checklist](STAGING_EXTERNAL_DEPLOYMENT_CHECKLIST.md) | Pre-production gate |
| [Webhook E2E Verification](WEBHOOK_E2E.md) | Deterministic webhook replay checks |
| [Queue Workers & Background Jobs](QUEUE_WORKERS_AND_BACKGROUND_JOBS.md) | Worker topology and supervision |
| [Backup Verification Runbook](BACKUP_VERIFICATION_RUNBOOK.md) | Proving backups restore |
| [Database Optimization & Cleanup](DATABASE_OPTIMIZATION_AND_CLEANUP_RUNBOOK.md) | Index and data hygiene |
| [SSL Certificate Renewal](SSL_CERTIFICATE_RENEWAL_RUNBOOK.md) | Certificate rotation |
| [Monitoring & Alerting Dashboards](MONITORING_AND_ALERTING_DASHBOARDS.md) | What is watched and how |
| [Performance Monitoring Baseline](PERFORMANCE_MONITORING_BASELINE.md) | Baseline metrics |
| [Error Tracking & Resolution](ERROR_TRACKING_AND_RESOLUTION.md) | Triage workflow |
| [Local Performance & Accessibility Checks](LOCAL_PERFORMANCE_ACCESSIBILITY_CHECKS_2026-03-03.md) | Point-in-time audit |

---

## Security and compliance

| Document | What it covers |
|---|---|
| [Security Policy](../SECURITY.md) | Reporting and disclosure |
| [Security Policies Baseline](SECURITY_POLICIES_BASELINE.md) | Controls the platform commits to |
| [Security Patch Log](SECURITY_PATCH_LOG.md) | History of applied patches |
| [Security Audit Schedule](SECURITY_AUDIT_SCHEDULE.md) | Cadence of reviews |
| [Penetration Testing Runbook](PENETRATION_TESTING_RUNBOOK.md) | Pentest procedure |
| [Bug Bounty Program Policy](BUG_BOUNTY_PROGRAM_POLICY.md) | Researcher terms |
| [Suspicious Activity Monitoring](SUSPICIOUS_ACTIVITY_MONITORING_RUNBOOK.md) | Fraud and abuse detection |
| [Pending Closure Matrix](PENDING_CLOSURE_MATRIX.md) | Audit trail of closed items |
| [GDPR Compliance Checklist](GDPR_COMPLIANCE_CHECKLIST.md) | Data protection obligations |
| [Payment Compliance Audit](PAYMENT_COMPLIANCE_AUDIT_RUNBOOK.md) | Payment handling review |
| [Regional Compliance Expansion](REGIONAL_COMPLIANCE_EXPANSION_RUNBOOK.md) | Entering new markets |
| [Tax Regulation Updates](TAX_REGULATION_UPDATE_PROCESS.md) | Keeping tax rules current |
| [Terms & Policies Review Cadence](TERMS_AND_POLICIES_REVIEW_CADENCE.md) | Legal document upkeep |
| [Snyk Rules](../.github/instructions/snyk_rules.instructions.md) | Static analysis conventions |

---

## Growth, content and support

| Document | What it covers |
|---|---|
| [SEO Content Creation Workflow](SEO_CONTENT_CREATION_WORKFLOW.md) | Publishing pipeline |
| [Blog Content Calendar](BLOG_CONTENT_CALENDAR.md) | Editorial schedule |
| [Social Media Posting Calendar](SOCIAL_MEDIA_POSTING_CALENDAR.md) | Channel cadence |
| [Email & Newsletter Campaigns](EMAIL_NEWSLETTER_CAMPAIGNS_PLAYBOOK.md) | Lifecycle messaging |
| [Seasonal Campaign Playbook](SEASONAL_CAMPAIGN_PLAYBOOK.md) | Peak-period campaigns |
| [Paid Advertising Campaigns](PAID_ADVERTISING_CAMPAIGNS_RUNBOOK.md) | Acquisition spend |
| [Influencer Partnerships](INFLUENCER_PARTNERSHIPS_PROGRAM.md) | Partnership program |
| [Community Engagement Playbook](COMMUNITY_ENGAGEMENT_PLAYBOOK.md) | Community management |
| [Homepage Content Refresh](HOMEPAGE_CONTENT_REFRESH_RUNBOOK.md) | Keeping the storefront current |
| [Product Catalog Refresh Schedule](PRODUCT_CATALOG_REFRESH_SCHEDULE.md) | Catalog hygiene |
| [Customer Inquiry Response SOP](CUSTOMER_INQUIRY_RESPONSE_SOP.md) | Support response standards |
| [Order Issue Handling](ORDER_ISSUE_HANDLING_RUNBOOK.md) | Order exception handling |
| [Refund Request Process](REFUND_REQUEST_PROCESS.md) | Refund workflow |
| [Review Moderation Policy](REVIEW_MODERATION_POLICY.md) | UGC moderation |
| [Support Team Training](SUPPORT_TEAM_TRAINING_PROGRAM.md) | Onboarding support staff |
| [Help Documentation](HELP_DOCUMENTATION.md) | Help-centre source content |
| [FAQ Update Workflow](FAQ_UPDATE_WORKFLOW.md) | Keeping the FAQ accurate |
| [User Feedback Collection](USER_FEEDBACK_COLLECTION.md) | Feedback channels |
| [Feature Usage Analytics Baseline](FEATURE_USAGE_ANALYTICS_BASELINE.md) | What is measured |

---

## Module and feature plans

Design documents for features. Each carries a status banner at the top — read it
before treating the document as a description of what exists. Index:
[plans/README.md](../plans/README.md).

| Document | What it covers |
|---|---|
| [Booking System Plan (full)](../plans/comprehensive-booking-system-plan.md) | Salon and service booking design |
| [Booking System Plan (summary)](Booking-System-Implementation-Plan.md) | Condensed booking plan |
| [Auction Module Plan](../plans/auction-module-implementation-plan.md) | Auction and bidding design |
| [Configuration Management Plan](../plans/configuration-management-system.md) | Admin-managed configuration design |
| [App Features](app-modules/App%20Features.md) | Feature catalogue |
| [Subscription Modules](app-modules/app_subscription_modules.md) | Subscription tiers and gating |
| [Modules Implementation Guide](app-modules/modules-implementation-guide.md) | How modules are built |
| [Premium Modules](Premium%20modules.md) | Paid module concepts |
| [Starter Module](starter%20module.md) | Baseline module template |
| [Swish Payment Integration](swish-payment-integration.md) | Swedish payment rail |
| [Elasticsearch Evaluation](ELASTICSEARCH_EVALUATION.md) | Search engine assessment |
| [Sidebar UI Plan](<PLAN-Sticky + Collapsible Sidebars (Admin + Vendor).md>) | Admin/vendor navigation |
| [Dashboard Sidebar UI Audit](DASHBOARD_SIDEBAR_UI_AUDIT_2026-03-03.md) | Point-in-time UI audit |
| [Authentication Form](authentication-form/README.md) | Auth screen design |
| [Update Plan](update%20plan.md) | Historical upgrade notes |

---

## Mobile apps

| Document | What it covers |
|---|---|
| [Mobile App Implementation](../documentation/mobile-app-implementation.md) | Android + iOS approach |
| [Mobile App Build Guide](../documentation/mobile-app-build-guide.md) | Building releases |
| [Android App Development](../documentation/android-app-development.md) | Android specifics |
| [Android Studio Setup (Windows)](../documentation/android-studio-setup.md) | Toolchain setup |
| [iOS App Development](../documentation/ios-app-development.md) | iOS status |
| [Android Module README](../mobile/android/README.md) | Native module overview |
| [Play Store Listing](../mobile/android/PLAY_STORE_LISTING.md) | Store metadata |

---

## End-user documentation

Written for customers, vendors, admins and support staff rather than developers.

- [User Documentation Index](../documentation/README.md)
- [v2 Documentation Index](../documentation/v2/README.md)

---

## Archive

Superseded documents, kept so no context is lost. Do not update them.

| Document | Superseded by |
|---|---|
| [TODO snapshot, 2026-02-22](TODO.backup-20260222-202442.bak.md) | [TODO.md](../TODO.md) and [ROADMAP.md](ROADMAP.md) |

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
[Documentation Audit](DOCUMENTATION_AUDIT.md) for the triage backlog.

---

## Not yet written

Tracked gaps, in priority order. See [ROADMAP](ROADMAP.md) for scheduling.

- Vendor payout and ledger reference
- Delivery and logistics domain guide
- Notification and email catalogue
- Frontend/Blade component conventions
- Database index and query performance guide
- Disaster recovery plan

---

*Maintained as part of the repository. When you add a document, link it here in
the same commit — an unlinked document is an unread document.*
