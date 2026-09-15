# Deployment Runbook Checklist

> ## ⚠️ Required before any release that accepts mobile money
>
> **`MOMO_WEBHOOK_SECRET` must be set.** The `/webhooks/momo/{provider}`
> endpoint marks orders paid and is authenticated with an HMAC-SHA256 of the
> raw body. It **fails closed**: with no secret configured it returns 503 and
> logs a critical error, so MTN and M-Pesa payments stop being confirmed.
>
> It previously accepted unauthenticated requests, which meant anyone able to
> reach it with a known transaction reference could mark an order paid without
> paying. Closing that is why the behaviour changed.
>
> ```bash
> php -r "echo bin2hex(random_bytes(32));"   # generate
> php artisan app:verify-readiness --strict  # gate — non-zero exit blocks release
> ```
>
> Configure the provider to sign callbacks with the same key.


This checklist covers production readiness items that are code-ready in this repository and require environment provisioning.

Companion execution checklist for staging-first external rollout:
- `docs/operations/staging-deployment-checklist.md`

## 1. Secrets and Environment
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Configure production DB credentials.
- Configure SMTP credentials (`MAIL_*`).
- Configure payment keys (`STRIPE_*`, `PAYSTACK_*`).
- Configure monitoring DSN (`SENTRY_LARAVEL_DSN`) if enabled.
- Apply security baseline controls in `docs/security/policies-baseline.md`.
- Maintain recurring security audit cadence in `docs/security/audit-schedule.md`.

## 2. Infrastructure
- Enable HTTPS and valid SSL certificate.
- Configure queue worker process (see `docs/operations/queue-workers.md` for the required `--queue=...` flag and named queues -- a worker started without it silently never processes the campaign/media/chatbot/payout jobs).
- Configure scheduler (`php artisan schedule:run` every minute via cron).
- Configure persistent storage and backup location.
- Follow certificate renewal procedure in `docs/operations/ssl-certificate-renewal.md`.

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
- Execute backup verification workflow in `docs/operations/backup-verification.md`.
- Apply performance thresholds from `docs/operations/performance-baseline.md`.
- Configure suspicious activity detection per `docs/security/suspicious-activity-monitoring.md`.
- Align incident triage and fixes with `docs/operations/error-tracking.md`.
- Track feedback and adoption via:
  - `docs/support/user-feedback-collection.md`
  - `docs/growth/feature-usage-analytics.md`
- Apply database maintenance cycle in `docs/operations/database-optimization.md`.
- Maintain penetration testing cycle with `docs/security/penetration-testing.md`.
- Use coordinated disclosure policy in `docs/security/bug-bounty-policy.md`.

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

See `docs/operations/performance-baseline.md` for target thresholds and deploy verification.

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
  - `docs/growth/catalog-refresh-schedule.md`
  - `docs/growth/blog-content-calendar.md`
  - `docs/growth/seasonal-campaigns.md`
  - `docs/support/faq-update-workflow.md`
  - `docs/growth/homepage-content-refresh.md`
- Support and policy operations runbooks documented and assigned:
  - `docs/compliance/terms-review-cadence.md`
  - `docs/support/inquiry-response-sop.md`
  - `docs/support/refund-request-process.md`
  - `docs/support/order-issue-handling.md`
  - `docs/support/review-moderation-policy.md`
- Marketing and support enablement playbooks documented and assigned:
  - `docs/support/team-training.md`
  - `docs/growth/social-media-calendar.md`
  - `docs/growth/email-campaigns.md`
  - `docs/growth/seo-content-workflow.md`
  - `docs/growth/paid-advertising.md`
- Growth and compliance playbooks documented and assigned:
  - `docs/growth/influencer-partnerships.md`
  - `docs/growth/community-engagement.md`
  - `docs/compliance/gdpr-checklist.md`
  - `docs/compliance/payment-compliance-audit.md`
  - `docs/compliance/tax-regulation-updates.md`
  - `docs/compliance/regional-expansion.md`
