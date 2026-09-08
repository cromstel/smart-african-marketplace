# Penetration Testing Runbook

This runbook defines the minimum penetration testing workflow.

## Scope
- Authentication and account takeover paths
- Admin and vendor privilege boundaries
- Checkout/payment-related flows
- File upload and media handling endpoints
- Public API endpoints and rate-limit behavior

## Frequency
- Twice yearly minimum
- After major architecture or auth/payment changes

## Execution Model
- Internal pre-test using OWASP-style checklist
- External penetration test by qualified assessor
- Retest after fixes for high/critical findings

## Required Outputs
- Findings report with severity, evidence, and reproduction
- Fix plan with owner and target dates
- Retest report confirming closure of critical/high issues

## Closure Criteria
- No open critical findings
- High findings accepted only with formal risk sign-off
- Regression coverage added for fixed exploitable paths where feasible

