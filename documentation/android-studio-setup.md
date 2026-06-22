# Android Studio Setup Guide (Windows)

Use this guide if Android Studio is already installed and you want to run/build the Android app in this repository.

## Quick Path (You Already Installed Android Studio)

If Android Studio is already installed, do these steps first:

1. Open Android Studio, then go to `More Actions > SDK Manager`.
2. Install: `Android SDK Platform 35`, `Build-Tools 35.x`, `Platform-Tools`, `Command-line Tools (latest)`, and `Android Emulator`.
3. Create/start an emulator in `More Actions > Virtual Device Manager` (Pixel + Android 14+ image).
4. Open `mobile/android` as a project and wait for Gradle sync.
5. Set `WEBSITE_BASE_URL` and `ALLOWED_HOSTS` in `mobile/android/gradle.properties`.
6. Press `Run` and select your emulator/device.

## 1) Confirm Prerequisites

- Windows 10/11 (64-bit)
- Android Studio (latest stable)
- Minimum free disk space: 15 GB
- Hardware virtualization enabled in BIOS (for emulator performance)

## 2) First-Time Android Studio Configuration

Open Android Studio and complete the setup wizard.

Choose:

- Standard setup (recommended)
- Use bundled JDK unless your team requires a specific JDK path

After setup, open:

- `More Actions > SDK Manager`

Install/confirm:

- `Android SDK Platform 35`
- `Android SDK Build-Tools 35.x`
- `Android SDK Command-line Tools (latest)`
- `Android Emulator`
- `Android SDK Platform-Tools`

Then open:

- `SDK Manager > SDK Tools`

Install/confirm:

- `Android SDK Command-line Tools (latest)`
- `Android Emulator Hypervisor Driver` (or equivalent on your machine)

## 3) Create Emulator (AVD)

Open:

- `More Actions > Virtual Device Manager`

Create device:

1. Device: `Pixel 7` (or similar)
2. System image: Android 14+ (`x86_64` image)
3. Name it: `AfricanMarket_API_34` (example)
4. Finish and start emulator

If emulator is slow:

- Ensure virtualization is enabled
- Close other heavy apps
- Increase RAM in AVD settings (if available)

## 4) Open This Project

In Android Studio:

1. `Open`
2. Select folder: `mobile/android`
3. Wait for Gradle sync to complete

If prompted for trust/indexing, allow both.

## 5) Configure Website URL for App

Edit:

- `mobile/android/gradle.properties`

Set:

```properties
WEBSITE_BASE_URL=https://your-domain.com
ALLOWED_HOSTS=your-domain.com,www.your-domain.com
```

For local testing, use your reachable dev URL (not `localhost` unless emulator can reach it).

## 6) Run App (Debug)

1. Select run target (emulator/device)
2. Build variant: `debug`
3. Click `Run` (green play button)

Expected:

- App opens website in WebView
- You can browse pages
- Pull-to-refresh works

## 7) Build APK

Debug APK:

- `Build > Build Bundle(s) / APK(s) > Build APK(s)`

Release APK (unsigned/signed flow):

- `Build > Generate Signed Bundle / APK`
- Choose `APK`
- Select/create keystore
- Build `release`

## 8) Recommended Android Studio Settings

Open `Settings` and confirm:

- `Build, Execution, Deployment > Build Tools > Gradle`
  - Gradle JDK: Embedded JDK (or approved JDK 17)
- `Editor > Code Style > Kotlin`
  - Keep defaults unless team standards differ
- `Appearance & Behavior > System Settings > Memory Settings`
  - Increase IDE memory if frequent freezes

## 9) Common Issues and Fixes

Gradle sync fails:

- Click `File > Sync Project with Gradle Files`
- Check internet access
- Invalidate cache: `File > Invalidate Caches`

Emulator cannot connect to local backend:

- `localhost` inside emulator points to emulator itself
- Use:
  - `10.0.2.2` for local host mapping (standard emulator)
  - or your LAN IP + open firewall/port

App opens blank page:

- Recheck `WEBSITE_BASE_URL`
- Confirm HTTPS certificate is valid
- Check host included in `ALLOWED_HOSTS`

File upload not working:

- Ensure website input type is standard file input
- Test on physical device if emulator behavior is inconsistent

## 10) Next Steps After First Successful Run

1. Validate login, product browsing, cart, checkout flows.
2. Build signed release APK.
3. Upload APK to your self-hosted download path.
4. Update release metadata used by `/mobile` landing page.
