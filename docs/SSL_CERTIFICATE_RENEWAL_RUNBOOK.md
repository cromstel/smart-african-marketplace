# SSL Certificate Renewal Runbook

This runbook standardizes TLS certificate renewal and verification.

## Renewal Policy
- Start renewal process at least 30 days before expiry.
- Use automated renewal where provider supports it.
- Maintain fallback manual renewal procedure.

## Renewal Steps
1. Confirm certificate expiration date and SAN coverage.
2. Generate CSR/private key per security policy.
3. Complete CA/domain validation.
4. Install renewed certificate and intermediate chain.
5. Reload web server/load balancer.

## Post-Renewal Validation
- Verify HTTPS handshake and full chain trust.
- Verify no mixed-content/security warnings on key pages.
- Verify HSTS/TLS configuration remains intact.
- Verify webhook and callback endpoints remain reachable over TLS.

## Evidence
- Renewal date and certificate serial
- Validation screenshots/command output
- Operator and approver names

