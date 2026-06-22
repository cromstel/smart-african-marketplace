# iOS App Development (Coming Soon)

This document defines the iOS implementation plan for a future phase.

## Current Status

- iOS app code is intentionally not present in the repository.
- This document is retained as implementation guidance for the future iOS phase.
- Android remains the active mobile implementation track.

## Planned Project Path

- Planned iOS source root: `mobile/ios`
- Planned app shell: SwiftUI + `WKWebView`

## Planned Implementation

- SwiftUI app shell.
- `WKWebView` with:
  - JavaScript enabled.
  - Pull-to-refresh.
  - Back/forward gestures.
  - Allowed-host enforcement.
  - External domain handoff to Safari.
- Build-time configuration:
  - `WEBSITE_BASE_URL`
  - `ALLOWED_HOSTS`

## Configure for Environment (When Development Starts)

Use build settings for:

- `WEBSITE_BASE_URL`
- `ALLOWED_HOSTS`

## Start Criteria

Begin iOS development only when:

1. Product approves iOS phase kickoff.
2. Apple developer account/signing setup is ready.
3. Distribution decision is finalized (TestFlight/App Store).

## Build Prerequisites

- macOS machine with Xcode 15+
- iOS SDK 15+
- Apple Developer account for signed distribution

## Build and Archive (Future)

1. Set signing team and bundle identifier.
2. Build and run on simulator/device.
3. Archive in Xcode for TestFlight/App Store.

## Distribution

Public iOS distribution should use:

- TestFlight (beta)
- App Store (production)

The website QR flow (`/mobile`) remains self-hosted and should link iOS users to Apple distribution channels.

## QA Checklist (iOS Future Phase)

- Login/logout works.
- Product browsing works.
- Cart and checkout flows work.
- File upload flows work in `WKWebView`.
- External payment links open safely in Safari.
