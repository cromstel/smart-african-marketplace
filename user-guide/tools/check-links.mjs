#!/usr/bin/env node
/**
 * Documentation integrity check.
 *
 * Verifies that every relative link in every Markdown file under `user-guide/`
 * resolves to a file or directory that actually exists in the repository.
 *
 * Anchors (`#section`), query strings, absolute URLs, `mailto:` and `tel:`
 * links are ignored. Fenced code blocks are stripped first so template
 * snippets such as `{{ route('bookings.show') }}` are not mistaken for links.
 *
 * Usage:
 *   node user-guide/tools/check-links.mjs [root]
 *
 * Exits 1 when at least one link is broken, so it can gate CI.
 */

import { readdirSync, readFileSync, existsSync } from 'node:fs';
import { dirname, resolve, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const defaultRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const root = resolve(process.argv[2] ?? defaultRoot);

// [text](target) and [text](<target>) — target stops at whitespace or ).
const markdownLink = /\[[^\]]*\]\(\s*<?([^)>\s]+)>?/g;
// <img src="...">, <a href="..."> and CSS url(...) inside Markdown.
const htmlAttribute = /(?:src|href)\s*=\s*"([^"]+)"/g;
const cssUrl = /url\(\s*['"]?([^'")]+)['"]?\s*\)/g;

const ignoredScheme = /^(?:[a-z][a-z0-9+.-]*:|\/\/)/i;

function walk(dir) {
  const files = [];
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    if (entry.name === 'node_modules' || entry.name.startsWith('.')) continue;
    const full = join(dir, entry.name);
    if (entry.isDirectory()) files.push(...walk(full));
    else if (/\.mdx?$/i.test(entry.name)) files.push(full);
  }
  return files;
}

function stripCode(text) {
  // Fenced blocks, including ~~~ fences.
  return text
    .replace(/^[ \t]*(`{3,}|~{3,})[^\n]*\n[\s\S]*?^[ \t]*\1[ \t]*$/gm, '')
    // Inline code spans.
    .replace(/`[^`\n]*`/g, '');
}

const broken = [];
let checked = 0;

for (const file of walk(root)) {
  const text = stripCode(readFileSync(file, 'utf8'));
  const targets = new Set();

  for (const pattern of [markdownLink, htmlAttribute, cssUrl]) {
    pattern.lastIndex = 0;
    let match;
    while ((match = pattern.exec(text)) !== null) targets.add(match[1]);
  }

  for (const raw of targets) {
    if (!raw || raw.startsWith('#') || ignoredScheme.test(raw)) continue;

    // Drop the fragment and query, then decode percent-encoding.
    const path = raw.split('#')[0].split('?')[0];
    if (!path) continue;

    let decoded;
    try {
      decoded = decodeURIComponent(path);
    } catch {
      decoded = path;
    }

    checked += 1;
    if (!existsSync(resolve(dirname(file), decoded))) {
      broken.push({ file: relative(root, file), target: raw });
    }
  }
}

if (broken.length === 0) {
  console.log(`Documentation links OK — ${checked} relative links checked.`);
  process.exit(0);
}

for (const { file, target } of broken) {
  console.error(`BROKEN  ${file}  ->  ${target}`);
}
console.error(`\n${broken.length} broken link(s) out of ${checked} checked.`);
process.exit(1);
