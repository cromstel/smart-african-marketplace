<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AiSettingsController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\BookingTypeController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatbotSessionsController;
use App\Http\Controllers\Admin\ChatbotSettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DataExportRequestController;
use App\Http\Controllers\Admin\HeroSectionController;
use App\Http\Controllers\Admin\LoyaltyPointsController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\PageManagementController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\ProductCatalogController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\SalonServiceCategoryController;
use App\Http\Controllers\Admin\SalonServiceController;
use App\Http\Controllers\Admin\SalonStylistController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShippingSettingsController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorLedgerController;
use App\Http\Controllers\Admin\VendorVerificationController;
use App\Http\Controllers\Admin\WidgetController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\BlogCommentController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingTypeController as CustomerBookingTypeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CampaignTrackingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\Customer\LoyaltyController;
use App\Http\Controllers\Customer\ReferralController;
use App\Http\Controllers\DataRequestController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RefundRequestController;
use App\Http\Controllers\ResourceBookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SalonController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Shop\ProductEnquiryController;
use App\Http\Controllers\Shop\VendorFollowController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StylistController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SwishPaymentController;
use App\Http\Controllers\UserSecurityController;
use App\Http\Controllers\Vendor\BookingResourceController;
use App\Http\Controllers\Vendor\MediaController;
use App\Http\Controllers\Vendor\PayoutSettingsController;
use App\Http\Controllers\Vendor\RmaRequestController;
use App\Http\Controllers\Vendor\ShipmentController;
use App\Http\Controllers\Vendor\StoreDomainController;
use App\Http\Controllers\Vendor\ThemeController;
use App\Http\Controllers\Vendor\VendorAvailabilityController;
use App\Http\Controllers\Vendor\VerificationController;
use App\Http\Controllers\Vendor\WalletController;
use App\Http\Controllers\VendorApplicationController;
use App\Http\Controllers\VendorProfileController;
use App\Http\Controllers\VendorStoreController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebVitalsController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::redirect('/welcome', '/')->name('welcome');
Route::get('/healthz', HealthController::class)->name('healthz');

