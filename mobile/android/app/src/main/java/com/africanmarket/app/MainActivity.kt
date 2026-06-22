package com.africanmarket.app

import android.annotation.SuppressLint
import android.app.DownloadManager
import android.content.ActivityNotFoundException
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.os.Environment
import android.webkit.CookieManager
import android.webkit.DownloadListener
import android.webkit.URLUtil
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.activity.result.ActivityResultLauncher
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import com.africanmarket.app.databinding.ActivityMainBinding

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var fileChooserLauncher: ActivityResultLauncher<Array<String>>
    private var filePathCallback: ValueCallback<Array<Uri>>? = null

    private val baseUrl: String by lazy { sanitizeBaseUrl(BuildConfig.WEBSITE_BASE_URL) }
    private val allowedHosts: Set<String> by lazy {
        val configured = BuildConfig.ALLOWED_HOSTS
            .split(",")
            .map { it.trim().lowercase() }
            .filter { it.isNotEmpty() }
            .toMutableSet()
        Uri.parse(baseUrl).host?.lowercase()?.let { configured.add(it) }
        configured
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        configureFileChooser()
        configureWebView(binding.webView)
        configurePullToRefresh()
        configureBackPress()

        if (savedInstanceState == null) {
            binding.webView.loadUrl(baseUrl)
        } else {
            binding.webView.restoreState(savedInstanceState)
        }
    }

    override fun onSaveInstanceState(outState: Bundle) {
        super.onSaveInstanceState(outState)
        binding.webView.saveState(outState)
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun configureWebView(webView: WebView) {
        val settings = webView.settings
        settings.javaScriptEnabled = true
        settings.domStorageEnabled = true
        settings.databaseEnabled = true
        settings.builtInZoomControls = false
        settings.displayZoomControls = false
        settings.cacheMode = WebSettings.LOAD_DEFAULT
        settings.mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
        settings.allowFileAccess = false
        settings.allowContentAccess = true
        settings.loadsImagesAutomatically = true
        settings.userAgentString = settings.userAgentString + " AfricanMarketAndroid/0.1.0"

        CookieManager.getInstance().setAcceptCookie(true)
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true)

        webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val uri = request?.url ?: return false
                if (isAllowedUri(uri)) {
                    return false
                }

                return openExternalLink(uri)
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                binding.swipeRefreshLayout.isRefreshing = false
            }
        }

        webView.webChromeClient = object : WebChromeClient() {
            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                this@MainActivity.filePathCallback?.onReceiveValue(null)
                this@MainActivity.filePathCallback = filePathCallback
                fileChooserLauncher.launch(arrayOf("*/*"))
                return true
            }
        }

        webView.setDownloadListener(
            DownloadListener { url, userAgent, contentDisposition, mimeType, _ ->
                enqueueDownload(url, userAgent, contentDisposition, mimeType)
            }
        )
    }

    private fun configurePullToRefresh() {
        binding.swipeRefreshLayout.setColorSchemeResources(
            R.color.brand_primary,
            R.color.brand_primary_dark
        )
        binding.swipeRefreshLayout.setOnRefreshListener {
            binding.webView.reload()
        }
    }

    private fun configureBackPress() {
        onBackPressedDispatcher.addCallback(this, object : androidx.activity.OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (binding.webView.canGoBack()) {
                    binding.webView.goBack()
                } else {
                    finish()
                }
            }
        })
    }

    private fun configureFileChooser() {
        fileChooserLauncher = registerForActivityResult(ActivityResultContracts.OpenDocument()) { uri ->
            val callback = filePathCallback
            if (callback == null) {
                return@registerForActivityResult
            }

            if (uri != null) {
                callback.onReceiveValue(arrayOf(uri))
            } else {
                callback.onReceiveValue(null)
            }

            filePathCallback = null
        }
    }

    private fun enqueueDownload(
        url: String,
        userAgent: String?,
        contentDisposition: String?,
        mimeType: String?
    ) {
        val request = DownloadManager.Request(Uri.parse(url))
            .setMimeType(mimeType)
            .addRequestHeader("User-Agent", userAgent ?: "")
            .setDescription("Downloading file")
            .setTitle(URLUtil.guessFileName(url, contentDisposition, mimeType))
            .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
            .setDestinationInExternalPublicDir(
                Environment.DIRECTORY_DOWNLOADS,
                URLUtil.guessFileName(url, contentDisposition, mimeType)
            )
            .setAllowedOverMetered(true)
            .setAllowedOverRoaming(false)

        val downloadManager = getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
        downloadManager.enqueue(request)
        Toast.makeText(this, getString(R.string.download_started), Toast.LENGTH_SHORT).show()
    }

    private fun sanitizeBaseUrl(url: String): String {
        val clean = url.trim()
        return if (clean.endsWith("/")) clean.dropLast(1) else clean
    }

    private fun isAllowedUri(uri: Uri): Boolean {
        val host = uri.host?.lowercase() ?: return false
        if (allowedHosts.contains(host)) {
            return true
        }

        return allowedHosts.any { host.endsWith(".$it") }
    }

    private fun openExternalLink(uri: Uri): Boolean {
        return try {
            val intent = Intent(Intent.ACTION_VIEW, uri)
            startActivity(intent)
            true
        } catch (_: ActivityNotFoundException) {
            Toast.makeText(this, getString(R.string.unsupported_link), Toast.LENGTH_SHORT).show()
            true
        }
    }
}
