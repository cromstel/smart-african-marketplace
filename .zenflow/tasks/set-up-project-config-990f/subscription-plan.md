# Subscription Packages Feature Plan

## Overview
Implement a complete subscription system with multiple pricing tiers including a 30-day free trial period. Users can subscribe to different plans to access premium features.

## Database Design

### `subscription_plans` Table
- `id` - Primary key
- `name` - Plan name (e.g., "Basic", "Pro", "Enterprise")
- `slug` - URL-friendly identifier
- `description` - Plan description (text)
- `price` - Monthly price (decimal)
- `currency` - Currency code (default: USD)
- `billing_cycle` - enum: 'monthly', 'yearly', 'lifetime'
- `trial_days` - Number of trial days (default: 30)
- `features` - JSON array of features included in this plan
- `max_products` - Max products vendor can list (-1 for unlimited)
- `max_orders` - Max orders per month (-1 for unlimited)
- `commission_rate` - Platform commission percentage (decimal)
- `is_popular` - Boolean to highlight plan
- `is_active` - Boolean to enable/disable plan
- `sort_order` - Integer for display ordering
- `created_at`, `updated_at`

### `user_subscriptions` Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `subscription_plan_id` - Foreign key to subscription_plans
- `status` - enum: 'trialing', 'active', 'past_due', 'canceled', 'expired'
- `trial_ends_at` - Trial end date (nullable)
- `current_period_start` - Current billing period start
- `current_period_end` - Current billing period end
- `cancel_at_period_end` - Boolean - cancel when period ends
- `canceled_at` - When subscription was canceled (nullable)
- `payment_provider` - enum: 'stripe', 'paystack', 'momo' (nullable)
- `payment_provider_subscription_id` - External subscription ID (nullable)
- `created_at`, `updated_at`

### `subscription_payments` Table
- `id` - Primary key
- `user_subscription_id` - Foreign key
- `amount` - Payment amount
- `currency` - Currency code
- `status` - enum: 'pending', 'paid', 'failed', 'refunded'
- `payment_provider` - Provider used
- `payment_provider_payment_id` - External payment ID
- `paid_at` - Payment completion date (nullable)
- `created_at`, `updated_at`

## Models

### SubscriptionPlan Model
```php
- fillable: name, slug, description, price, currency, billing_cycle, trial_days, features, max_products, max_orders, commission_rate, is_popular, is_active, sort_order
- casts: features => array, price => decimal, commission_rate => decimal, is_popular => boolean, is_active => boolean
- relationships: subscriptions() hasMany UserSubscription
- methods: isActive(), scopeActive(), scopePopular()
```

### UserSubscription Model
```php
- fillable: user_id, subscription_plan_id, status, trial_ends_at, current_period_start, current_period_end, cancel_at_period_end, canceled_at, payment_provider, payment_provider_subscription_id
- casts: trial_ends_at => datetime, current_period_start => datetime, current_period_end => datetime, canceled_at => datetime, cancel_at_period_end => boolean
- relationships: user() belongsTo, plan() belongsTo, payments() hasMany SubscriptionPayment
- methods: isActive(), isTrialing(), isPastDue(), isCanceled(), isExpired(), onTrial(), onGracePeriod(), cancel(), resume()
```

### SubscriptionPayment Model
```php
- fillable: user_subscription_id, amount, currency, status, payment_provider, payment_provider_payment_id, paid_at
- casts: amount => decimal, paid_at => datetime
- relationships: subscription() belongsTo UserSubscription
```

## Controllers

### Admin\SubscriptionPlanController
- index() - List all plans
- create() - Show create form
- store() - Create new plan
- edit() - Show edit form
- update() - Update plan
- destroy() - Soft delete plan
- toggle() - Toggle is_active status

### SubscriptionController (User-facing)
- index() - Display all available plans
- show() - Show plan details
- checkout() - Show checkout page for selected plan
- subscribe() - Process subscription
- mySubscription() - Show user's current subscription
- cancel() - Cancel subscription
- resume() - Resume canceled subscription

### WebhookController
- stripeWebhook() - Handle Stripe webhooks
- paystackWebhook() - Handle Paystack webhooks

## Views Structure

### Admin Views (`resources/views/admin/subscriptions/`)
- `plans/index.blade.php` - List all plans with CRUD actions
- `plans/create.blade.php` - Create new plan form
- `plans/edit.blade.php` - Edit plan form
- `subscribers/index.blade.php` - View all subscribers

### User Views (`resources/views/subscriptions/`)
- `index.blade.php` - Pricing page showing all plans
- `checkout.blade.php` - Checkout page with payment form
- `my-subscription.blade.php` - User's subscription dashboard
- `success.blade.php` - Subscription success page
- `canceled.blade.php` - Subscription cancellation confirmation

