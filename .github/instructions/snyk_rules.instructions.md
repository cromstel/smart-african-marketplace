---
alwaysApply: true
trigger: model_decision
description: Security best practices for dependency management
---

# Security Best Practices

## Dependency Auditing

Run before declaring task complete if significant changes were made:

```bash
# NPM Security
npm run security-check

# Composer Security (will fail on high severity)
composer audit --format=github

# Or use the automated script
pwsh scripts/security-fix.ps1  # Windows
```

## Automated Security Scanning

GitHub Actions workflow runs weekly and on PR merges:
- `.github/workflows/security-audit.yml`
- Monitors `composer.json` and `package.json` changes
- Posts security reports to PR comments

## Known Limitations

- Previously blocked by ecosystem packages, but now resolved:
  - Upgraded to Laravel 12.62.0 with `barryvdh/laravel-dompdf:dev-master`
  - Removed unused `unicodeveloper/laravel-paystack` package
- All vulnerabilities patched: `composer audit` returns 0 advisories
- `npm audit` returns 0 vulnerabilities