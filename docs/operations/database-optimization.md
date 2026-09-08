# Database Optimization and Cleanup Runbook

This runbook defines recurring database maintenance for performance and stability.

## Frequency
- Weekly: lightweight health checks
- Monthly: optimization and cleanup window
- Release-based: index/query review for major features

## Weekly Checks
- Review slow query logs and top offenders.
- Check connection pool saturation and lock wait events.
- Confirm backup completion and restore test freshness.

## Monthly Maintenance
1. Analyze table/index statistics.
2. Optimize heavily updated tables during low-traffic window.
3. Archive or purge expired/obsolete operational records per retention policy.
4. Validate migration history and schema drift status.
5. Re-run key query performance checks.

## Safety Rules
- Always run backups before destructive cleanup.
- Execute changes in staging first with representative data.
- Use transactional scripts where possible.

## Verification
- Compare p95 query latency pre/post maintenance.
- Verify critical user flows: login, product search, checkout, vendor dashboard.
- Record actions, timings, and observed improvements.

