# Suspicious Activity Monitoring Runbook

This runbook defines minimum monitoring for fraud and account abuse signals.

## Signals to Monitor
- Repeated failed logins from same IP/account.
- Sudden high-volume order attempts from new accounts.
- Unusual webhook signature failures.
- Rapid privilege-sensitive actions in admin routes.
- Payment mismatch or repeated failed charge patterns.

## Detection Channels
- Application logs (`storage/logs/*` and centralized logging).
- Failed job queue and exception monitoring.
- Payment webhook error counters.
- Auth and rate-limit telemetry.

## Alert Thresholds
- Failed login bursts: > 20 attempts in 5 minutes per account/IP.
- Webhook signature failures: > 10 in 10 minutes.
- Admin sensitive action spikes: > 3x normal baseline in 15 minutes.

## Response Procedure
1. Confirm event scope and affected entities.
2. Temporarily rate-limit or block suspicious source.
3. Lock impacted accounts when compromise is suspected.
4. Notify operations/security owner and record incident.
5. Preserve logs and timeline for audit.

## Post-Incident
- Document root cause and actions.
- Rotate credentials if required.
- Add prevention rule or monitoring update.

