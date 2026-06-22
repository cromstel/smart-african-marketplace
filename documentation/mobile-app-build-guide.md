# Mobile App Build & Release Guide

This document provides complete instructions for building, testing, and releasing the African Market mobile app.

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Project Structure](#2-project-structure)
3. [Configuration](#3-configuration)
4. [Building the App](#4-building-the-app)
5. [Testing](#5-testing)
6. [Release Process](#6-release-process)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. Prerequisites

### Required Software

| Software | Version | Purpose |
|----------|---------|---------|
| Android Studio | 2024.1+ | IDE for building Android apps |
| JDK | 17+ | Java Development Kit |
| Android SDK | API 35 | Android platform tools |
| Gradle | 8.4+ | Build automation (bundled) |

### System Requirements

- Windows 10/11 (64-bit) or macOS/Linux
- Minimum 8 GB RAM (16 GB recommended)
- 15 GB free disk space

---

## 2. Project Structure

```
mobile/
└── android/
    ├── app/
    │   ├── src/
    │   │   ├── main/
    │   │   │   ├── java/com/africanmarket/app/
    │   │   │   │   └── MainActivity.kt          # Main WebView app
    │   │   │   ├── res/
    │   │   │   │   ├── layout/activity_main.xml
    │   │   │   │   ├── mipmap-*/                 # App icons
    │   │   │   │   ├── values/                   # Colors, strings, themes
    │   │   │   │   └── xml/network_security_config.xml
    │   │   └── debug/                           # Debug-specific config
    │   └── build.gradle                         # App-level build config
    ├── build.gradle                             # Root build config
    ├── gradle.properties                       # Build properties (URL config)
    ├── settings.gradle
    └── gradlew / gradlew.bat                    # Gradle wrapper scripts
```

---

## 3. Configuration

### 3.1 Website URL Configuration

Edit `mobile/android/gradle.properties`:

```properties
# Development - Use your network IP for testing
WEBSITE_BASE_URL=http://192.168.100.32:8000
ALLOWED_HOSTS=localhost,127.0.0.1,192.168.100.32

# Production - Uncomment and set when ready for release
# WEBSITE_BASE_URL=https://your-domain.com
# ALLOWED_HOSTS=your-domain.com,www.your-domain.com
```

### 3.2 Understanding the URLs

| URL Type | Value | Use Case |
|----------|-------|----------|
| `10.0.2.2:8000` | Android emulator to host machine | Emulator testing |
| `192.168.x.x:8000` | Local network IP | Physical device testing |
| `your-domain.com` | Production domain | Production release |

### 3.3 Mobile API Endpoints

The app uses these Laravel API endpoints:

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/products` | GET | List products |
| `/api/v1/products/{slug}` | GET | Product details |
| `/api/v1/categories` | GET | List categories |
| `/api/v1/mobile/auth/login` | POST | Mobile login |
| `/api/v1/mobile/auth/me` | GET | Get current user |
| `/api/v1/mobile/auth/logout` | POST | Logout |
| `/api/v1/cart` | GET/POST/PATCH/DELETE | Cart operations |
| `/api/v1/orders` | GET | List orders |
| `/mobile/releases.json` | GET | Release metadata |

---

## 4. Building the App

### 4.1 Opening the Project

1. Open Android Studio
2. Select **File > Open**
3. Navigate to `mobile/android`
4. Wait for Gradle sync to complete

### 4.2 Building Debug APK

**Using Android Studio:**

1. Go to **Build > Build Bundle(s) / APK(s) > Build APK(s)**
2. Wait for build to complete
3. APK will be at: `app/build/outputs/apk/debug/app-debug.apk`

**Using Command Line:**

```bash
cd mobile/android
./gradlew assembleDebug
```

Output: `mobile/android/app/build/outputs/apk/debug/app-debug.apk`

### 4.3 Building Release APK

**Using Android Studio:**

1. Go to **Build > Generate Signed Bundle / APK**
2. Select **APK**
3. Click **Create New** to create a keystore (do this once)
4. Fill in keystore details
5. Set build type to **Release**
6. Click **Finish**

**Using Command Line:**

```bash
cd mobile/android
./gradlew assembleRelease
```

**Important:** For production, you must sign the APK with a keystore. Never commit keystore files to version control.

---

## 5. Testing

### 5.1 Testing Options

| Method | Setup | Use Case |
|--------|-------|----------|
| Android Emulator | AVD in Android Studio | Quick testing |
| Physical Device (USB) | Enable USB debugging | Full testing |
| Physical Device (Network) | Install APK manually | Beta testing |

### 5.2 Testing Checklist

- [ ] App launches and loads website
- [ ] User can log in
- [ ] Products display correctly
- [ ] Cart operations work
- [ ] Checkout flow completes
- [ ] Pull-to-refresh works
- [ ] File uploads work (product images)
- [ ] Back navigation works
- [ ] External links open in browser

### 5.3 Network Testing

For testing on physical devices over network:

1. Ensure your computer and phone are on the same WiFi
2. Find your computer's IP: `ipconfig` (Windows) / `ifconfig` (Mac/Linux)
3. Update `gradle.properties` with your IP
4. Build and install the APK
5. Ensure port 8000 is not blocked by firewall

---

## 6. Release Process

### 6.1 Pre-Release Checklist

- [ ] Test all features on debug APK
- [ ] Verify production URL in gradle.properties
- [ ] Generate signed release APK
- [ ] Test signed APK on physical device
- [ ] Calculate SHA256 checksum

### 6.2 Generating Checksum

```bash
# Windows
Get-FileHash app-release.apk -Algorithm SHA256

# macOS
shasum -a 256 app-release.apk

# Linux
sha256sum app-release.apk
```

### 6.3 Uploading to Server

1. Upload APK to your server:
   ```
   /public/downloads/android/
   ```

2. Update database settings for mobile app:

```sql
INSERT INTO settings (key, value) VALUES 
('mobile_android_version', '1.0.0'),
('mobile_android_build', '1'),
('mobile_android_url', 'https://your-domain.com/downloads/android/app-release.apk'),
('mobile_android_sha256', 'YOUR_SHA256_HERE'),
('mobile_android_min_os', '5.0'),
('mobile_published_at', NOW());
```

### 6.4 Updating Release Metadata

The `/mobile` landing page and `/mobile/releases.json` endpoint read from database settings. Update these settings after uploading a new APK.

---

## 7. Troubleshooting

### Common Issues

#### Issue: Gradle Sync Fails

**Solution:**
1. Check internet connection
2. Go to **File > Sync Project with Gradle Files**
3. If still failing: **File > Invalidate Caches > Invalidate and Restart**

#### Issue: App Shows Blank Page

**Solutions:**
1. Verify `WEBSITE_BASE_URL` is correct
2. Ensure HTTPS certificate is valid (for production)
3. Check that host is in `ALLOWED_HOSTS`
4. Check Android logs for errors

#### Issue: Can't Connect to Local Server

**Solutions:**
1. Use `10.0.2.2:8000` for Android emulator
2. Use your LAN IP (e.g., `192.168.x.x:8000`) for physical devices
3. Ensure firewall allows port 8000

#### Issue: Login Doesn't Work in App

**Solutions:**
1. Verify Sanctum API is working: `php artisan route:list --path=api`
2. Check CORS settings in `config/cors.php`
3. Ensure `SANCTUM_STATEFUL_DOMAINS` includes your app URL

#### Issue: File Uploads Not Working

**Solutions:**
1. Ensure website uses standard HTML file inputs
2. Test on physical device (emulator may have issues)
3. Check file size limits

---

## Security Notes

1. **Never commit** keystore files or passwords
2. **Always use HTTPS** in production
3. **Keep API tokens secure** - don't expose in logs
4. **Validate all inputs** on the server side

---

## Support

For issues or questions:
- Check the main documentation: `docs/`
- Review Android logs in Android Studio
- Check Laravel logs: `storage/logs/`

---

*Last updated: 2026-03-24*
