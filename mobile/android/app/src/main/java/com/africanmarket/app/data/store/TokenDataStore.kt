package com.africanmarket.app.data.store

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.core.PreferenceDataStoreFactory
import androidx.datastore.preferences.preferencesDataStoreFile
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.runBlocking
import java.util.concurrent.atomic.AtomicReference

object TokenDataStore {
    private val KEY_TOKEN = stringPreferencesKey("auth_token")
    private val KEY_USER = stringPreferencesKey("user_json")

    private lateinit var dataStore: DataStore<Preferences>

    // In-memory token cache for the synchronous OkHttp auth interceptor.
    private val cachedToken = AtomicReference<String?>(null)

    fun init(context: Context) {
        if (!::dataStore.isInitialized) {
            dataStore = PreferenceDataStoreFactory.create(
                produceFile = { context.applicationContext.preferencesDataStoreFile("auth_prefs") },
            )
        }
        cachedToken.set(runBlocking { dataStore.data.first()[KEY_TOKEN] })
    }

    fun getCachedToken(): String? = cachedToken.get()

    suspend fun saveToken(token: String, userJson: String) {
        cachedToken.set(token)
        dataStore.edit { prefs ->
            prefs[KEY_TOKEN] = token
            prefs[KEY_USER] = userJson
        }
    }

    suspend fun getUserJson(): String? = dataStore.data.first()[KEY_USER]

    suspend fun clear() {
        cachedToken.set(null)
        dataStore.edit { it.clear() }
    }
}
