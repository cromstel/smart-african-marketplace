#!/usr/bin/env python3
"""
Documentation integrity checks.

Two failures this catches, both of which reached main before it existed:

  1. A markdown link pointing at a file that does not exist.
  2. A document citing a repository file (in backticks) that does not exist --
     e.g. marking security work "Complete" with a test file as evidence when
     that test was never written.

Documents that legitimately discuss files which do not exist -- design plans,
historical snapshots, and audit findings -- declare themselves with a status
banner in their first lines and are exempt from check 2:

    > **Status: Plan - not yet implemented.**
    > **Status: Historical** ...
    > **Status: Needs review** ...

Fenced code blocks are ignored throughout, so templates and examples do not
produce false positives.

Dependency-free. Exit code 1 on any finding.

Usage:
    python3 scripts/docs/check-docs.py [--links-only] [--citations-only]
"""

import os
import re
import sys
import urllib.parse

SKIP_DIRS = {".git", "node_modules", "vendor", "storage"}

# Markdown links. Second alternative handles <angle-bracket> targets, which is
# how you write a link whose path contains parentheses.
LINK_RE = re.compile(r"\[([^\]]*)\]\(\s*(?:<([^>]+)>|([^)\s]+))\s*\)")
CITATION_RE = re.compile(
    r"`([A-Za-z0-9_./\-]+\.(?:php|md|yml|yaml|json|ps1|sh|js|css))`"
)
FENCE_RE = re.compile(r"^\s*(```|~~~)")

# Banners that exempt a document from the citation check.
EXEMPT_BANNER_RE = re.compile(
    r"Status:\s*\**\s*(Plan|Historical|Needs review)", re.IGNORECASE
)
BANNER_SCAN_LINES = 40

# Documents whose subject matter *is* the missing files.
CITATION_EXEMPT_FILES = {
    os.path.normpath("docs/DOCUMENTATION_AUDIT.md"),
    os.path.normpath("docs/ROADMAP.md"),
}

# Illustrative paths that are examples, not repository files.
CITATION_ALLOWLIST = {
    "database/migrations/xxxx_create_products_table.php",
}


def markdown_files(root="."):
    for dirpath, dirnames, filenames in os.walk(root):
        dirnames[:] = [d for d in dirnames if d not in SKIP_DIRS]
        for name in sorted(filenames):
            if name.endswith(".md"):
                yield os.path.join(dirpath, name)


def strip_code_fences(text):
    """Blank out fenced code blocks, preserving line numbering."""
    out, in_fence = [], False
    for line in text.split("\n"):
        if FENCE_RE.match(line):
            in_fence = not in_fence
            out.append("")
            continue
        out.append("" if in_fence else line)
    return "\n".join(out)


def is_citation_exempt(path, text):
    if os.path.normpath(path.lstrip("./")) in CITATION_EXEMPT_FILES:
        return True
    head = "\n".join(text.split("\n")[:BANNER_SCAN_LINES])
    return bool(EXEMPT_BANNER_RE.search(head))


def check_links():
    findings = []
    for path in markdown_files():
        raw = open(path, encoding="utf-8", errors="ignore").read()
        for match in LINK_RE.finditer(strip_code_fences(raw)):
            target = match.group(2) or match.group(3)
            if target.startswith(("http://", "https://", "#", "mailto:")):
                continue
            cleaned = urllib.parse.unquote(target.split("#")[0])
            if not cleaned:
                continue
            resolved = os.path.normpath(os.path.join(os.path.dirname(path), cleaned))
            if not os.path.exists(resolved):
                findings.append((path, match.group(1), target))
    return findings


def check_citations():
    findings = []
    for path in markdown_files():
        raw = open(path, encoding="utf-8", errors="ignore").read()
        if is_citation_exempt(path, raw):
            continue
        for cited in sorted(set(CITATION_RE.findall(strip_code_fences(raw)))):
            # A leading slash denotes a URL route (e.g. `/mobile/releases.json`),
            # not a path in the repository.
            if cited.startswith("/"):
                continue
            if "/" not in cited or cited in CITATION_ALLOWLIST:
                continue
            if not os.path.exists(cited):
                findings.append((path, cited))
    return findings


def main():
    args = sys.argv[1:]
    run_links = "--citations-only" not in args
    run_citations = "--links-only" not in args
    failed = False

    if run_links:
        findings = check_links()
        print(f"Broken links: {len(findings)}")
        for path, label, target in sorted(findings):
            print(f"  {path}: [{label}] -> {target}")
        if findings:
            print(
                "\n  Note: a link whose path contains parentheses must use angle "
                "brackets: [Label](<path (with parens).md>)"
            )
        failed |= bool(findings)

    if run_citations:
        findings = check_citations()
        print(f"Citations of non-existent files: {len(findings)}")
        for path, cited in sorted(findings):
            print(f"  {path}: {cited}")
        if findings:
            print(
                "\n  A document must not cite a file that does not exist. Either "
                "create the file, correct the reference, or add a status banner "
                "marking the document as a plan. See CONTRIBUTING.md."
            )
        failed |= bool(findings)

    if failed:
        sys.exit(1)
    print("\nDocumentation checks passed.")


if __name__ == "__main__":
    main()
