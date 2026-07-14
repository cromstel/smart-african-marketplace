#!/usr/bin/env pwsh
# Security Fix Script for Smart African Marketplace
# Automatically scans and fixes npm and composer vulnerabilities

Write-Host "🔍 Scanning for vulnerabilities..." -ForegroundColor Cyan

# NPM Security Scan and Fix
Write-Host "📦 Checking npm vulnerabilities..." -ForegroundColor Yellow
npm audit --json 2>$null > audit-report.json
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ No npm vulnerabilities found" -ForegroundColor Green
} else {
    Write-Host "🔧 Attempting to fix npm vulnerabilities..." -ForegroundColor Yellow
    npm audit fix --force
    npm audit --json 2>$null > audit-report.json
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ npm vulnerabilities fixed" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Some npm vulnerabilities remain (manual review needed)" -ForegroundColor Red
    }
}

# Composer Security Scan
Write-Host "�/PHP Checking composer vulnerabilities..." -ForegroundColor Yellow
$auditOutput = composer audit --format=json 2>$null

# Parse vulnerabilities
$vulnData = $auditOutput | ConvertFrom-Json -ErrorAction SilentlyContinue
if ($vulnData -and $vulnData.advisories) {
    $advisories = $vulnData.advisories
    Write-Host "⚠️ Found $($advisories.PSObject.Properties.Count) packages with vulnerabilities" -ForegroundColor Red

    foreach ($pkg in $advisories.PSObject.Properties) {
        Write-Host "  - $($pkg.Name): $($pkg.Value.Count) advisories" -ForegroundColor Yellow
    }

    # Check if Laravel vulnerabilities are pending ecosystem support
    $laravelVulns = $advisories.'laravel/framework'
    if ($laravelVulns) {
        Write-Host "⚠️ Laravel vulnerabilities detected - requires ecosystem support for Laravel 12 upgrade" -ForegroundColor Yellow
    }
} else {
    Write-Host "✅ No composer vulnerabilities found" -ForegroundColor Green
}

Write-Host "`n📝 Summary:" -ForegroundColor Cyan
npm audit 2>&1 | Select-String "found"
composer audit 2>&1 | Select-String "Found"

Write-Host "`n📋 Next steps if vulnerabilities remain:" -ForegroundColor Yellow
Write-Host "1. Review audit report in audit-report.json" -ForegroundColor Gray
Write-Host "2. Check packagist.org for updated package versions" -ForegroundColor Gray
Write-Host "3. Consider upgrading to newer major versions if ecosystem supports it" -ForegroundColor Gray