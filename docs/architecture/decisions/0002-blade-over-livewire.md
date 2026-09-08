# 0002. Blade + Alpine as the default UI, Livewire by exception

**Status:** Accepted (recorded retroactively to match reality)
**Date:** Retroactively recorded 2026-09-08

## Context

`README.md` and `docs/architecture/tech-stack.md` described Livewire 3 as the UI layer — *"real-time
reactive components (no JavaScript frameworks needed)"*.

The codebase says otherwise. Two Livewire components exist (`LiveSearch` and one
vendor component), referenced by 2 of roughly 330 Blade views. The application is
server-rendered Blade with Tailwind CSS and Alpine.js.

Whatever the original intent, the built system chose Blade. The documentation had
not caught up, and a developer onboarding from it would have started in the wrong
paradigm.

## Decision

Blade + Tailwind + Alpine.js is the default for new screens. Livewire stays a
dependency and is used where a component genuinely needs stateful server
round-trips — live search being the existing example. Reaching for Livewire
requires a note in the pull request explaining why.

## Consequences

**Easier:** one rendering model across ~330 views; no reasoning about component
state living on the server; smaller payloads and no Livewire round-trip per
interaction; Alpine covers most interactivity in a few lines.

**Harder:** genuinely reactive UI (live bidding, real-time dashboards) needs
either Livewire or hand-written JavaScript plus the Echo/Soketi channel; there is
no established component library, so consistency depends on discipline.

**Follow-up:** extract a documented Blade component library
([Roadmap 4.3](../../product/roadmap.md#43-frontend-consistency)) so the next hundred
views resemble the last three hundred.

This record exists mainly to stop the documentation drifting back. The pattern
was already chosen; it just was not written down.
