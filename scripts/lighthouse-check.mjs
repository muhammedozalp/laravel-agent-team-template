// Lighthouse audit using Playwright's Chromium (guides/checklists.md Phase 3).
//   docker compose run --rm browser npm run lighthouse
//   LIGHTHOUSE_URL=https://example.com … npm run lighthouse
// Lighthouse's own launcher starts the browser (CHROME_PATH → the Playwright
// Chromium that provably renders this app); attaching to an externally
// launched browser over the CDP port fails ("Target closed").
// Writes storage/app/private/lighthouse.json — read by LighthouseProbe.
import { execFileSync } from 'node:child_process';
import { existsSync, readFileSync, renameSync } from 'node:fs';
import process from 'node:process';
import { chromium } from 'playwright';

const url = process.env.LIGHTHOUSE_URL || process.env.APP_URL || 'https://examplesite.local';
const out = 'storage/app/private/lighthouse.json';

// While the Vite dev server runs, public/hot points every asset at it — a URL
// the audit browser can't reach (and mixed content besides), so nothing paints
// and Lighthouse dies with NO_FCP. Audit against the built assets instead.
const hot = 'public/hot';
const hotBackup = 'public/hot.lighthouse-bak';
const hadHotFile = existsSync(hot);

if (hadHotFile) {
    renameSync(hot, hotBackup);
    console.log('Vite dev hot file moved aside — auditing built assets (restored after).');
}

console.log(`Lighthouse: ${url}`);

try {
    execFileSync(
        'npx',
        [
            'lighthouse',
            url,
            '--chrome-flags=--headless=new --no-sandbox --disable-dev-shm-usage --ignore-certificate-errors',
            '--only-categories=performance,accessibility,best-practices,seo',
            '--output=json',
            `--output-path=${out}`,
            '--quiet',
        ],
        { stdio: 'inherit', env: { ...process.env, CHROME_PATH: chromium.executablePath() } },
    );
} finally {
    if (hadHotFile) {
        renameSync(hotBackup, hot);
    }
}

const { categories, runtimeError } = JSON.parse(readFileSync(out, 'utf8'));

if (runtimeError) {
    console.error(`Runtime error: ${runtimeError.code} — ${runtimeError.message}`);
    process.exit(1);
}

const scores = Object.fromEntries(
    Object.entries(categories).map(([k, v]) => [k, Math.round(v.score * 100)]),
);

console.log('Scores:', JSON.stringify(scores));
console.log(`Report: ${out}`);
