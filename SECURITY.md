# Security Policy

## Supported Versions

We provide security fixes for the latest stable release of the Smart African Market
application. Older releases are no longer actively maintained.

| Version            | Supported          |
| ------------------ | ------------------ |
| Latest `main`      | :white_check_mark: |
| Older releases     | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability in this repository, please report it
**privately and responsibly**. Do **not** open a public GitHub issue or pull
request for security problems.

### Preferred method — GitHub Private Vulnerability Reporting
1. Open the repository's **Security** tab.
2. Click **Report a vulnerability** to start a private security advisory.
3. Fill in the details described below.

This keeps the report confidential until a fix is ready and does not require a
separate email address.

### Alternative method — direct email
Email the maintainers at <security-contact-not-configured> (configure this before release — replace with the project's real security contact address or rely solely on GitHub Private Vulnerability Reporting) using the subject line
`Security Vulnerability Report`.

### What to include
- A clear description of the vulnerability and its potential impact.
- Step-by-step instructions to reproduce it (proof-of-concept or exploit if available).
- The affected version, commit hash, or deployment environment.
- Any suggested fix or mitigation (optional).

### Response timeline
- **Acknowledgement:** within 72 hours of a complete report.
- **Triage & severity assessment:** within 7 days.
- **Fix & coordinated disclosure:** we aim to ship a patch as soon as possible
  and will coordinate public disclosure with you.

## Public Disclosure
Please allow us reasonable time to investigate and release a fix before any
public disclosure. Reporters who wish to be credited will be acknowledged in
the release notes.

## Out of Scope
The following are not treated as vulnerabilities under this policy:
- Issues requiring physical access or social engineering of project maintainers.
- Denial-of-service testing run against production infrastructure.
- Vulnerabilities in third-party dependencies (report those to the upstream
  project, but feel free to notify us so we can bump the package).
