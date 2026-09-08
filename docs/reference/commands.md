# Artisan Commands Reference

**Status: Current** — verified against `app/Console/Commands/` on 2026-09-08.

[Documentation Hub](../README.md) › Reference › Commands

The 23 custom console commands this application defines, beyond Laravel's
built-ins. Most are intended to run on a schedule — see
[Queue Workers & Background Jobs](../operations/queue-workers.md).

```bash
php artisan list          # everything, including framework commands
php artisan help <name>   # signature and options for one command
```

---

## Operations

| Command | Purpose |
|---|---|
| `app:verify-readiness` | Pre-flight check of configuration and dependencies. `--strict` is the production gate |
| `webhooks:replay-fixture` | Replay a signed webhook fixture locally, no provider needed |
| `maintenance:enable` | Enter maintenance mode |
| `maintenance:disable` | Leave maintenance mode |
| `app:generate-models` | Scaffolding helper |

### `app:verify-readiness`

```bash
php artisan app:verify-readiness            # report only
php artisan app:verify-readiness --strict   # non-zero exit on failure
```

Run in CI and before every release. `--strict` requires real payment and mail
credentials, so it only passes where those are provisioned.

### `webhooks:replay-fixture`

```bash
php artisan webhooks:replay-fixture stripe-charge-succeeded
php artisan webhooks:replay-fixture paystack-charge-success
```

Resolves `tests/Fixtures/webhooks/<name>.json`, signs it with a test secret, and
posts it to the webhook route — deterministic verification without a live
provider callback.

> **That fixtures directory does not currently exist**, so the command has
> nothing to replay. Creating the fixtures is
> [Roadmap 1.1](../product/roadmap.md#11-create-the-missing-webhook-fixtures).

---

## Subscriptions

| Command | Purpose |
|---|---|
| `subscriptions:process-renewals` | Charge due renewals |
| `subscriptions:check-expiring` | Flag subscriptions nearing expiry |
| `subscriptions:check-trial-expiry` | Transition ended trials |
| `subscriptions:send-trial-reminders` | Notify before a trial ends |

Money-moving and customer-visible. Failures are silent unless monitored —
alert on non-zero exits.

---

## Commerce

| Command | Purpose |
|---|---|
| `inventory:check-low-stock` | Alert vendors below threshold |
| `auction:process` | Close ended auctions, settle winners |
| `booking:send-reminders` | Remind customers of upcoming bookings |
| `loyalty:expire-points` | Expire points past their window |
| `vendors:normalize-pending-status` | Reconcile stuck vendor states |

> `auction:process` decides who won and is charged. It must run reliably and on
> time; a missed run leaves auctions unsettled. Its schedule deserves an alert.

---

## Marketing

| Command | Purpose |
|---|---|
| `campaigns:send-abandoned-cart` | Abandoned-cart recovery |
| `campaigns:send-welcome-series` | New-customer onboarding emails |
| `campaigns:send-winback` | Re-engage lapsed customers |

These send mail to real customers. Verify `MAIL_TO` is **empty** in production —
when set it redirects every message to one inbox
([Configuration](configuration.md#mail)).

---

## Content and media

| Command | Purpose |
|---|---|
| `sitemap:generate` | Regenerate `sitemap.xml` |
| `media:backfill` | Backfill legacy uploads into the media library |
| `media:optimize-pending` | Compress and resize queued images |

---

## Chatbot

| Command | Purpose |
|---|---|
| `chatbot:reindex-knowledge` | Rebuild the retrieval index from knowledge documents |
| `chatbot:purge-old` | Remove old sessions and messages |

Run `chatbot:reindex-knowledge` after changing knowledge documents, or answers
will be served from a stale index. `chatbot:purge-old` supports data-retention
obligations — see [GDPR Checklist](../compliance/gdpr-checklist.md).

---

## Administrative

| Command | Purpose |
|---|---|
| `users:reset-passwords` | Bulk password reset |

> `users:reset-passwords` invalidates credentials across accounts. Treat it as a
> break-glass tool: confirm the target set, and never run it casually against
> production.

---

## Suggested schedule

Indicative starting point — tune to your traffic. Register in the console kernel.

| Frequency | Commands |
|---|---|
| Every minute | `auction:process` |
| Every 15 minutes | `media:optimize-pending` |
| Hourly | `subscriptions:process-renewals`, `booking:send-reminders` |
| Daily | `inventory:check-low-stock`, `subscriptions:check-expiring`, `subscriptions:check-trial-expiry`, `subscriptions:send-trial-reminders`, `campaigns:*`, `loyalty:expire-points`, `sitemap:generate` |
| Weekly | `chatbot:purge-old`, `vendors:normalize-pending-status` |

The scheduler needs one cron entry:

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

> Work currently runs **inside these commands** rather than as queued jobs
> (`app/Jobs/` is empty), so a long command blocks its schedule slot and cannot
> be retried. Moving heavy work to queued jobs is
> [Roadmap 3.2](../product/roadmap.md#32-introduce-real-queued-jobs).

---

## See also

- [Configuration Reference](configuration.md)
- [Queue Workers & Background Jobs](../operations/queue-workers.md)
- [Deployment Runbook](../operations/deployment-runbook.md)
- [Command Lines](command-lines.md)
