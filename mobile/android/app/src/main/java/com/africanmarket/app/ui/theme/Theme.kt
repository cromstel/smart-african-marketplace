package com.africanmarket.app.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

private val BrandOrange = Color(0xFFEA580C)
private val BrandOrangeDark = Color(0xFFC2410C)
private val BrandOnPrimary = Color(0xFFFFFFFF)

private val LightColorScheme = lightColorScheme(
    primary = BrandOrange,
    onPrimary = BrandOnPrimary,
    primaryContainer = BrandOrangeDark,
    onPrimaryContainer = BrandOnPrimary,
)

private val DarkColorScheme = darkColorScheme(
    primary = BrandOrange,
    onPrimary = BrandOnPrimary,
    primaryContainer = BrandOrangeDark,
)

@Composable
fun AppTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit,
) {
    MaterialTheme(
        colorScheme = if (darkTheme) DarkColorScheme else LightColorScheme,
        content = content,
    )
}