// 301 Redirects for salons -> salon (singular) URL change
Route::redirect('/salons', '/salon', 301)->name('salons.index');
Route::redirect('/salons/{slug}', '/salon/{slug}', 301)->name('salons.show');
Route::redirect('/salons/{service}/book/calendar', '/salon/{service}/book/calendar', 301)->name('salons.book.calendar');
Route::redirect('/salons/{service}/book', '/salon/{service}/book', 301)->name('salons.book');

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-static.xml', [SitemapController::class, 'static'])->name('sitemap.static');
Route::get('/sitemap-products.xml', [SitemapController::class, 'products'])->name('sitemap.products');
Route::get('/sitemap-salon.xml', [SitemapController::class, 'salon'])->name('sitemap.salon');
Route::get('/sitemap-blog.xml', [SitemapController::class, 'blog'])->name('sitemap.blog');
Route::get('/sitemap-auctions.xml', [SitemapController::class, 'auctions'])->name('sitemap.auctions');
Route::get('/sitemap-vendors.xml', [SitemapController::class, 'vendors'])->name('sitemap.vendors');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:public-forms')->name('contact.store');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/vendor-guide', [PageController::class, 'vendorGuide'])->name('vendor.guide');
Route::get('/pages/{page:slug}', [PageController::class, 'customPage'])->name('pages.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{post:slug}/comments', [BlogCommentController::class, 'store'])
    ->middleware('auth', 'throttle:30,1')
    ->name('blog.comments.store');

Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->middleware('throttle:search')->name('search.suggestions');
Route::post('/analytics/web-vitals', [WebVitalsController::class, 'store'])->middleware('throttle:search')->name('analytics.web-vitals');
Route::post('/chatbot/session', [ChatbotController::class, 'session'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.session');
Route::post('/chatbot/message', [ChatbotController::class, 'message'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.message');
Route::get('/chatbot/messages', [ChatbotController::class, 'messages'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.messages');
Route::post('/chatbot/handoff', [ChatbotController::class, 'handoff'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.handoff');
// These are public, token-guarded, and unauthenticated by design (they're
// hit from email clients, not logged-in sessions), but had no rate limit at
// all -- each one does a DB lookup, and open()/unsubscribe() also write, so
// an unthrottled flood (even of a single known/guessed token) is a cheap
// DB-amplification vector. Reusing the `public-forms` limiter (20/min/IP)
// rather than inventing a new one, since it already exists for exactly this
// class of anonymous-public-endpoint abuse (see contact/newsletter below).
Route::get('/campaigns/track/open/{token}', [CampaignTrackingController::class, 'open'])->middleware('throttle:public-forms')->name('campaigns.track.open');
Route::get('/campaigns/track/click/{token}', [CampaignTrackingController::class, 'click'])->middleware('throttle:public-forms')->name('campaigns.track.click');
Route::get('/campaigns/unsubscribe/{token}', [CampaignTrackingController::class, 'unsubscribe'])->middleware('throttle:public-forms')->name('campaigns.unsubscribe');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:public-forms')->name('newsletter.subscribe');
Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe'])->middleware('throttle:public-forms')->name('newsletter.unsubscribe');

Route::get('/vendors/{vendor:slug}/profile', [VendorProfileController::class, 'show'])->name('vendors.show');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/pricing', [SubscriptionController::class, 'index'])->name('subscriptions.index');
Route::get('/pricing/{plan:slug}', [SubscriptionController::class, 'show'])->name('subscriptions.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Authenticated users may browse account pages; sensitive actions (money,
// account mutations, security) require a verified email — added per-route below.
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('verified');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update')->middleware('verified');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('verified');

    // Security routes - Two-Factor Authentication & Session Management
    Route::get('/account/security', [UserSecurityController::class, 'index'])->name('account.security');
    Route::get('/account/security/2fa/setup', [UserSecurityController::class, 'show2faSetup'])->name('account.security.2fa.setup');
    Route::post('/account/security/2fa/enable', [UserSecurityController::class, 'enable2fa'])->name('account.security.2fa.enable')->middleware('throttle:5,1', 'verified');
    Route::post('/account/security/2fa/disable', [UserSecurityController::class, 'disable2fa'])->name('account.security.2fa.disable')->middleware('throttle:5,1', 'verified');
    Route::post('/account/security/2fa/verify', [UserSecurityController::class, 'verify2fa'])->name('account.security.2fa.verify')->middleware('throttle:5,1', 'verified');
    Route::get('/account/security/2fa/recovery-codes', [UserSecurityController::class, 'showRecoveryCodes'])->name('account.security.2fa.recovery-codes');
    Route::post('/account/security/2fa/regenerate-codes', [UserSecurityController::class, 'regenerateRecoveryCodes'])->name('account.security.2fa.regenerate-codes')->middleware('throttle:5,1', 'verified');
    Route::get('/account/security/sessions', [UserSecurityController::class, 'showSessions'])->name('account.security.sessions');
    Route::delete('/account/security/sessions/{sessionId}', [UserSecurityController::class, 'revokeSession'])->name('account.security.sessions.revoke')->middleware('verified');
    Route::delete('/account/security/sessions', [UserSecurityController::class, 'revokeAllSessions'])->name('account.security.sessions.revoke-all')->middleware('verified');

    Route::get('/vendor/apply', [VendorApplicationController::class, 'create'])->name('vendor.apply');
    Route::post('/vendor/apply', [VendorApplicationController::class, 'store'])->name('vendor.apply.store')->middleware('verified');
    Route::get('/vendor/application/status', [VendorApplicationController::class, 'status'])->name('vendor.application.status');
    Route::post('/vendor/application/documents', [VendorApplicationController::class, 'storeDocuments'])->name('vendor.application.documents')->middleware('verified');

    Route::get('/pricing/checkout/{plan:slug}', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe')->middleware('verified');
    Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription'])->name('subscriptions.my');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel')->middleware('verified');
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume')->middleware('verified');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscriptions.success');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics/revenue-data', [AnalyticsController::class, 'revenueData'])->name('analytics.revenue');
    Route::get('/analytics/top-products', [AnalyticsController::class, 'topProducts'])->name('analytics.top-products');
    Route::get('/analytics/category-distribution', [AnalyticsController::class, 'categoryDistribution'])->name('analytics.categories');
    Route::get('/analytics/customer-data', [AnalyticsController::class, 'customerAnalytics'])->name('analytics.customers');
    Route::get('/analytics/traffic-sources', [AnalyticsController::class, 'trafficSources'])->name('analytics.traffic-sources');
    Route::get('/analytics/sales-data', [AnalyticsController::class, 'salesAnalytics'])->name('analytics.sales');
    Route::get('/analytics/product-performance', [AnalyticsController::class, 'productPerformance'])->name('analytics.product-performance');
    Route::get('/analytics/customer-segmentation', [AnalyticsController::class, 'customerSegmentation'])->name('analytics.customer-segmentation');
    Route::get('/analytics/cohort-analysis', [AnalyticsController::class, 'cohortAnalysis'])->name('analytics.cohort-analysis');
    Route::get('/analytics/conversion-funnel', [AnalyticsController::class, 'conversionFunnel'])->name('analytics.conversion-funnel');
    Route::post('/analytics/track-event', [AnalyticsController::class, 'trackEvent'])->middleware('throttle:api')->name('analytics.track-event');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/products/variations', [ProductCatalogController::class, 'variations'])->name('products.variations.overview');
    Route::get('/products/variations/create', [ProductCatalogController::class, 'createVariation'])->name('products.variations.create');
    Route::post('/products/variations', [ProductCatalogController::class, 'storeVariation'])->name('products.variations.store');
    Route::get('/products/brands', [ProductCatalogController::class, 'brands'])->name('products.brands.index');
    Route::get('/products/brands/create', [ProductCatalogController::class, 'createBrand'])->name('products.brands.create');
    Route::post('/products/brands', [ProductCatalogController::class, 'storeBrand'])->name('products.brands.store');
    Route::get('/products/brands/{brand}/edit', [ProductCatalogController::class, 'editBrand'])->name('products.brands.edit');
    Route::put('/products/brands/{brand}', [ProductCatalogController::class, 'updateBrand'])->name('products.brands.update');
    Route::delete('/products/brands/{brand}', [ProductCatalogController::class, 'destroyBrand'])->name('products.brands.destroy');
    Route::get('/products/tags', [ProductCatalogController::class, 'tags'])->name('products.tags.index');
    Route::get('/products/tags/create', [ProductCatalogController::class, 'createTag'])->name('products.tags.create');
    Route::post('/products/tags', [ProductCatalogController::class, 'storeTag'])->name('products.tags.store');
    Route::get('/products/tags/{tag}/edit', [ProductCatalogController::class, 'editTag'])->name('products.tags.edit');
    Route::put('/products/tags/{tag}', [ProductCatalogController::class, 'updateTag'])->name('products.tags.update');
    Route::delete('/products/tags/{tag}', [ProductCatalogController::class, 'destroyTag'])->name('products.tags.destroy');
    Route::get('/products/attributes', [ProductAttributeController::class, 'index'])->name('products.attributes.index');
    Route::get('/products/attributes/create', [ProductAttributeController::class, 'create'])->name('products.attributes.create');
    Route::post('/products/attributes', [ProductAttributeController::class, 'store'])->name('products.attributes.store');
    Route::get('/products/attributes/{attribute}/edit', [ProductAttributeController::class, 'edit'])->name('products.attributes.edit');
    Route::put('/products/attributes/{attribute}', [ProductAttributeController::class, 'update'])->name('products.attributes.update');
    Route::delete('/products/attributes/{attribute}', [ProductAttributeController::class, 'destroy'])->name('products.attributes.destroy');
    Route::get('/products/stock-management', [ProductController::class, 'stockManagement'])->name('products.stock-management');
    Route::get('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::post('/products/import', [ProductController::class, 'processImport'])->name('products.import.process');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::post('/products/export', [ProductController::class, 'processExport'])->name('products.export.process');

    Route::get('/imports', fn () => redirect()->route('admin.products.import'))->name('imports.index');
    Route::get('/exports', fn () => redirect()->route('admin.products.export'))->name('exports.index');
    Route::resource('products', ProductController::class);

    Route::get('/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::put('/reviews/{review}/status', [App\Http\Controllers\Admin\ReviewController::class, 'updateStatus'])->name('reviews.status');
    Route::delete('/reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/bulk', [App\Http\Controllers\Admin\ReviewController::class, 'bulk'])->name('reviews.bulk');
    Route::delete('/product-images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.delete');
    Route::post('/product-images/{image}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
    Route::post('/product-images/{image}/replace', [ProductController::class, 'replaceImage'])->name('products.images.replace');
    Route::get('/products/{product}/variants', [ProductVariantController::class, 'index'])->name('products.variants.index');
    Route::get('/products/{product}/variants/create', [ProductVariantController::class, 'create'])->name('products.variants.create');
    Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::get('/products/{product}/variants/{variant}/edit', [ProductVariantController::class, 'edit'])->name('products.variants.edit');
    Route::put('/products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
    Route::delete('/products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    Route::post('/categories/bulk', [CategoryController::class, 'bulkAction'])->name('categories.bulk');
    // CategoryController has no show() and there is no show.blade.php --
    // categories only get index/edit in this admin, same as the
    // salon-categories and blog-categories resources below. Without this
    // except(), GET /admin/categories/{category} resolved to
    // Route::resource's default show binding and threw "Call to undefined
    // method CategoryController::show()" for every category.
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('orders', AdminOrderController::class);
    Route::put('/orders/{order}/status', [OrderStatusController::class, 'updateStatus'])->name('orders.update-status');
    Route::resource('vendors', VendorController::class);
    Route::post('/vendors/{vendor}/message', [VendorController::class, 'message'])->name('vendors.message');
    Route::get('/vendor-verifications', [VendorVerificationController::class, 'index'])
        ->name('vendor-verifications.index');
    Route::get('/vendor-verifications/{verification}', [VendorVerificationController::class, 'show'])
        ->name('vendor-verifications.show');
    Route::post('/vendor-verifications/{verification}/approve', [VendorVerificationController::class, 'approve'])
        ->name('vendor-verifications.approve');
    Route::post('/vendor-verifications/{verification}/reject', [VendorVerificationController::class, 'reject'])
        ->name('vendor-verifications.reject');
    Route::get('/vendor-verifications/{verification}/documents/{index}', [VendorVerificationController::class, 'document'])
        ->whereNumber('index')
        ->name('vendor-verifications.document');
    Route::get('/vendors/{vendor}/ledger', [VendorLedgerController::class, 'show'])->name('vendors.ledger');
    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class)->except(['show']);
    Route::resource('salon', App\Http\Controllers\Admin\SalonController::class)->except(['show']);
    Route::resource('salon-stylists', SalonStylistController::class)->except(['show']);
    Route::resource('salon-services', SalonServiceController::class)->except(['show']);
    Route::resource('salon-categories', SalonServiceCategoryController::class)->except(['show']);
    Route::post('salon-categories/{salonCategory}/toggle', [SalonServiceCategoryController::class, 'toggle'])->name('salon-categories.toggle');
    // can:bookings.X strings below used to have no matching Gate::define nor
    // a model argument to resolve a Policy against, so Laravel's Gate denied
    // every one of these routes to everyone (admins included) by default.
    // Rewired to can:ability,model / can:ability,ClassName against the now-
    // fixed BookingPolicy. The old index+show group is split: viewAny has no
    // per-instance check, so keeping /bookings/{booking} under it would have
    // skipped ownership entirely once the ability existed (harmless here
    // since BookingPolicy grants admins everything, but not a pattern to
    // repeat for the customer/vendor groups below).
    Route::middleware('can:viewAny,App\Models\Booking')->group(function () {
        Route::get('/bookings/calendar', [App\Http\Controllers\Admin\BookingController::class, 'calendar'])->name('bookings.calendar');
        Route::get('/bookings', [App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    });
    Route::middleware('can:view,booking')->group(function () {
        Route::get('/bookings/{booking}', [App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');
    });
    Route::middleware('can:update,booking')->group(function () {
        Route::put('/bookings/{booking}/status', [App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('bookings.update-status');
        Route::post('/bookings/{booking}/force-confirm', [App\Http\Controllers\Admin\BookingController::class, 'forceConfirm'])->name('bookings.force-confirm');
        Route::post('/bookings/{booking}/resend-reminder', [App\Http\Controllers\Admin\BookingController::class, 'resendReminder'])->name('bookings.resend-reminder');
    });

    // Booking Types (Multi-type booking system) - Admin only
    //
    // The pricing-tiers routes that used to live here (pricing-tiers,
    // pricing-tiers.store/.update/.destroy) referenced BookingTypeController
    // methods that were never implemented, and no data model would have
    // backed them even if they had been: ServicePricingTier belongs to a
    // SalonService (salon_service_id), and salon_services has no
    // booking_type_id column at all -- BookingType::services() was equally
    // broken, referencing a column that doesn't exist. A pricing tier is
    // inherently a per-service day/time price rule, not something that
    // belongs to a booking-type category (Appointment/Rental/...), so
    // removed rather than built on a relationship this app has never had.
    Route::middleware('can:manageTypes,App\Models\Booking')->group(function () {
        Route::resource('booking-types', BookingTypeController::class)->except(['show']);
        Route::post('/booking-types/{bookingType}/toggle', [BookingTypeController::class, 'toggle'])->name('booking-types.toggle');
        Route::post('/booking-types/{bookingType}/reorder', [BookingTypeController::class, 'reorder'])->name('booking-types.reorder');
    });

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-email', [SettingController::class, 'testEmail'])->name('settings.test-email')->middleware('throttle:5,1');
    Route::get('/settings/export', [SettingController::class, 'export'])->name('settings.export')->middleware('throttle:10,1');
    Route::post('/settings/import', [SettingController::class, 'import'])->name('settings.import')->middleware('throttle:5,1');
    Route::get('/settings/audit', [SettingController::class, 'audit'])->name('settings.audit')->middleware('throttle:60,1');
    Route::post('/settings/clear-cache', [SettingController::class, 'clearCache'])->name('settings.clear-cache')->middleware('throttle:10,1');
    Route::get('/shipping-settings', [ShippingSettingsController::class, 'index'])->name('shipping-settings.index');
    Route::put('/shipping-settings', [ShippingSettingsController::class, 'update'])->name('shipping-settings.update');
    Route::post('/shipping-settings/zones', [ShippingSettingsController::class, 'storeZone'])->name('shipping-settings.zones.store');
    Route::put('/shipping-settings/zones/{zone}', [ShippingSettingsController::class, 'updateZone'])->name('shipping-settings.zones.update');
    Route::delete('/shipping-settings/zones/{zone}', [ShippingSettingsController::class, 'destroyZone'])->name('shipping-settings.zones.destroy');
    Route::get('/appearance/theme', [AppearanceController::class, 'theme'])->name('appearance.theme');
    Route::post('/appearance/theme/preset', [AppearanceController::class, 'applyThemePreset'])->name('appearance.theme.preset');
    Route::get('/appearance/customization', [AppearanceController::class, 'customization'])->name('appearance.customization');
    Route::put('/appearance/customization', [AppearanceController::class, 'updateCustomization'])->name('appearance.customization.update');
    Route::post('/appearance/customization/hero', [AppearanceController::class, 'storeHero'])->name('appearance.customization.hero.store');
    Route::put('/appearance/customization/hero/{heroSection}', [AppearanceController::class, 'updateHero'])->name('appearance.customization.hero.update');
    Route::delete('/appearance/customization/hero/{heroSection}', [AppearanceController::class, 'destroyHero'])->name('appearance.customization.hero.destroy');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::post('/users/bulk', [UserManagementController::class, 'bulkAction'])->name('users.bulk');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::match(['put', 'patch'], '/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.status');
    Route::post('/users/{user}/send-reset-link', [UserManagementController::class, 'sendResetLink'])->name('users.send-reset-link');

    Route::get('/chatbot-sessions', [ChatbotSessionsController::class, 'index'])
        ->name('chatbot.sessions.index');
    Route::get('/chatbot-sessions/{session}', [ChatbotSessionsController::class, 'show'])
        ->name('chatbot.sessions.show');
    Route::post('/chatbot-sessions/{session}/claim', [ChatbotSessionsController::class, 'claim'])
        ->name('chatbot.sessions.claim');
    Route::post('/chatbot-sessions/{session}/messages', [ChatbotSessionsController::class, 'store'])
        ->name('chatbot.sessions.messages.store');
    Route::get('/support-tickets', [SupportTicketController::class, 'index'])
        ->name('support-tickets.index');
    Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])
        ->name('support-tickets.show');
    Route::post('/support-tickets/{supportTicket}/messages', [SupportTicketController::class, 'store'])
        ->name('support-tickets.messages.store');
    Route::put('/support-tickets/{supportTicket}/status', [SupportTicketController::class, 'updateStatus'])
        ->name('support-tickets.status.update');

    Route::resource('hero-sections', HeroSectionController::class)->except(['show']);

    Route::post('/pages/bulk', [PageManagementController::class, 'bulkAction'])->name('pages.bulk');
    Route::resource('pages', PageManagementController::class)->except(['show']);

    Route::resource('about', AboutPageController::class)->except(['show']);
    Route::post('/about/{about}/toggle', [AboutPageController::class, 'toggle'])->name('about.toggle');

    Route::resource('announcements', AnnouncementController::class);
    Route::post('/announcements/{announcement}/toggle', [AnnouncementController::class, 'toggle'])->name('announcements.toggle');
    Route::post('/announcements/{announcement}/toggle-pin', [AnnouncementController::class, 'togglePin'])->name('announcements.togglePin');

    Route::get('/media', [MediaLibraryController::class, 'index'])->name('media.index');
    Route::get('/media/create', [MediaLibraryController::class, 'create'])->name('media.create');
    Route::get('/media/settings', [MediaLibraryController::class, 'settings'])->name('media.settings');
    Route::put('/media/settings', [MediaLibraryController::class, 'updateSettings'])->name('media.settings.update');
    Route::get('/media/{media}', [MediaLibraryController::class, 'show'])->name('media.show');
    Route::get('/media/{media}/edit', [MediaLibraryController::class, 'edit'])->name('media.edit');
    Route::put('/media/{media}', [MediaLibraryController::class, 'update'])->name('media.update');
    Route::delete('/media/{media}', [MediaLibraryController::class, 'destroy'])->name('media.destroy');
    Route::post('/media/upload', [MediaLibraryController::class, 'upload'])->name('media.upload');
    Route::post('/media/folders', [MediaLibraryController::class, 'storeFolder'])->name('media.folders.store');
    Route::post('/media/bulk-delete', [MediaLibraryController::class, 'bulkDelete'])->name('media.bulk-delete');
    Route::post('/media/{media}/regenerate-alt', [MediaLibraryController::class, 'regenerateAlt'])->name('media.regenerate-alt');
    Route::post('/media/{media}/replace', [MediaLibraryController::class, 'replace'])->name('media.replace');
    Route::post('/media/bulk-regenerate-alt', [MediaLibraryController::class, 'bulkRegenerateAlt'])->name('media.bulk-regenerate-alt');

    Route::get('/blog/{blog}/preview', [BlogPostController::class, 'preview'])->name('blog.preview');
    Route::post('/blog/preview-draft', [BlogPostController::class, 'previewDraft'])->name('blog.preview-draft');
    Route::post('/blog/inline-category', [BlogPostController::class, 'storeInlineCategory'])->name('blog.inline-category.store');
    Route::post('/blog/inline-tag', [BlogPostController::class, 'storeInlineTag'])->name('blog.inline-tag.store');
    Route::post('/blog/upload-content-image', [BlogPostController::class, 'uploadContentImage'])->name('blog.content-image.upload');
    Route::post('/blog/generate-content', [BlogPostController::class, 'generateContent'])->name('blog.generate-content');
    Route::post('/blog/{blog}/revisions/{revision}/restore', [BlogPostController::class, 'restoreRevision'])->name('blog.revisions.restore');
    Route::resource('blog', BlogPostController::class)->except(['show']);
    Route::resource('blog-categories', BlogCategoryController::class)->except(['show'])->names('blog.categories');
    Route::resource('blog-tags', BlogTagController::class)->except(['show'])->names('blog.tags');

    Route::get('/loyalty', [LoyaltyPointsController::class, 'index'])->name('loyalty.index');
    Route::get('/loyalty/settings', [LoyaltyPointsController::class, 'settings'])->name('loyalty.settings');
    Route::put('/loyalty/settings', [LoyaltyPointsController::class, 'updateSettings'])->name('loyalty.settings.update');
    Route::post('/loyalty/adjust', [LoyaltyPointsController::class, 'manualAdjust'])->name('loyalty.adjust');

    Route::get('/referrals', [App\Http\Controllers\Admin\ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/referrals/settings', [App\Http\Controllers\Admin\ReferralController::class, 'settings'])->name('referrals.settings');
    Route::put('/referrals/settings', [App\Http\Controllers\Admin\ReferralController::class, 'updateSettings'])->name('referrals.settings.update');
    Route::post('/referrals/{referral}/approve', [App\Http\Controllers\Admin\ReferralController::class, 'approve'])->name('referrals.approve');
    Route::post('/referrals/{referral}/reject', [App\Http\Controllers\Admin\ReferralController::class, 'reject'])->name('referrals.reject');

    Route::resource('subscriptions/plans', SubscriptionPlanController::class)->except(['show'])->names('subscriptions.plans');
    Route::post('/subscriptions/plans/{plan}/toggle', [SubscriptionPlanController::class, 'toggle'])->name('subscriptions.plans.toggle');
    Route::get('/subscriptions/subscribers', [SubscriptionPlanController::class, 'subscribers'])->name('subscriptions.subscribers');

    Route::get('/ai-settings', [AiSettingsController::class, 'index'])->name('ai-settings.index');
    Route::put('/ai-settings', [AiSettingsController::class, 'update'])->name('ai-settings.update');
    Route::get('/chatbot-settings', [ChatbotSettingsController::class, 'index'])->name('chatbot.settings.index');
    Route::put('/chatbot-settings', [ChatbotSettingsController::class, 'update'])->name('chatbot.settings.update');

    Route::get('/widgets', [WidgetController::class, 'index'])->name('widgets.index');
    Route::get('/widgets/preview', [WidgetController::class, 'preview'])->name('widgets.preview');
    Route::get('/widgets/create', [WidgetController::class, 'create'])->name('widgets.create');
    Route::post('/widgets', [WidgetController::class, 'store'])->name('widgets.store');
    Route::get('/widgets/{widget}/edit', [WidgetController::class, 'edit'])->name('widgets.edit');
    Route::match(['put', 'patch'], '/widgets/{widget}', [WidgetController::class, 'update'])->name('widgets.update');
    Route::delete('/widgets/{widget}', [WidgetController::class, 'destroy'])->name('widgets.destroy');
    Route::post('/widgets/bulk', [WidgetController::class, 'bulkAction'])->name('widgets.bulk');
    Route::post('/widgets/reorder', [WidgetController::class, 'reorder'])->name('widgets.reorder');

    Route::get('/data-exports', [DataExportRequestController::class, 'index'])->name('data-exports.index');
    Route::get('/data-exports/{dataExportRequest}', [DataExportRequestController::class, 'show'])->name('data-exports.show');
    Route::post('/data-exports/{dataExportRequest}/approve', [DataExportRequestController::class, 'approve'])->name('data-exports.approve');
    Route::post('/data-exports/{dataExportRequest}/reject', [DataExportRequestController::class, 'reject'])->name('data-exports.reject');
    Route::get('/data-exports/{dataExportRequest}/download', [DataExportRequestController::class, 'download'])->name('data-exports.download');

    Route::resource('campaigns', CampaignController::class);
    Route::post('/campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');

    Route::get('/refunds', [App\Http\Controllers\Admin\RefundRequestController::class, 'index'])->name('refunds.index');
    Route::get('/refunds/{refund}', [App\Http\Controllers\Admin\RefundRequestController::class, 'show'])->name('refunds.show');
    Route::post('/refunds/{refund}/approve', [App\Http\Controllers\Admin\RefundRequestController::class, 'approve'])->name('refunds.approve');
    Route::post('/refunds/{refund}/reject', [App\Http\Controllers\Admin\RefundRequestController::class, 'reject'])->name('refunds.reject');
    Route::post('/refunds/{refund}/refunded', [App\Http\Controllers\Admin\RefundRequestController::class, 'markRefunded'])->name('refunds.refunded');

    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::get('/payouts/{payout}', [PayoutController::class, 'show'])->name('payouts.show');
    Route::post('/payouts/{payout}/process', [PayoutController::class, 'process'])->name('payouts.process');
    Route::post('/payouts/{payout}/fail', [PayoutController::class, 'fail'])->name('payouts.fail');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/system-health', [SystemHealthController::class, 'index'])->name('system-health.index');

    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/complete', [WithdrawalController::class, 'complete'])->name('withdrawals.complete');
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Auction management
    Route::resource('auctions', App\Http\Controllers\Admin\AuctionController::class);
    Route::post('/auctions/{auction}/feature', [App\Http\Controllers\Admin\AuctionController::class, 'toggleFeature'])->name('auctions.feature');
    Route::post('/auctions/{auction}/settle', [App\Http\Controllers\Admin\AuctionController::class, 'settle'])->name('auctions.settle');
    Route::get('/auctions/reports', [App\Http\Controllers\Admin\AuctionController::class, 'reports'])->name('auctions.reports');

    // Maintenance Mode
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance/update', [MaintenanceController::class, 'update'])->name('maintenance.update');
    Route::post('/maintenance/enable', [MaintenanceController::class, 'enable'])->name('maintenance.enable');
    Route::post('/maintenance/disable', [MaintenanceController::class, 'disable'])->name('maintenance.disable');
    Route::post('/maintenance/toggle', [MaintenanceController::class, 'toggle'])->name('maintenance.toggle');
    Route::get('/maintenance/status', [MaintenanceController::class, 'status'])->name('maintenance.status');
    // Never wired up at all -- preview() existed and (once fixed) is safe
    // to expose: it now renders the visitor-facing view directly with no
    // side effects, rather than the destructive enable()-with-no-restore
    // it used to call.
    Route::get('/maintenance/preview', [MaintenanceController::class, 'preview'])->name('maintenance.preview');
});

Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/vendors/{vendor:slug}', [VendorStoreController::class, 'show'])->name('vendor.store');

Route::get('/wishlist/shared/{token}', [WishlistController::class, 'shared'])->name('wishlist.shared');

Route::get('/salon', [SalonController::class, 'index'])->name('salon.index');
Route::get('/salon/{service:slug}', [SalonController::class, 'show'])->name('salon.show');
Route::get('/stylists', [StylistController::class, 'index'])->name('stylists.index');
Route::get('/stylists/{stylist}', [StylistController::class, 'show'])->name('stylists.show');

// Booking types: the customer-facing "start a booking" entry point --
// pick a type, then see what's actually bookable under it. See
// BookingTypeController's own docblock for why only appointment/
// consultation/rental render real listings.
Route::get('/book', [CustomerBookingTypeController::class, 'index'])->name('booking-types.index');
Route::get('/book/{bookingType:slug}', [CustomerBookingTypeController::class, 'show'])->name('booking-types.show');

// Auction routes
Route::get('/auctions', [AuctionController::class, 'index'])->name('auctions.index');
Route::get('/auctions/featured', [AuctionController::class, 'featured'])->name('auctions.featured');
Route::get('/auctions/ending-soon', [AuctionController::class, 'endingSoon'])->name('auctions.ending-soon');
Route::get('/auctions/search', [AuctionController::class, 'search'])->name('auctions.search');
Route::get('/auctions/{auction:slug}', [AuctionController::class, 'show'])->name('auctions.show');

// Auction bidding (requires authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/auctions/{auction:slug}/bid', [AuctionController::class, 'bid'])->middleware('throttle:60,1')->name('auctions.bid');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store')->middleware('verified');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update')->middleware('verified');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy')->middleware('verified');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/shipping-preview', [CheckoutController::class, 'shippingPreview'])->name('checkout.shipping-preview');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('verified');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::resource('addresses', AddressController::class);

    Route::get('/payment/stripe/{order}', [PaymentController::class, 'stripe'])->name('payment.stripe');
    Route::post('/payment/stripe/{order}', [PaymentController::class, 'stripeProcess'])->name('payment.stripe.process')->middleware('verified');
    Route::get('/payment/paystack/{order}', [PaymentController::class, 'paystack'])->name('payment.paystack');
    Route::get('/payment/paystack/{order}/callback', [PaymentController::class, 'paystackCallback'])->name('payment.paystack.callback')->middleware('verified');
    Route::get('/payment/momo/{order}', [PaymentController::class, 'momo'])->name('payment.momo');
    Route::post('/payment/momo/{order}', [PaymentController::class, 'momoProcess'])->name('payment.momo.process')->middleware('verified');
    Route::get('/payment/swish/{order}', [SwishPaymentController::class, 'show'])->name('payment.swish');
    Route::post('/payment/swish/{order}', [SwishPaymentController::class, 'process'])->name('payment.swish.process')->middleware('verified');
    Route::get('/payment/swish/{order}/status', [SwishPaymentController::class, 'status'])->name('payment.swish.status');
    Route::get('/payment/success/{order}', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed/{order}', [PaymentController::class, 'failed'])->name('payment.failed');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store')->middleware('verified');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy')->middleware('verified');
    Route::post('/reviews/{review}/helpful', [ReviewController::class, 'toggleHelpful'])->name('reviews.helpful')->middleware('verified');
    Route::get('/account/reviews', [ReviewController::class, 'myReviews'])->name('account.reviews');
    Route::get('/account/notifications', [NotificationController::class, 'index'])->name('account.notifications');
    Route::post('/account/notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('account.notifications.markRead')->middleware('verified');
    Route::post('/account/notifications/mark-all-read', [NotificationController::class, 'markAll'])->name('account.notifications.markAll')->middleware('verified');
    Route::post('/account/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('account.notifications.preferences')->middleware('verified');
    Route::post('/account/notifications/push-token', [NotificationController::class, 'updatePushToken'])->name('account.notifications.push-token')->middleware('verified');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store')->middleware('verified');
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy')->middleware('verified');
    Route::post('/wishlist/share', [WishlistController::class, 'share'])->name('wishlist.share')->middleware('verified');
    Route::post('/wishlist/{wishlist}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.move')->middleware('verified');

    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply')->middleware('verified');
    Route::delete('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove')->middleware('verified');

    // Booking routes (customer-facing: browsing/creating/managing YOUR OWN
    // bookings) - authorization is BookingPolicy alone (viewAny/view/create/
    // update/cancel), which already grants admins, customers, and vendors
    // (scoped to their own salon_owner_id) appropriately -- see
    // BookingPolicy's class doc comments. This group used to also be wrapped
    // in module.access:bookings, a vendor-subscription-plan gate. That's the
    // right gate for the VENDOR booking-management routes under
    // vendor.bookings.* (still gated, see the /vendor prefix group below),
    // but here it ran ahead of BookingPolicy and rejected every non-admin,
    // non-subscribed user -- i.e. every ordinary customer, since customer
    // accounts never hold a vendor subscription -- making it impossible for
    // any customer to view, create, reschedule, or cancel a booking at all.
    // Removed so BookingPolicy is the sole authorization layer here, as the
    // policy's own logic already assumes.
    Route::group([], function () {
        Route::middleware('can:viewAny,App\Models\Booking')->group(function () {
            Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        });
        Route::middleware('can:view,booking')->group(function () {
            Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        });
        Route::middleware('can:update,booking')->group(function () {
            Route::get('/bookings/{booking}/reschedule', [BookingController::class, 'edit'])->name('bookings.reschedule');
            Route::put('/bookings/{booking}/reschedule', [BookingController::class, 'update'])->name('bookings.update')->middleware('verified');
        });
        Route::middleware('can:cancel,booking')->group(function () {
            Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel')->middleware('verified');
        });
        Route::middleware('can:create,App\Models\Booking')->group(function () {
            Route::get('/salon/{service}/book/calendar', [BookingController::class, 'calendarPreview'])->name('bookings.calendar');
            Route::get('/salon/{service}/book', [BookingController::class, 'create'])->name('bookings.create');
            Route::post('/salon/{service}/book', [BookingController::class, 'store'])->name('bookings.store')->middleware('verified');
            Route::get('/resources/{resource}/book', [ResourceBookingController::class, 'create'])->name('resource-bookings.create');
            Route::post('/resources/{resource}/book', [ResourceBookingController::class, 'store'])->name('resource-bookings.store')->middleware('verified');
        });
        Route::get('/api/salon/{service}/available-slots', [BookingController::class, 'getServiceSlots'])->name('bookings.available-slots');
    });

    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::post('/loyalty/redeem', [LoyaltyController::class, 'redeem'])->name('loyalty.redeem')->middleware('verified');
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');

    Route::post('/vendors/{vendor}/follow', [VendorFollowController::class, 'toggle'])->name('vendors.follow')->middleware('verified');
    Route::get('/following', [VendorFollowController::class, 'following'])->name('following.index');

    Route::post('/products/{product}/enquiries', [ProductEnquiryController::class, 'store'])->name('products.enquiries.store')->middleware('verified');

    Route::post('/orders/{order}/refunds', [RefundRequestController::class, 'store'])->name('orders.refunds.store')->middleware('verified');
    Route::post('/privacy/delete', [DataRequestController::class, 'delete'])->name('privacy.delete')->middleware('verified');
});

Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'vendor', 'verified'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [App\Http\Controllers\Vendor\AnalyticsController::class, 'index'])->name('analytics.index');

    Route::get('/data-export/request', [App\Http\Controllers\Vendor\DataExportRequestController::class, 'create'])
        ->name('data-export.request');
    Route::post('/data-export/request', [App\Http\Controllers\Vendor\DataExportRequestController::class, 'store'])
        ->name('data-export.store');
    Route::get('/data-export/{dataExportRequest}/download', [App\Http\Controllers\Vendor\DataExportRequestController::class, 'download'])
        ->name('data-export.download');

    // See the identical note on the admin/customer booking routes: these
    // can:bookings.X strings had no Gate::define and no model argument, so
    // Gate denied every one by default, regardless of BookingController's
    // own ensureOwnership() check further down the request lifecycle.
    Route::middleware('module.access:bookings')->group(function () {
        Route::middleware('can:viewAny,App\Models\Booking')->group(function () {
            Route::get('/bookings', [App\Http\Controllers\Vendor\BookingController::class, 'index'])->name('bookings.index');
        });
        Route::middleware('can:view,booking')->group(function () {
            Route::get('/bookings/{booking}', [App\Http\Controllers\Vendor\BookingController::class, 'show'])->name('bookings.show');
        });
        Route::middleware('can:confirm,booking')->group(function () {
            Route::post('/bookings/{booking}/confirm', [App\Http\Controllers\Vendor\BookingController::class, 'confirm'])->name('bookings.confirm');
        });
        Route::middleware('can:cancel,booking')->group(function () {
            Route::post('/bookings/{booking}/cancel', [App\Http\Controllers\Vendor\BookingController::class, 'cancel'])->name('bookings.cancel');
        });
        Route::post('/bookings/{booking}/message', [App\Http\Controllers\Vendor\BookingController::class, 'message'])->name('bookings.message');
    });

    // Booking Resources (for rentals and inventory management)
    Route::middleware('module.access:bookings')->group(function () {
        Route::middleware('can:bookings.manage_resources')->group(function () {
            // `show` used to be excepted because its view didn't exist yet
            // (resources/views/vendor/bookings/resources/*.blade.php --
            // see BookingResourceIsolationTest's docblock); now that the
            // views are built, the full resource set (including show) is
            // wired up. `toggle` likewise had a working controller method
            // but no route.
            Route::resource('booking-resources', BookingResourceController::class);
            Route::post('/booking-resources/{bookingResource}/toggle', [BookingResourceController::class, 'toggle'])->name('booking-resources.toggle');
            Route::get('/booking-resources/{bookingResource}/availability', [BookingResourceController::class, 'availability'])->name('booking-resources.availability');
            Route::get('/booking-resources/{bookingResource}/statistics', [BookingResourceController::class, 'statistics'])->name('booking-resources.statistics');
            // `bulk-availability` (POST -> bulkAvailability()) was removed here:
            // the controller method never existed (fatal error if hit), no view
            // or JS anywhere referenced it, and there's no backing schema for
            // it -- availability is derived entirely from the `bookings` table,
            // with no `resource_availability`/blocked-dates table to bulk-write
            // into. Rebuilding it as a real feature needs a product decision
            // (what "bulk availability" should persist) before a schema is
            // designed, so it isn't reinstated speculatively.
        });
    });

    Route::get('/settings/availability', [VendorAvailabilityController::class, 'index'])
        ->name('settings.availability.index');
    Route::post('/settings/availability', [VendorAvailabilityController::class, 'store'])
        ->name('settings.availability.store');
    Route::put('/settings/availability/{availability}', [VendorAvailabilityController::class, 'update'])
        ->name('settings.availability.update');
    Route::delete('/settings/availability/{availability}', [VendorAvailabilityController::class, 'destroy'])
        ->name('settings.availability.destroy');

    Route::middleware('module.access:multiple_product_category')->group(function () {
        // subscription.limits:products only blocks the plain POST /products
        // (create/store) action -- it lets any route through that carries a
        // {product} route parameter (edit/update/variants/images), so it is
        // scoped to just the resource route rather than this whole group.
        Route::resource('products', App\Http\Controllers\Vendor\ProductController::class)
            ->except(['show'])
            ->middleware('subscription.limits:products');
        Route::get('/categories', [App\Http\Controllers\Vendor\CategoryController::class, 'index'])->name('categories.index');
        Route::get('/inventory', [App\Http\Controllers\Vendor\ProductController::class, 'inventory'])->name('inventory.index');
        Route::get('/variations', [App\Http\Controllers\Vendor\ProductVariantController::class, 'all'])->name('variations.index');
        Route::delete('/product-images/{image}', [App\Http\Controllers\Vendor\ProductController::class, 'deleteImage'])->name('products.images.delete');
        Route::post('/product-images/{image}/primary', [App\Http\Controllers\Vendor\ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        Route::post('/product-images/{image}/replace', [App\Http\Controllers\Vendor\ProductController::class, 'replaceImage'])->name('products.images.replace');

        Route::get('/products/{product}/variants', [App\Http\Controllers\Vendor\ProductVariantController::class, 'index'])->name('products.variants.index');
        Route::get('/products/{product}/variants/create', [App\Http\Controllers\Vendor\ProductVariantController::class, 'create'])->name('products.variants.create');
        Route::post('/products/{product}/variants', [App\Http\Controllers\Vendor\ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::get('/products/{product}/variants/{variant}/edit', [App\Http\Controllers\Vendor\ProductVariantController::class, 'edit'])->name('products.variants.edit');
        Route::put('/products/{product}/variants/{variant}', [App\Http\Controllers\Vendor\ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/products/{product}/variants/{variant}', [App\Http\Controllers\Vendor\ProductVariantController::class, 'destroy'])->name('products.variants.destroy');

        Route::resource('attributes', App\Http\Controllers\Vendor\ProductAttributeController::class)->except(['show']);
        Route::get('/reviews', [App\Http\Controllers\Vendor\ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [App\Http\Controllers\Vendor\ReviewController::class, 'show'])->name('reviews.show');
    });

    Route::resource('salon', App\Http\Controllers\Vendor\SalonController::class)->except(['show']);
    Route::resource('salon-stylists', App\Http\Controllers\Vendor\SalonStylistController::class)->except(['show']);
    Route::resource('salon-services', App\Http\Controllers\Vendor\SalonServiceController::class)->except(['show']);
    Route::post('/products/generate-content', [App\Http\Controllers\Vendor\ProductController::class, 'generateContent'])
        ->middleware(['module.access:multiple_product_category', 'module.access:automate_workload_with_ai'])
        ->name('products.generate-content');

    Route::resource('brands', App\Http\Controllers\Vendor\BrandController::class)
        ->except(['show'])
        ->middleware('module.access:product_brands_sync');

    Route::get('/subscription', [App\Http\Controllers\Vendor\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/upgrade', [App\Http\Controllers\Vendor\SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    Route::post('/subscription/upgrade', [App\Http\Controllers\Vendor\SubscriptionController::class, 'processUpgrade'])->name('subscription.process');

    Route::middleware('module.access:vendor_dashboard_announcements')->group(function () {
        Route::get('/announcements', [App\Http\Controllers\Vendor\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/{announcement}', [App\Http\Controllers\Vendor\AnnouncementController::class, 'show'])->name('announcements.show');
        Route::post('/announcements/{announcement}/read', [App\Http\Controllers\Vendor\AnnouncementController::class, 'markAsRead'])->name('announcements.read');
        Route::post('/announcements/mark-all-read', [App\Http\Controllers\Vendor\AnnouncementController::class, 'markAllAsRead'])->name('announcements.markAllAsRead');
    });

    // /orders/create must be registered before the /orders/{order} wildcard
    // below -- Laravel matches routes in registration order, so with the
    // wildcard first, "GET /orders/create" was matching /orders/{order}
    // instead (Order::findOrFail('create')), a 404 every time. Manual order
    // creation was therefore unreachable regardless of subscription limits.
    //
    // subscription.limits:orders has no HTTP-method guard internally (unlike
    // the products check), so it must only ever sit on these two
    // order-creation routes -- never on orders.index/show/updateStatus/
    // addNote below, or a vendor at their monthly order limit would be
    // locked out of viewing and managing orders they already have.
    Route::get('/orders/create', [App\Http\Controllers\Vendor\OrderController::class, 'create'])
        ->middleware(['module.access:order_management', 'module.access:manual_order_creation', 'subscription.limits:orders'])
        ->name('orders.create');
    Route::post('/orders', [App\Http\Controllers\Vendor\OrderController::class, 'store'])
        ->middleware(['module.access:order_management', 'module.access:manual_order_creation', 'subscription.limits:orders'])
        ->name('orders.store');

    Route::middleware('module.access:order_management')->group(function () {
        Route::get('/orders', [App\Http\Controllers\Vendor\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\Vendor\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [App\Http\Controllers\Vendor\OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders/{order}/notes', [App\Http\Controllers\Vendor\OrderController::class, 'addNote'])->name('orders.addNote');
    });

    Route::get('/refunds', [App\Http\Controllers\Vendor\RefundRequestController::class, 'index'])->name('refunds.index');
    Route::get('/refunds/{refund}', [App\Http\Controllers\Vendor\RefundRequestController::class, 'show'])->name('refunds.show');
    Route::post('/refunds/{refund}/approve', [App\Http\Controllers\Vendor\RefundRequestController::class, 'approve'])->name('refunds.approve');
    Route::post('/refunds/{refund}/reject', [App\Http\Controllers\Vendor\RefundRequestController::class, 'reject'])->name('refunds.reject');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/payouts', [App\Http\Controllers\Vendor\PayoutController::class, 'index'])->name('payouts.index');
    Route::get('/payouts/create', [App\Http\Controllers\Vendor\PayoutController::class, 'create'])->name('payouts.create');
    Route::post('/payouts', [App\Http\Controllers\Vendor\PayoutController::class, 'store'])->name('payouts.store');
    Route::get('/settings/payouts', [PayoutSettingsController::class, 'edit'])->name('settings.payouts.edit');
    Route::put('/settings/payouts', [PayoutSettingsController::class, 'update'])->name('settings.payouts.update');
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::post('/verification', [VerificationController::class, 'store'])->name('verification.store');
    Route::resource('coupons', App\Http\Controllers\Vendor\CouponController::class)->except(['show']);

    Route::get('/withdrawals', [App\Http\Controllers\Vendor\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/create', [App\Http\Controllers\Vendor\WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/withdrawals', [App\Http\Controllers\Vendor\WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::get('/withdrawals/{withdrawal}', [App\Http\Controllers\Vendor\WithdrawalController::class, 'show'])->name('withdrawals.show');

    Route::middleware('module.access:vendor_shipping_status')->group(function () {
        Route::get('/orders/{order}/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('/orders/{order}/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
        Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
        Route::post('/shipments/{shipment}/tracking', [ShipmentController::class, 'addTracking'])->name('shipments.tracking');
        Route::post('/shipments/{shipment}/label', [ShipmentController::class, 'generateLabel'])->name('shipments.label');
    });

    Route::middleware('module.access:product_enquiry')->group(function () {
        Route::get('/enquiries', [App\Http\Controllers\Vendor\ProductEnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('/enquiries/{enquiry}', [App\Http\Controllers\Vendor\ProductEnquiryController::class, 'show'])->name('enquiries.show');
        Route::post('/enquiries/{enquiry}/respond', [App\Http\Controllers\Vendor\ProductEnquiryController::class, 'respond'])->name('enquiries.respond');
        Route::post('/enquiries/{enquiry}/close', [App\Http\Controllers\Vendor\ProductEnquiryController::class, 'close'])->name('enquiries.close');
    });

    Route::middleware('module.access:vendor_rma_request')->group(function () {
        Route::get('/rma', [RmaRequestController::class, 'index'])->name('rma.index');
        Route::get('/rma/{rma}', [RmaRequestController::class, 'show'])->name('rma.show');
        Route::post('/rma/{rma}/status', [RmaRequestController::class, 'updateStatus'])->name('rma.updateStatus');
    });

    Route::get('/followers', [VendorFollowController::class, 'followers'])
        ->middleware('module.access:follow_store')
        ->name('followers.index');

    Route::middleware('module.access:vendor_independent_store')->group(function () {
        Route::get('/theme', [ThemeController::class, 'index'])->name('theme.index');
        Route::put('/theme', [ThemeController::class, 'update'])->name('theme.update');
        Route::delete('/theme/image/{type}', [ThemeController::class, 'deleteImage'])->name('theme.delete-image');
    });

    Route::get('/store-domain', [StoreDomainController::class, 'index'])->name('store-domain.index');
    Route::put('/store-domain', [StoreDomainController::class, 'update'])->name('store-domain.update');
    Route::post('/store-domain/verify', [StoreDomainController::class, 'verify'])->name('store-domain.verify');

    // Auction routes
    Route::middleware('module.access:auctions')->group(function () {
        Route::resource('auctions', App\Http\Controllers\Vendor\AuctionController::class);
        Route::post('/auctions/{auction}/publish', [App\Http\Controllers\Vendor\AuctionController::class, 'publish'])->name('auctions.publish');
        Route::post('/auctions/{auction}/cancel', [App\Http\Controllers\Vendor\AuctionController::class, 'cancel'])->name('auctions.cancel');
        Route::post('/auctions/{auction}/extend', [App\Http\Controllers\Vendor\AuctionController::class, 'extend'])->name('auctions.extend');
        Route::post('/auctions/{auction}/settle', [App\Http\Controllers\Vendor\AuctionController::class, 'settle'])->name('auctions.settle');
        Route::get('/auctions/{auction}/bids', [App\Http\Controllers\Vendor\AuctionController::class, 'bids'])->name('auctions.bids');
    });

    // Vendor Media Library routes
    Route::middleware('module.access:vendor_media_library')->group(function () {
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::get('/media/create', [MediaController::class, 'create'])->name('media.create');
        Route::get('/media/{media}', [MediaController::class, 'show'])->name('media.show');
        Route::get('/media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
        Route::put('/media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
        Route::post('/media/folders', [MediaController::class, 'storeFolder'])->name('media.folders.store');
        Route::post('/media/bulk-delete', [MediaController::class, 'bulkDelete'])->name('media.bulk-delete');
        Route::post('/media/{media}/replace', [MediaController::class, 'replace'])->name('media.replace');
    });
});

// Payment/SMS provider callbacks. These sit inside the `web` group (session,
// CRLF protection, etc. -- CSRF is excepted for `webhooks/*` in
// VerifyCsrfToken) like the rest of this file, but unlike the other public
// POST endpoints here (contact, newsletter, chatbot) they had no rate limit
// at all. The `webhook` limiter is deliberately generous -- providers can
// legitimately burst events, and dropping a real payment webhook is worse
// than under-throttling one -- it exists to stop unbounded abuse, not to
// gate normal traffic.
Route::middleware('throttle:webhook')->group(function () {
    Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
    Route::post('/webhooks/paystack', [WebhookController::class, 'paystack'])->name('webhooks.paystack');
    Route::post('/webhooks/momo/{provider}', [WebhookController::class, 'momo'])->name('webhooks.momo');
    Route::post('/webhooks/swish', [WebhookController::class, 'swish'])->name('webhooks.swish');
});

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// Mobile App
Route::get('/mobile', [MobileController::class, 'index'])->name('mobile.index');
Route::get('/mobile/releases.json', [MobileController::class, 'releases'])->name('mobile.releases');

require __DIR__.'/auth.php';
