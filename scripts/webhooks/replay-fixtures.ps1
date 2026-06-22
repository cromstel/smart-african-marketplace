Param(
    [string]$StripeFixture = "stripe-charge-succeeded.json",
    [string]$PaystackFixture = "paystack-charge-success.json",
    [string]$StripeSecret = "whsec_fixture_test",
    [string]$PaystackSecret = "sk_test_fixture"
)

$ErrorActionPreference = "Stop"

Write-Host "Replaying Stripe fixture..." -ForegroundColor Cyan
php artisan webhooks:replay-fixture stripe $StripeFixture --secret=$StripeSecret

Write-Host "Replaying Paystack fixture..." -ForegroundColor Cyan
php artisan webhooks:replay-fixture paystack $PaystackFixture --secret=$PaystackSecret

Write-Host "Webhook fixture replay completed." -ForegroundColor Green

