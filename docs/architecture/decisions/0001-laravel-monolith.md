# 0001. Laravel monolith over a decoupled stack

**Status:** Accepted
**Date:** Retroactively recorded 2026-09-08

## Context

The platform must be installable on commodity shared hosting — uploaded by FTP
and set up through a web wizard, in the manner of a CMS. The intended operators
are small businesses across Sweden and West Africa, not teams with a DevOps
function.

Early planning documents (`TODO.md`) contemplated a NestJS API with a Next.js
frontend, and those entries survive marked *N/A*.

## Decision

Build a single Laravel application that server-renders its own HTML, with a REST
API attached for the native mobile clients.

## Consequences

**Easier:** one deployable artifact; no build step on the server beyond compiled
assets; runs on Apache + PHP + MySQL, the most widely available hosting in both
target markets; no CORS, token-refresh or API-versioning burden for the web UI;
one language across the stack.

**Harder:** the web and mobile experiences do not share a rendering layer, so
some presentation logic exists twice; horizontal scaling means scaling the whole
application rather than a hot service; the frontend cannot be handed to a
JavaScript-only team.

**Ruled out:** independent deployment of parts of the system, and any hosting
model that assumes Node on the server.

The self-hosting constraint is load-bearing. Revisit this decision only if that
constraint is dropped.
