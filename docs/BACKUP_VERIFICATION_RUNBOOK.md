# Backup Verification Runbook

This runbook defines the minimum backup verification procedure for production.

## Scope
- Database backups
- Uploaded media and attachments (`storage/app/public`)
- Critical configuration snapshots (`.env` in secret manager, not in git)

## Verification Frequency
- Daily: backup job success check
- Weekly: restore test in staging
- Monthly: full recovery drill with sign-off

## Daily Checks
1. Confirm last scheduled backup completed successfully.
2. Confirm backup artifact size is within expected range.
3. Confirm backup retention policy is applied (no expired backups retained).
4. Confirm backup destination is reachable and encrypted at rest.

## Weekly Restore Test
1. Provision clean staging database.
2. Restore latest database backup.
3. Run `php artisan migrate:status` and confirm no mismatch.
4. Validate core entities exist (users, vendors, products, orders).
5. Restore a sample of uploaded files and verify access.
6. Record timestamp, operator, and result in release notes.

## Monthly Recovery Drill
1. Simulate production data loss scenario.
2. Restore database and media to recovery environment.
3. Measure RTO (recovery time objective) and RPO (recovery point objective).
4. Validate application boot and smoke flows:
   - `/healthz`
   - Admin login/dashboard
   - Vendor product listing
5. Document gaps and create action items.

## Evidence to Retain
- Backup job logs
- Restore command outputs
- Drill report with RTO/RPO metrics
- Incident/action links for failed checks

