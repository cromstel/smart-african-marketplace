# Performance Monitoring Baseline

This baseline defines the minimum metrics and alert thresholds for production monitoring.

## Key Service Level Targets
- API p95 latency: `< 800ms`
- Web page p95 latency: `< 1500ms`
- Error rate (5xx): `< 1%`
- Queue backlog: `< 500` pending jobs for more than 5 minutes

## Required Dashboards
- Application health (`/healthz` uptime and latency)
- HTTP performance (p50/p95/p99 response times by endpoint)
- Error monitoring (exceptions by type and deployment)
- Queue monitoring (pending, failed, retry rates)
- Database performance (slow queries, connection usage)

## Alert Minimums
- Health endpoint non-200 for 2 minutes
- 5xx error rate > 3% over 5 minutes
- Queue worker unavailable for 2 minutes
- Failed jobs growth > 20 in 10 minutes
- Database CPU > 85% for 10 minutes

## Release Verification
After each deploy:
1. Confirm `/healthz` status is `ok`.
2. Confirm no spike in 5xx errors for 15 minutes.
3. Confirm queue lag remains under threshold.
4. Confirm top 10 endpoints remain within p95 baseline.
5. Attach dashboard snapshots to release log.

## Ownership
- Engineering owns thresholds and tuning.
- Operations owns alert routing and on-call response.
- Product owners review monthly trend reports.

