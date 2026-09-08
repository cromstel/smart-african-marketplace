# Security Audit Schedule

This schedule defines the recurring security audit process for production.

## Frequency
- Monthly: configuration and access review.
- Quarterly: full application security audit.
- Release-based: pre-release security checklist for major changes.

## Monthly Audit Checklist
- Review admin and privileged user access.
- Review secret rotation status and stale credentials.
- Review dependency vulnerability scan outcomes and unresolved findings.
- Review suspicious activity alerts and incident actions.

## Quarterly Audit Checklist
- Run full policy compliance review against `docs/security/policies-baseline.md`.
- Validate TLS configuration and certificate validity.
- Verify backup/restore evidence and log retention controls.
- Produce remediation list with owner and due date.

## Deliverables
- Audit report with findings by severity.
- Remediation tracker with ETA.
- Sign-off by engineering and operations owners.

