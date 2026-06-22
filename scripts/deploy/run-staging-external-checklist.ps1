param(
    [string]$EvidenceDir = "storage/logs/deploy-evidence",
    [switch]$SkipWebhookVerification
)

$ErrorActionPreference = "Stop"

function Invoke-Step([string]$Name, [scriptblock]$Action) {
    Write-Host ""
    Write-Host "==> $Name" -ForegroundColor Cyan
    & $Action
    if ($LASTEXITCODE -ne 0) {
        throw "Step failed: $Name"
    }
}

if (-not (Test-Path $EvidenceDir)) {
    New-Item -ItemType Directory -Path $EvidenceDir -Force | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$logPath = Join-Path $EvidenceDir "staging-external-checklist-$timestamp.log"

Start-Transcript -Path $logPath | Out-Null
try {
    Invoke-Step "Strict readiness" {
        php artisan app:verify-readiness --strict
    }

    Invoke-Step "Migrate + plan seed + status" {
        php artisan migrate --force
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
        php artisan db:seed --class=SubscriptionPlanSeeder --force
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
        php artisan migrate:status
    }

    Invoke-Step "Queue and scheduler quick checks" {
        php artisan queue:work --once
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
        php artisan schedule:list
    }

    if (-not $SkipWebhookVerification.IsPresent) {
        Invoke-Step "Webhook real-credential verification" {
            powershell -ExecutionPolicy Bypass -File scripts/webhooks/verify-real-credentials.ps1
        }
    } else {
        Write-Host "Skipping webhook verification by request." -ForegroundColor Yellow
    }

    Invoke-Step "Webhook E2E regression check" {
        php artisan test --filter=WebhookE2ETest
    }

    Write-Host ""
    Write-Host "Staging external checklist completed." -ForegroundColor Green
    Write-Host "Evidence log: $logPath" -ForegroundColor Green
}
finally {
    Stop-Transcript | Out-Null
}
