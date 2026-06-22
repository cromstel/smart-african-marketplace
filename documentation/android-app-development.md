# Android App Development (Phase 1)

This document covers Android implementation. iOS is planned as a future track.

## Current Status

- Android project scaffold created at `mobile/android`.
- Architecture: native Android WebView wrapper for the website app.
- iOS: intentionally not started.

## Project Path

- Android source root: `mobile/android`
- Main app module: `mobile/android/app`
- Main activity: `mobile/android/app/src/main/java/com/africanmarket/app/MainActivity.kt`

## What Is Implemented

- WebView app shell with:
  - JavaScript + DOM storage enabled.
  - Pull-to-refresh.
  - Back navigation handling.
  - External link routing outside allowed hosts.
  - File chooser support for uploads.
  - Download support via Android `DownloadManager`.
- Build-time configuration:
  - `WEBSITE_BASE_URL` in `mobile/android/gradle.properties`
  - `ALLOWED_HOSTS` in `mobile/android/gradle.properties`

## Configure for Environment

Edit `mobile/android/gradle.properties`:

```properties
WEBSITE_BASE_URL=https://your-domain.com
ALLOWED_HOSTS=your-domain.com,www.your-domain.com
```

For production releases, inject these values from CI/CD rather than committing secrets or temporary domains.

## Build Prerequisites

- Android Studio Iguana+ (or compatible with AGP 8.5+)
- Android SDK Platform 35
- JDK 17
- Gradle (via Android Studio or wrapper if generated)

If you are setting up Android Studio now, follow:

- [Android Studio Setup Guide (Windows)](./android-studio-setup.md)

## Build APK (Android Studio)

1. Open `mobile/android` in Android Studio.
2. Sync Gradle.
3. Select `Build > Build Bundle(s) / APK(s) > Build APK(s)`.
4. Output APK path:
   - `mobile/android/app/build/outputs/apk/release/app-release.apk`

## Signed Release (Recommended)

Use Android Studio signed build wizard:

1. `Build > Generate Signed Bundle / APK`.
2. Select `APK`.
3. Configure keystore, alias, passwords.
4. Build `release`.

Do not store keystore files in the repository.

## Self-Hosted Distribution

After signed APK build:

1. Upload APK to website storage, for example:
   - `/public/downloads/android/latest.apk`
2. Publish checksum (`sha256`) in release metadata.
3. Ensure `/mobile` page points Android users to this APK.

## QA Checklist (Android)

- Login/logout works.
- Product browsing works.
- Cart and checkout flows work.
- File uploads (profile/media/forms) work.
- Download links open and save correctly.
- External payment links open outside app where required.

## iOS Relationship

- iOS is currently documented as Coming Soon.
- Android and iOS should remain aligned to the same website base URL and allowed host policy when iOS starts.
