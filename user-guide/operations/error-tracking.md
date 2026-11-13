# Error Tracking and Resolution Workflow

This workflow standardizes how production errors are triaged and resolved.

## Intake Sources
- Monitoring alerts (5xx spikes, queue failures, health check failures)
- Exception tracking provider alerts
- Support-reported incidents

## Severity Levels
- Sev 1: Production outage or payment-blocking failures
- Sev 2: Major feature degradation with workaround
- Sev 3: Limited-scope defects with low business impact

## Triage Steps
1. Capture timestamp, environment, affected route/service.
2. Reproduce from logs/request context.
3. Assign owner and severity.
4. Apply immediate mitigation (rollback, feature flag, hotfix).
5. Track permanent fix in issue backlog.

## Resolution SLAs
- Sev 1: acknowledge in 15 minutes; mitigation in 60 minutes
- Sev 2: acknowledge in 30 minutes; mitigation in 4 hours
- Sev 3: schedule in standard sprint cycle

## Closure Requirements
- Fix merged and deployed.
- Regression test added/updated where feasible.
- Incident summary with root cause and prevention action published.

