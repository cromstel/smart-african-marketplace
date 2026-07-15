package com.africanmarket.app.util

import com.africanmarket.app.BuildConfig

fun resolveImageUrl(path: String?): String? {
    if (path.isNullOrBlank()) return null
    if (path.startsWith("http://", ignoreCase = true) || path.startsWith("https://", ignoreCase = true)) return path
    val base = BuildConfig.WEBSITE_BASE_URL.trimEnd('/')
    return "$base/$path"
}

fun formatPrice(value: String?): String {
    val amount = value?.toDoubleOrNull() ?: return value ?: ""
    return "%,.2f".format(amount)
}