## Routes

### Admin Routes
```php
Route::resource('subscriptions/plans', Admin\SubscriptionPlanController::class);
Route::post('subscriptions/plans/{plan}/toggle', [Admin\SubscriptionPlanController::class, 'toggle']);
Route::get('subscriptions/subscribers', [Admin\SubscriptionPlanController::class, 'subscribers']);
```

### User Routes
```php
Route::get('/pricing', [SubscriptionController::class, 'index'])->name('subscriptions.index');
Route::get('/pricing/{plan}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
Route::middleware('auth')->group(function () {
    Route::get('/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription'])->name('subscriptions.my');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
});

Route::post('/webhooks/stripe', [WebhookController::class, 'stripeWebhook'])->name('webhooks.stripe');
Route::post('/webhooks/paystack', [WebhookController::class, 'paystackWebhook'])->name('webhooks.paystack');
```

## Middleware

### CheckSubscription Middleware
- Verify user has active subscription or is on trial
- Redirect to pricing page if subscription expired
- Allow access during grace period
- Exempt certain routes (pricing, checkout, account settings)

## Features to Implement

### Phase 1: Basic Subscription Management
1. Create migrations for all tables
2. Create models with relationships
3. Create admin CRUD for subscription plans
4. Create pricing page (public)
5. Implement basic subscription creation (free trial only initially)

### Phase 2: Payment Integration
1. Integrate Stripe for card payments
2. Integrate Paystack for African markets
3. Implement webhook handlers for payment events
4. Create subscription payment records
5. Handle subscription renewals

### Phase 3: Subscription Management
1. User subscription dashboard
2. Upgrade/downgrade functionality
3. Cancel subscription (end of period)
4. Resume canceled subscription
5. Subscription status middleware

### Phase 4: Advanced Features
1. Prorated upgrades/downgrades
2. Subscription usage limits (products, orders)
3. Email notifications (trial ending, payment failed, etc.)
4. Admin analytics dashboard
5. Export subscriber data

## Default Plans to Create

### 1. Free Trial (30 days)
- Price: $0/month
- Max 5 products
- Max 10 orders/month
- 15% commission
- All basic features

### 2. Starter Plan
- Price: $29/month
- Max 50 products
- Max 100 orders/month
- 10% commission
- Priority support

### 3. Professional Plan (Most Popular)
- Price: $79/month
- Max 200 products
- Max 500 orders/month
- 7% commission
- Priority support + Analytics

### 4. Enterprise Plan
- Price: $199/month
- Unlimited products
- Unlimited orders
- 5% commission
- Dedicated account manager + Custom features

## Payment Flow

1. User selects plan on pricing page
2. Redirected to checkout page (auth required)
3. User enters payment details (Stripe/Paystack form)
4. Payment provider validates and creates subscription
5. Webhook confirms subscription creation
6. Create UserSubscription record with trial_ends_at = 30 days from now
7. Redirect to success page
8. Send welcome email with trial information

## Trial Period Logic

- New subscriptions automatically get 30-day trial (or plan's trial_days value)
- No payment required during trial
- Full access to plan features during trial
- 7 days before trial ends, send reminder email
- 1 day before trial ends, send final reminder
- At trial end, attempt to charge card
- If payment succeeds: status = 'active'
- If payment fails: status = 'past_due', send payment failed email
- After 7 days past_due: status = 'expired', restrict access

## Implementation Priority

1. Database migrations and models ✓
2. Admin plan management
3. Public pricing page
4. Basic subscription creation (trial)
5. Payment integration (Stripe first)
6. Subscription management features
7. Webhooks and auto-renewal
8. Advanced features and notifications

## Testing Checklist

- [ ] Create subscription plan via admin
- [ ] View pricing page as guest
- [ ] Start free trial (no payment)
- [ ] Access features during trial
- [ ] Trial expiration handling
- [ ] First payment after trial
- [ ] Successful payment handling
- [ ] Failed payment handling
- [ ] Cancel subscription
- [ ] Resume subscription
- [ ] Upgrade plan
- [ ] Downgrade plan
- [ ] Webhook handling (Stripe)
- [ ] Webhook handling (Paystack)
- [ ] Email notifications

## Notes

- All prices should be stored in cents to avoid floating point issues
- Support multiple currencies (USD, GHS, NGN, KES, etc.)
- Gracefully handle webhook delivery failures with retry logic
- Log all subscription events for debugging
- Ensure GDPR compliance for subscription data
- Provide clear cancellation process (no dark patterns)
