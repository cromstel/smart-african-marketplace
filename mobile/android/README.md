# Smart African Market Android App

Native Android shell for the Smart African Market website.

## Purpose

- Deliver Android app experience now.
- Reuse existing website/backend flows in a secure WebView wrapper.
- Keep iOS for future phase.

## Configure Base URL

Edit `gradle.properties`:

```properties
WEBSITE_BASE_URL=https://your-domain.com
ALLOWED_HOSTS=your-domain.com,www.your-domain.com
```

## Open in Android Studio

1. Open folder: `mobile/android`
2. Sync Gradle project
3. Run on emulator or device

## Build Release APK

1. `Build > Generate Signed Bundle / APK`
2. Choose `APK`
3. Use production keystore
4. Build `release`

## Notes

- Do not commit keystore files.
- Keep production URL and allowed hosts managed via CI/CD or secure local configuration.
