# 0004. Database queue and cache drivers rather than Redis

**Status:** Accepted
**Date:** Retroactively recorded 2026-09-08

## Context

Laravel defaults toward Redis for queues, cache and sessions at any scale. Redis
is another service to install, secure and monitor — and it is frequently absent
from the shared hosting this platform targets ([ADR 0001](0001-laravel-monolith.md)).

## Decision

Default `QUEUE_CONNECTION`, `CACHE_DRIVER` and `SESSION_DRIVER` to `database`.
Keep the Redis, Memcached, DynamoDB and SQS configuration present in `config/`
so an operator with the infrastructure can switch by changing `.env`, but do not
require any of it.

## Consequences

**Easier:** MySQL is the only stateful dependency; installation is genuinely
"upload and run the wizard"; one backup covers application state and queue state.

**Harder:** the database absorbs queue polling and cache traffic, which becomes
the first bottleneck under load; database queues do not match Redis for
throughput or latency; cache invalidation is slower.

**Mitigation:** the alternative drivers are already configured, so a busy
installation can move to Redis with an `.env` change and a restart. That is a
deliberate escape hatch, not an oversight.

**Related:** `app/Jobs/` is currently empty — background work runs inside
scheduled Artisan commands rather than queued jobs, so the queue is barely used
at present. Moving heavy work into real jobs is
[Roadmap 3.2](../../ROADMAP.md#32-introduce-real-queued-jobs), and will make this
decision matter more than it does today.

Revisit when database queue latency shows up in monitoring.
