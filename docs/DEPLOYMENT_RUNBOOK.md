# Deployment Runbook Checklist

This checklist covers production readiness items that are code-ready in this repository and require environment provisioning.

Companion execution checklist for staging-first external rollout:
- `docs/STAGING_EXTERNAL_DEPLOYMENT_CHECKLIST.md`

## 1. Secrets and Environment
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Configure production DB credentials.
- Configure SMTP credentials (`MAIL_*`).
- Configure payment keys (`STRIPE_*`, `PAYSTACK_*`).
- Configure monitoring DSN (`SENTRY_LARAVEL_DSN`) if enabled.
- Apply security baseline controls in `docs/SECURITY_POLICIES_BASELINE.md`.
- Maintain recurring security audit cadence in `docs/SECURITY_AUDIT_SCHEDULE.md`.

## 2. Infrastructure
- Enable HTTPS and valid SSL certificate.
- Configure queue worker process (`php artisan queue:work` under process manager).
- Configure scheduler (`php artisan schedule:run` every minute via cron).
- Configure persistent storage and backup location.
- Follow certificate renewal procedure in `docs/SSL_CERTIFICATE_RENEWAL_RUNBOOK.md`.

## 3. Database and Seeding
- Run migrations: `php artisan migrate --force`.
- Seed plans: `php artisan db:seed --class=SubscriptionPlanSeeder --force`.
- Verify seeded plans include: `free-trial`, `starter`, `professional`, `business`, `enterprise`.

## 4. Health and Verification
- Check health endpoint: `GET /healthz` returns `status=ok`.
- Run tests in CI or pre-deploy stage: `php artisan test`.
- Verify route bootstrap: `php artisan route:list`.
- Run readiness verification: `php artisan app:verify-readiness --strict`.
- Verify deterministic webhook flow locally:
  - `php artisan webhooks:replay-fixture stripe stripe-charge-succeeded.json --secret=whsec_fixture_test`
  - `php artisan webhooks:replay-fixture paystack paystack-charge-success.json --secret=sk_test_fixture`
  - `php artisan test --filter=WebhookE2ETest`

## 5. Backups and Monitoring
- Configure automated database backups.
- Configure log aggregation/retention.
- Configure application error alerting in monitoring provider.
- Execute backup verification workflow in `docs/BACKUP_VERIFICATION_RUNBOOK.md`.
- Apply performance thresholds from `docs/PERFORMANCE_MONITORING_BASELINE.md`.
- Configure suspicious activity detection per `docs/SUSPICIOUS_ACTIVITY_MONITORING_RUNBOOK.md`.
- Align incident triage and fixes with `docs/ERROR_TRACKING_AND_RESOLUTION.md`.
- Track feedback and adoption via:
  - `docs/USER_FEEDBACK_COLLECTION.md`
  - `docs/FEATURE_USAGE_ANALYTICS_BASELINE.md`
- Apply database maintenance cycle in `docs/DATABASE_OPTIMIZATION_AND_CLEANUP_RUNBOOK.md`.
- Maintain penetration testing cycle with `docs/PENETRATION_TESTING_RUNBOOK.md`.
- Use coordinated disclosure policy in `docs/BUG_BOUNTY_PROGRAM_POLICY.md`.

### Log Rotation and Archiving Standard
- Use daily-rotated Laravel logs in production:
  - `LOG_CHANNEL=stack`
  - `LOG_STACK=daily`
  - `LOG_DAILY_DAYS=30` (or your retention policy)
- Forward archived logs to centralized storage (S3/Blob/ELK/SIEM) before retention cutoff.
- Confirm rotation by checking dated files in `storage/logs/` after 24h uptime.
- Include log retention checks in release handover sign-off.

### Monitoring and Alerting Dashboard Minimums
- Health endpoint dashboard panel for `GET /healthz` (availability + latency + non-200 alerts).
- Readiness command panel from deploy pipeline (`php artisan app:verify-readiness --strict`) with failed-run alerts.
- Queue health panel (pending jobs, failed jobs, worker process up/down).
- Webhook reliability panel (Stripe/Paystack webhook 2xx rate, signature failures, processing errors).
- Alert routing must notify on-call channel for:
  - Health endpoint down > 2 minutes
  - Queue worker down > 2 minutes
  - Webhook error rate > 5% over 5 minutes

See `docs/PERFORMANCE_MONITORING_BASELINE.md` for target thresholds and deploy verification.

## 6. Smoke Test
- Admin login and dashboard load.
- Vendor login and product/inventory submenu access.
- Customer checkout path basic flow.
- Mobile token login (`/api/v1/mobile/auth/login`) and `/api/v1/mobile/auth/me`.

## 7. CI Strict Mode (Optional but Recommended)
- Configure repository secrets in GitHub Actions:
  - `STRIPE_KEY`
  - `STRIPE_SECRET`
  - `PAYSTACK_PUBLIC_KEY`
  - `PAYSTACK_SECRET_KEY`
- Workflow behavior:
  - Non-strict readiness always runs.
  - Strict readiness runs only when all secrets above are present.

## 8. Production Handover Checklist (Closeout)
- Deployment checklist document updated and versioned in repo.
- Queue workers and scheduler configured under process manager and validated after deploy.
- Monitoring dashboards created and alert routes tested.
- Backup verification checks completed and evidence archived.
- Real-credential webhook E2E run executed and evidence attached (command output + screenshot/log links).
- Dependency vulnerability scan completed in CI and reviewed by release owner.
- Content operations cadence documented and assigned:
  - `docs/PRODUCT_CATALOG_REFRESH_SCHEDULE.md`
  - `docs/BLOG_CONTENT_CALENDAR.md`
  - `docs/SEASONAL_CAMPAIGN_PLAYBOOK.md`
  - `docs/FAQ_UPDATE_WORKFLOW.md`
  - `docs/HOMEPAGE_CONTENT_REFRESH_RUNBOOK.md`
- Support and policy operations runbooks documented and assigned:
  - `docs/TERMS_AND_POLICIES_REVIEW_CADENCE.md`
  - `docs/CUSTOMER_INQUIRY_RESPONSE_SOP.md`
  - `docs/REFUND_REQUEST_PROCESS.md`
  - `docs/ORDER_ISSUE_HANDLING_RUNBOOK.md`
  - `docs/REVIEW_MODERATION_POLICY.md`
- Marketing and support enablement playbooks documented and assigned:
  - `docs/SUPPORT_TEAM_TRAINING_PROGRAM.md`
  - `docs/SOCIAL_MEDIA_POSTING_CALENDAR.md`
  - `docs/EMAIL_NEWSLETTER_CAMPAIGNS_PLAYBOOK.md`
  - `docs/SEO_CONTENT_CREATION_WORKFLOW.md`
  - `docs/PAID_ADVERTISING_CAMPAIGNS_RUNBOOK.md`
- Growth and compliance playbooks documented and assigned:
  - `docs/INFLUENCER_PARTNERSHIPS_PROGRAM.md`
  - `docs/COMMUNITY_ENGAGEMENT_PLAYBOOK.md`
  - `docs/GDPR_COMPLIANCE_CHECKLIST.md`
  - `docs/PAYMENT_COMPLIANCE_AUDIT_RUNBOOK.md`
  - `docs/TAX_REGULATION_UPDATE_PROCESS.md`
  - `docs/REGIONAL_COMPLIANCE_EXPANSION_RUNBOOK.md`
