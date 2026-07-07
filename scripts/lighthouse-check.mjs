// Lighthouse audit via Playwright's Chromium (guides/checklists.md Phase 3).
//   docker compose run --rm browser npm run lighthouse
//   LIGHTHOUSE_URL=https://example.com … npm run lighthouse
// Playwright launches the browser (its config provably renders this app);
// Lighthouse attaches over the CDP port. Writes storage/app/private/lighthouse.json
// — read by LighthouseProbe on the admin checklists page.
import { execFileSync } from 'node:child_process';
import { chromium } from 'playwright';

const url = process.env.LIGHTHOUSE_URL || process.env.APP_URL || 'https://examplesite.local';
const out = 'storage/app/private/lighthouse.json';
const port = 9222;

console.log(`Lighthouse: ${url}`);

const browser = await chromium.launch({
    args: [
        `--remote-debugging-port=${port}`,
        '--no-sandbox',
        '--disable-dev-shm-usage',
        '--ignore-certificate-errors',
    ],
});

try {
    execFileSync(
        'npx',
        [
            'lighthouse',
            url,
            `--port=${port}`,
            '--only-categories=performance,accessibility,best-practices,seo',
            '--output=json',
            `--output-path=${out}`,
            '--quiet',
        ],
        { stdio: 'inherit' },
    );
} finally {
    await browser.close();
}

const { categories } = JSON.parse((await import('node:fs')).readFileSync(out, 'utf8'));
const scores = Object.fromEntries(
    Object.entries(categories).map(([k, v]) => [k, Math.round(v.score * 100)]),
);
console.log('Scores:', JSON.stringify(scores));
console.log(`Report: ${out}`);
