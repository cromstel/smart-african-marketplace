# Contributing

[Documentation Hub](docs/README.md) › Contributing

Thanks for working on Smart African Marketplace. This page covers how to get a
change reviewed and merged.

New to the codebase? Start with
[Getting Started](docs/guides/getting-started.md) and the
[Architecture Overview](docs/architecture/overview.md).

---

## Before you start

- **Understand the existing pattern before adding a new one.** This is a mature
  codebase with established conventions. Consistency beats personal preference.
- **Check the [Roadmap](docs/ROADMAP.md).** Phases 0 and 1 are higher priority
  than new features, and say why.
- **Know the frontend is Blade + Alpine**, not Livewire, despite what older
  documents said — see
  [Frontend architecture](docs/architecture/overview.md#frontend-architecture).

---

## Workflow

```bash
git checkout -b feature/your-feature   # or fix/ docs/ chore/
# make the change, with tests
vendor/bin/pint
php artisan test
git commit -m "feat: add vendor payout export"
git push origin feature/your-feature
```

Then open a pull request against `main`.

---

## Commit convention

| Prefix | Use for |
|---|---|
| `feat:` | New functionality |
| `fix:` | Bug fix |
| `docs:` | Documentation only |
| `style:` | Formatting, no behaviour change |
| `refactor:` | Restructuring, no behaviour change |
| `test:` | Tests |
| `chore:` | Tooling, dependencies, maintenance |
| `ci:` | Pipeline changes |
| `perf:` | Performance |
| `security:` | Security fixes |

Write the subject as an instruction: *"add vendor payout export"*, not *"added"*.

---

## Code standards

### PHP

- **Controllers validate and delegate.** Business logic belongs in
  `app/Services/`.
- **Declare `$fillable`** on every model. No `$guarded = []` — the codebase is
  currently 100% compliant; keep it that way.
- **No `env()` outside `config/`.** It returns `null` after `config:cache`. Add a
  config file and read `config()`.
- **Never interpolate user input into `DB::raw()` / `whereRaw()`.** Bind
  parameters.
- **Scope every vendor query by ownership.** Tenancy is enforced in application
  code, not by the database. A missing constraint is a data leak.
- Run `vendor/bin/pint` before committing.

### Blade and frontend

- Server-rendered Blade with Tailwind utilities and Alpine for interaction
- Reach for Livewire only when a component truly needs stateful server
  round-trips — and say why in the PR
- Semantic HTML, labelled controls, visible focus states, sufficient contrast
- Respect `prefers-reduced-motion`
- Do not hard-code user-facing strings that belong in `lang/`

### Security

Before opening a PR, ask:

- Can an unauthenticated user reach this?
- Can one vendor reach another vendor's data?
- Is input validated **server-side**, not only in the browser?
- Is authorization enforced on the server, not by hiding UI?
- Could this endpoint be replayed or spammed? Does it need a throttle?
- Are secrets kept out of client code, logs and URLs?

Report vulnerabilities per [SECURITY.md](SECURITY.md) — not in a public issue.

---

## Testing

Every behavioural change needs a test that fails without it. See the
[Testing Guide](docs/guides/testing.md).

```bash
php artisan test
```

Vendor-scoped changes additionally need an isolation test asserting that one
vendor cannot reach another's resource.

---

## Documentation

**Documentation changes ship in the same commit as the code.**

Two rules, both enforceable and both learned the hard way:

1. **Never cite a file that does not exist.** An audit found documents marking
   security work "Complete" with evidence files that were never written. If a
   test is your evidence, the test must exist.
2. **Link every new document from [the hub](docs/README.md).** An unlinked
   document is an unread document — the audit found 63 files reachable through
   only 69 links between them.

Where things go:

| Content | Location |
|---|---|
| Architecture, decisions | `docs/architecture/` |
| Env vars, API, commands | `docs/reference/` |
| Developer how-to | `docs/guides/` |
| Runbooks, policies | `docs/` |
| Customer/vendor/admin guides | `documentation/` |
| Unbuilt feature designs | `plans/` — with a **Status: Plan** banner |

New documents carry a status banner: **Current**, **Plan — not yet implemented**,
**Historical**, or **Needs review**.

### Check your work

```bash
# both scripts are in docs/DOCUMENTATION_AUDIT.md
python3 …   # broken relative links
python3 …   # docs citing files that do not exist
```

---

## Pull request checklist

- [ ] `php artisan test` passes
- [ ] New behaviour is covered by a test
- [ ] Vendor-scoped changes have an isolation test
- [ ] `vendor/bin/pint` clean
- [ ] `npm run lint` clean (if JS changed)
- [ ] Loading, empty and error states handled (if UI changed)
- [ ] Keyboard navigable and labelled (if UI changed)
- [ ] Works at mobile and desktop widths (if UI changed)
- [ ] Server-side validation and authorization present
- [ ] No secrets in code, logs or URLs
- [ ] Docs updated and linked from the hub
- [ ] No document cites a non-existent file

---

## Reviewing

Reviewers should check the security questions above, confirm business logic sits
in services rather than controllers, and verify that any documentation claim is
backed by something that actually exists.

Be direct about problems and kind about people.

---

## See also

- [Documentation Hub](docs/README.md)
- [Roadmap](docs/ROADMAP.md)
- [Testing Guide](docs/guides/testing.md)
- [AI Agent Instructions](.github/agents.md)
