param(
    [string]$StripeFixture = "stripe-charge-succeeded.json",
    [string]$PaystackFixture = "paystack-charge-success.json"
)

$ErrorActionPreference = "Stop"

function Test-Secret([string]$name) {
    $value = [Environment]::GetEnvironmentVariable($name)
    if ([string]::IsNullOrWhiteSpace($value)) { return $false }
    $lower = $value.ToLowerInvariant()
    if ($value.Contains("...")) { return $false }
    if ($lower.Contains("your-")) { return $false }
    if ($lower.Contains("example")) { return $false }
    return $true
}

$required = @(
    "STRIPE_KEY",
    "STRIPE_SECRET",
    "PAYSTACK_PUBLIC_KEY",
    "PAYSTACK_SECRET_KEY"
)

$missing = @()
foreach ($key in $required) {
    if (-not (Test-Secret $key)) {
        $missing += $key
    }
}

if ($missing.Count -gt 0) {
    Write-Host "Missing or placeholder real credentials:" -ForegroundColor Red
    $missing | ForEach-Object { Write-Host " - $_" -ForegroundColor Red }
    Write-Host "Aborting webhook real-credential verification." -ForegroundColor Yellow
    exit 1
}

Write-Host "Running strict readiness check..." -ForegroundColor Cyan
php artisan app:verify-readiness --strict
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Replaying Stripe webhook fixture..." -ForegroundColor Cyan
php artisan webhooks:replay-fixture stripe $StripeFixture --secret=$env:STRIPE_SECRET
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Replaying Paystack webhook fixture..." -ForegroundColor Cyan
php artisan webhooks:replay-fixture paystack $PaystackFixture --secret=$env:PAYSTACK_SECRET_KEY
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Running webhook E2E test..." -ForegroundColor Cyan
php artisan test --filter=WebhookE2ETest
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Webhook real-credential verification completed successfully." -ForegroundColor Green
