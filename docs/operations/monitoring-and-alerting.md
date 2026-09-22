# Monitoring and Alerting Dashboards

Minimum production monitoring baseline for Smart African Market.

## Dashboard Panels

1. **Availability**
   - Health endpoint: `GET /healthz`
   - Metrics: uptime %, p95 latency, non-200 count
2. **Readiness**
   - Deploy check result: `php artisan app:verify-readiness --strict`
   - Metrics: pass/fail trend by deploy
3. **Queue Health**
   - Pending jobs count
   - Failed jobs count
   - Queue worker process count
4. **Webhook Reliability**
   - Stripe webhook success/error counts
   - Paystack webhook success/error counts
   - Signature verification failures

## Alert Rules

- Health endpoint non-200 for > 2 minutes.
- Queue workers unavailable for > 2 minutes.
- Failed jobs increase above baseline threshold.
- Webhook error rate > 5% over 5 minutes.

## Alert Destinations

- On-call channel (Slack/Teams/PagerDuty).
- Secondary escalation to engineering email list.

## Verification Checklist

- Trigger a test alert and confirm delivery.
- Confirm alert acknowledgment path is documented.
- Confirm dashboard ownership and maintenance rotation.
