package com.africanmarket.app.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import com.africanmarket.app.BuildConfig
import com.africanmarket.app.data.model.RemoteVersion
import com.africanmarket.app.data.repository.ApiResult
import com.africanmarket.app.data.repository.VersionRepository
import com.africanmarket.app.data.store.TokenDataStore
import com.africanmarket.app.ui.navigation.Screen

@Composable
fun SplashScreen(navController: NavHostController) {
    var update by remember { mutableStateOf<RemoteVersion?>(null) }
    var checked by remember { mutableStateOf(false) }

    LaunchedEffect(Unit) {
        val versionResult = VersionRepository.remoteVersion()
        val rv = (versionResult as? ApiResult.Success)?.data
        if (rv != null && rv.required == true && (rv.versionCode ?: 0) > BuildConfig.VERSION_CODE) {
            update = rv
        }
        checked = true
        if (update == null) {
            val loggedIn = !TokenDataStore.getCachedToken().isNullOrEmpty()
            navController.navigate(if (loggedIn) Screen.Home.route else Screen.Login.route) {
                popUpTo(Screen.Splash.route) { inclusive = true }
            }
        }
    }

    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            Text(
                "Smart African Market",
                fontSize = 22.sp,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.primary,
            )
            Spacer(Modifier.height(16.dp))
            if (!checked || update != null) {
                CircularProgressIndicator(modifier = Modifier.size(36.dp))
            }
        }
    }

    if (update != null) {
        AlertDialog(
            onDismissRequest = {},
            title = { Text("Update required") },
            text = { Text(update?.message ?: "A new version of the app is available.") },
            confirmButton = {
                Button(onClick = {
                    update?.apkUrl?.let { url ->
                        val intent = android.content.Intent(
                            android.content.Intent.ACTION_VIEW,
                            android.net.Uri.parse(url),
                        )
                        navController.context.startActivity(intent)
                    }
                }) { Text("Update now") }
            },
            dismissButton = null,
        )
    }
}
