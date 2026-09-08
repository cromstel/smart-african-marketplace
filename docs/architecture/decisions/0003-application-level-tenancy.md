# 0003. Application-level vendor separation

**Status:** Accepted, with caveats
**Date:** Retroactively recorded 2026-09-08

## Context

The platform is a multi-vendor marketplace: many independent vendors share one
installation. Their data must not leak across that boundary.

The options were a database per tenant, row-level security, or separation
enforced in application code.

## Decision

One database, shared tables, separation enforced in application code through
ownership scoping, the vendor middleware and policies.

## Consequences

**Easier:** migrations run once; cross-vendor reporting and platform analytics
are ordinary queries; the web installer stays simple, which matters given
[ADR 0001](0001-laravel-monolith.md); hosting cost stays flat as vendors are
added.

**Harder — and this is the real cost:** every query touching vendor-owned data
must carry its ownership constraint. There is no database-level backstop. A
forgotten `where` clause is not a display bug; it is a cross-tenant data leak.

**Consequently:**

1. Every vendor-scoped endpoint requires an isolation test asserting that vendor A
   cannot reach vendor B's resource
   ([pattern](../../guides/testing.md#the-vendor-isolation-pattern)).
2. Prefer `404` over `403` for another tenant's resource — `403` confirms it
   exists.
3. Reviewers must treat a missing ownership constraint as a security defect.

**The caveat:** this decision is only safe with the tests that enforce it, and
those tests do not yet exist across the 32 vendor controllers. Writing them is
the top priority of
[Roadmap Phase 2](../../product/roadmap.md#23-vendor-isolation-as-a-first-class-test-pattern).
Until then the boundary rests entirely on reviewer attention.

Revisit if a vendor requires contractual data isolation, or if a leak occurs.
