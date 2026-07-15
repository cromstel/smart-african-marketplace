package com.africanmarket.app

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import com.africanmarket.app.data.store.TokenDataStore
import com.africanmarket.app.ui.navigation.AppNav
import com.africanmarket.app.ui.theme.AppTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        TokenDataStore.init(applicationContext)
        val startedViaPayment = intent?.data?.scheme == BuildConfig.PAYSTACK_CALLBACK_SCHEME
        setContent {
            AppTheme {
                AppNav(startedViaPayment = startedViaPayment)
            }
        }
    }
}
