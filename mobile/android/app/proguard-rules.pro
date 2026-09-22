# ---------------------------------------------------------------
# Smart African Market — release shrink/r8 rules
# R8 already applies the AGP default (proguard-android-optimize.txt)
# plus each library's consumer rules. The rules below cover the
# reflection-based libraries (Retrofit / Gson / OkHttp) and keep our
# serializable model classes intact.
# ---------------------------------------------------------------

# Keep annotations + generic signatures (Gson/Retrofit need these)
-keepattributes Signature
-keepattributes *Annotation*
-keepattributes InnerClasses,EnclosingMethod
-keepattributes RuntimeVisibleAnnotations,RuntimeVisibleParameterAnnotations

# Retrofit
-dontwarn retrofit2.**
-keep,allowobfuscation,allowshrinking interface retrofit2.Call
-keep,allowobfuscation,allowshrinking class retrofit2.Response
-keep,allowobfuscation,allowshrinking class retrofit2.http.**

# OkHttp / Okio
-dontwarn okhttp3.**
-dontwarn okio.**
-dontwarn org.conscrypt.**
-dontwarn org.bouncycastle.**

# Gson
-keep class com.google.gson.** { *; }
-dontwarn com.google.gson.**

# Keep app model classes (all fields carry @SerializedName, so wire
# format is stable; keeping them avoids accidental shrink removal).
-keep class com.africanmarket.app.data.model.** { *; }

# DataStore / Preferences
-keep class androidx.datastore.** { *; }
-dontwarn androidx.datastore.**

# Navigation Compose
-keep class androidx.navigation.** { *; }

# Coil
-dontwarn coil.**
-dontwarn coil3.**

# Coroutines
-keepnames class kotlinx.coroutines.internal.MainDispatcherFactory {}
-keepnames class kotlinx.coroutines.CoroutineExceptionHandler {}

# Compose / Material3 (R8 handles most; keep @Composable metadata safe)
-keep class androidx.compose.** { *; }
-dontwarn androidx.compose.**

# Prevent R8 from stripping the app's BuildConfig / manifest entries
-keep class com.africanmarket.app.BuildConfig { *; }
