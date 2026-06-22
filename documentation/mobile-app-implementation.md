# Mobile App Implementation Plan (Android + iOS)

This document defines how to implement and distribute the mobile app for the current website platform, with self-hosted download links exposed via footer QR codes.

Status: Android implementation active. iOS is planned as a Coming Soon feature.

## 1) Goals

- Provide installable Android and iOS mobile apps for the existing website platform.
- Host build artifacts and metadata on the website infrastructure (self-hosted distribution endpoints).
- Let users scan a footer QR code and reach the correct install flow for their device.
- Keep release, security, and rollback under admin control.

## 2) Scope

In scope:

- Mobile app architecture and deployment design.
- Distribution flow from website footer QR.
- Android APK/AAB distribution strategy.
- iOS distribution strategy and constraints.
- Backend/API requirements for mobile clients.
- Security, signing, and release governance.

Out of scope:

- Publishing production binaries.
- Final App Store assets and submission execution.

## 3) Distribution Model Overview

Single QR entry point:

- Footer QR code points to: `/mobile`
- `/mobile` detects platform and presents:
  - Android: direct APK install flow (self-hosted) and Play Store option (optional).
  - iOS: App Store/TestFlight route (public), or enterprise/internal route where applicable.

Required footer behavior:

- Show one primary QR code (universal link).
- Add fallback text links under QR:
  - `Download for Android`
  - `Download for iPhone`
- Show install instructions if auto-detection fails.

## 4) Platform Constraints (Critical)

### Android

- Self-hosted APK install is supported.
- Users must allow install from unknown sources if not using Play Store.
- Recommended:
  - Keep a signed release APK available at `/downloads/android/latest.apk`
  - Publish checksum and version metadata.

### iOS (Future Implementation)

iOS does not support unrestricted public direct `.ipa` installs from arbitrary websites the same way Android does.

Allowed practical paths:

- Public users:
  - App Store distribution (preferred) or TestFlight during beta.
- Internal enterprise users:
  - Enterprise-signed internal distribution (if organization is eligible and policy-compliant).

Decision note:

- For public audience, use QR -> App Store/TestFlight.
- Keep `/mobile` page self-hosted and controlled by your site, even if final iOS install comes from Apple channels.

## 5) Website Integration Requirements

## 5.1 Footer QR

- Add QR image block in website footer.
- QR target URL: `/mobile`
- Include alt text: `Scan to download mobile app`.

## 5.2 Mobile Landing Route

- Route: `GET /mobile`
- Detect user agent:
  - Android -> show Android card first.
  - iOS -> show iOS card first.
  - Other -> show both.
- Include:
  - Current stable version
  - Release date
  - Changelog link
  - Install instructions
  - Contact support link

## 5.3 Release Metadata Endpoint

- Route: `GET /mobile/releases.json`
- Should include:
  - `android`: version, build, url, sha256, minimum_os
  - `ios`: version, build, app_store_url or testflight_url, minimum_os
  - `published_at`

## 6) Backend/API Readiness for Mobile

- Use existing authenticated API routes (Sanctum tokens).
- Confirm these flows are production-ready for mobile:
  - login/logout
  - profile
  - catalog
  - cart
  - checkout/payment status
  - orders and order history
- Add API versioning discipline:
  - Keep `/api/v1` stable
  - Introduce `/api/v2` only for breaking changes

## 7) Security and Compliance

- Sign Android release builds with production keystore.
- Publish SHA-256 checksum for APK downloads.
- Enforce HTTPS on all download and metadata endpoints.
- Add rate limiting to download endpoints.
- Log download events (platform, version, IP, timestamp).
- Keep private signing keys out of repository and server webroot.

## 8) CI/CD and Release Process

Recommended release flow:

1. Build signed Android release artifact.
2. Upload to self-hosted path (`/downloads/android/...`).
3. Update `releases.json`.
4. Validate QR flow and installation instructions.
5. For iOS:
   - Publish via App Store/TestFlight (or enterprise internal distribution if applicable).
   - Update `releases.json` with Apple link.
6. Announce release notes.

Rollback:

- Keep at least one previous stable Android artifact.
- Roll back `releases.json` pointers if a release is bad.

## 9) UX Flow (User)

1. User scans footer QR code.
2. Website opens `/mobile`.
3. Device is detected:
   - Android: shows `Download APK` and install steps.
   - iOS: shows `Open in App Store/TestFlight` button.
4. User installs app.
5. User signs in with existing website account.

## 10) Required Admin Controls

- Upload/update Android binary.
- Set current stable version.
- Set iOS store/testflight URL.
- Toggle beta vs stable channel links.
- View download analytics (daily installs, versions).

## 11) Phase Plan

Phase 1: Foundation

- Footer QR + `/mobile` page + release metadata endpoint.
- Android self-hosted pipeline.
- iOS store/testflight link management.

Phase 2: Mobile App Build

- Implement app (shared API client, auth, catalog, cart, checkout, orders).
- Internal QA.

Phase 3: Release Hardening

- Analytics, crash reporting, release governance, rollback playbooks.

## 12) Approval Gate (Mandatory)

Before any mobile code development starts, confirm approval on:

- Distribution policy:
  - Android self-hosted APK
  - iOS App Store/TestFlight (or enterprise internal only)
- First release scope (MVP features).
- Branding/assets for mobile store listings.
- Security ownership for signing keys.

Only after this approval should development begin.
