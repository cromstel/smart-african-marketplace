# Security Policies Baseline

This baseline captures minimum policy controls for production operations.

## Access Control
- Enforce least privilege for admin, vendor, and support accounts.
- Require unique named accounts; prohibit shared credentials.
- Rotate privileged credentials at least every 90 days.
- Require MFA for admin and operational users.

## Secrets Management
- Store all production secrets in managed secret storage.
- Never commit secrets to source control.
- Rotate payment, mail, and API credentials on incident or role changes.

## Change and Release Policy
- All production changes require PR review and CI checks.
- Use deployment checklist and readiness verification before release.
- Maintain release notes with deployment owner and rollback plan.

## Data Protection
- Encrypt traffic in transit using TLS.
- Restrict direct database access to approved operators.
- Apply retention limits for logs and backups.

## Incident and Response
- Define on-call escalation path.
- Classify incidents by severity and target response windows.
- Keep post-incident action items tracked to closure.

