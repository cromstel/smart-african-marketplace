# Architecture Decision Records

**Status: Current**

[Documentation Hub](../../README.md) › [Architecture](../overview.md) › Decisions

Short records of significant technical decisions: what was decided, why, and what
it costs. They exist so that a year from now nobody has to reverse-engineer the
reasoning from the code.

## Index

| # | Title | Status |
|---|---|---|
| [0001](0001-laravel-monolith.md) | Laravel monolith over a decoupled stack | Accepted |
| [0002](0002-blade-over-livewire.md) | Blade + Alpine as the default UI, Livewire by exception | Accepted (retroactive) |
| [0003](0003-application-level-tenancy.md) | Application-level vendor separation | Accepted, with caveats |
| [0004](0004-database-queue-driver.md) | Database queue and cache drivers rather than Redis | Accepted |

## When to write one

Write an ADR when a decision is **costly to reverse** and **not obvious from the
code**: choosing or dropping a framework or major dependency, changing an
architectural boundary, adopting a persistence or tenancy strategy, or
deliberately accepting a known trade-off.

Do not write one for routine implementation choices.

## Format

Keep it to one page:

```markdown
# NNNN. Title

**Status:** Proposed | Accepted | Superseded by [NNNN](…) | Deprecated
**Date:** YYYY-MM-DD

## Context
The forces at play. What made a decision necessary.

## Decision
What was decided, in the active voice.

## Consequences
What this makes easy, what it makes hard, and what it rules out.
```

Number sequentially. Never edit a decision once accepted — supersede it with a
new record and update the status of the old one. The history is the point.
