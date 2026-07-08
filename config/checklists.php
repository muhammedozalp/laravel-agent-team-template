<?php

use App\Checklists\Probes;

/*
|--------------------------------------------------------------------------
| Launch & maintenance checklists (guides/checklists.md, ADR 0011)
|--------------------------------------------------------------------------
| Definitions only — per-project STATE lives in the checklist_items table.
| Items with a `probe` class are auto-checked (button on the page + weekly
| schedule); the rest are toggled by the developer. Content consolidated from
| the reference project's research (a11y/SEO/deploy/hosting/email playbooks)
| plus this template's own runbooks. Extend per project; keys are stable.
*/

return [

    'groups' => [
        [
            'key' => 'auto',
            'label' => 'Automated',
            'description' => 'Probed automatically — run from this page or the weekly schedule; regressions notify developers.',
            'items' => [
                ['key' => 'auto.health', 'label' => 'App is healthy (/up)', 'probe' => Probes\HealthProbe::class],
                ['key' => 'auto.robots', 'label' => 'robots.txt correct for this environment', 'probe' => Probes\RobotsProbe::class],
                ['key' => 'auto.sitemap', 'label' => 'sitemap.xml reachable and non-empty', 'probe' => Probes\SitemapProbe::class],
                ['key' => 'auto.security-headers', 'label' => 'Security headers present', 'probe' => Probes\SecurityHeadersProbe::class],
                ['key' => 'auto.https', 'label' => 'APP_URL is HTTPS', 'probe' => Probes\HttpsProbe::class],
                ['key' => 'auto.debug-off', 'label' => 'Debug mode off in production', 'probe' => Probes\DebugModeProbe::class],
                ['key' => 'auto.sentry', 'label' => 'Error monitoring configured (Sentry DSN)', 'probe' => Probes\SentryProbe::class],
                ['key' => 'auto.mail', 'label' => 'Real mailer configured (not log/array)', 'probe' => Probes\MailerProbe::class],
                ['key' => 'auto.lighthouse', 'label' => 'Lighthouse scores meet the floor', 'description' => 'docker compose run --rm browser npm run lighthouse', 'probe' => Probes\LighthouseProbe::class],
            ],
        ],
        [
            'key' => 'frontend',
            'label' => 'Frontend',
            'items' => [
                ['key' => 'frontend.html-valid', 'label' => 'HTML validation green', 'description' => 'docker compose exec node npm run html:check (add new public routes to the script)'],
                ['key' => 'frontend.a11y-suite', 'label' => 'Accessibility browser tests green on all public pages', 'description' => 'axe assertions in tests/Browser — extend to every new page'],
                ['key' => 'frontend.keyboard', 'label' => 'Key flows usable by keyboard only', 'description' => 'menus, dialogs, forms; visible focus states; no traps'],
                ['key' => 'frontend.contrast', 'label' => 'Color contrast ≥ 4.5:1 (3:1 large text)', 'description' => 'axe catches most; verify brand colors on real pages'],
                ['key' => 'frontend.alt-text', 'label' => 'Meaningful alt text on content images', 'description' => 'decorative images get empty alt=""'],
                ['key' => 'frontend.reduced-motion', 'label' => 'prefers-reduced-motion respected', 'description' => 'entrance animations, sliders, carousels'],
                ['key' => 'frontend.responsive', 'label' => 'Device-emulation browser tests cover key pages', 'description' => 'tests/Browser/ResponsiveTest — extend per project'],
                ['key' => 'frontend.images-optimized', 'label' => 'Images optimized (WebP/AVIF, width/height set)', 'description' => 'no lazy-loading the LCP/hero image; width/height prevent CLS'],
                ['key' => 'frontend.i18n-keys', 'label' => 'No hardcoded user-facing strings', 'description' => 't() everywhere — guides/i18n.md'],
                ['key' => 'frontend.a11y-statement', 'label' => 'Accessibility statement page published', 'description' => 'EAA/WCAG legal baseline for EU-facing sites'],
            ],
        ],
        [
            'key' => 'backend',
            'label' => 'Backend & Security',
            'items' => [
                ['key' => 'backend.gate-green', 'label' => 'Full pre-merge gate green', 'description' => 'context/ai-interaction.md — pint, larastan, eslint/tsc, tests, build'],
                ['key' => 'backend.authorization', 'label' => 'Every route/action authorized', 'description' => 'policies + verified/approved gates; IDOR checked (security-auditor sub-agent)'],
                ['key' => 'backend.mass-assignment', 'label' => 'No takeover-grade columns fillable', 'description' => 'is_admin / is_developer / approved_at / email_verified_at never in #[Fillable]'],
                ['key' => 'backend.rate-limits', 'label' => 'Rate limits on auth + abuse-prone endpoints', 'description' => 'login/2FA/passkeys/register ship limited; add per-project endpoints'],
                ['key' => 'backend.auth-audit', 'label' => 'Auth audit log verified writing', 'description' => 'storage/logs/auth-*.log (90 days)'],
                ['key' => 'backend.queues', 'label' => 'Queued jobs processed (worker + scheduler alive)', 'description' => 'docker compose ps queue scheduler; deploy.sh restarts them'],
                ['key' => 'backend.n-plus-one', 'label' => 'No N+1s on hot paths', 'description' => 'Model::shouldBeStrict() throws in dev — exercise key pages'],
                ['key' => 'backend.migrations', 'label' => 'Migrations append-only and reversible', 'description' => 'guides/database.md'],
                ['key' => 'backend.secrets', 'label' => 'No secrets in repo/history', 'description' => 'Gitleaks in CI; .env* never committed (hook-enforced)'],
            ],
        ],
        [
            'key' => 'deploy',
            'label' => 'Deploy & CI',
            'items' => [
                ['key' => 'deploy.ci-required', 'label' => 'CI checks required on main (ruleset)', 'description' => 'main-ci-gate: pint/phpstan/frontend/tests/assets/gitleaks'],
                ['key' => 'deploy.secrets-set', 'label' => 'Deploy secrets configured', 'description' => 'DEPLOY_SSH_* + LARAVEL_ENV_ENCRYPTION_KEY + APP_DOMAIN (guides/deploy.md §5)'],
                ['key' => 'deploy.env-encrypted', 'label' => '.env.production.encrypted committed, key in password manager', 'description' => 'php artisan env:encrypt --env=production'],
                ['key' => 'deploy.dry-run', 'label' => 'First deploy tested with dry_run', 'description' => 'workflow_dispatch → dry_run=true, read the logs'],
                ['key' => 'deploy.rollback-tested', 'label' => 'Rollback procedure tested once', 'description' => 'previous SHA images + pre-migrate dump (guides/deploy.md § Rollback)'],
                ['key' => 'deploy.smoke-live', 'label' => 'Post-deploy smoke suite ran against production', 'description' => 'E2E_BASE_URL=https://<domain> … --group=smoke'],
                ['key' => 'deploy.staging', 'label' => 'Staging environment deployed and access-protected', 'description' => 'staging.<domain> — robots blocked automatically outside production'],
            ],
        ],
        [
            'key' => 'hosting',
            'label' => 'Hosting & Domain',
            'items' => [
                ['key' => 'hosting.hardening', 'label' => 'Server hardened day one', 'description' => 'key-only SSH, non-root deploy user, ufw 22/80/443, fail2ban, unattended-upgrades'],
                ['key' => 'hosting.backups-offsite', 'label' => 'DB backups copied OFF the server', 'description' => 'nightly dumps are local — cron rclone/rsync to object storage (guides/deploy.md § Backups)'],
                ['key' => 'hosting.restore-tested', 'label' => 'A restore was actually tested', 'description' => 'a backup you never restored is not a backup (guides/database.md)'],
                ['key' => 'hosting.monitoring', 'label' => 'Uptime + SSL/domain-expiry monitoring active', 'description' => 'external cron → ntfy (Phase 3) or UptimeRobot — must live OUTSIDE the server'],
                ['key' => 'hosting.registrar', 'label' => 'Registrar: 2FA on, auto-renew on, contacts current', 'description' => 'domain hijack/lapse protection'],
                ['key' => 'hosting.abuse-contact', 'label' => 'Provider abuse/notice emails reach a monitored inbox', 'description' => 'answer within hours — on unmanaged providers (Hetzner etc.) ignored abuse reports end in account termination, taking every site with them'],
                ['key' => 'hosting.billing-hygiene', 'label' => 'Provider auto-pay on + payment method current', 'description' => 'card-expiry reminder set; a locked provider account = all sites down and backups on it unreachable'],
                ['key' => 'hosting.dns-ttl', 'label' => 'DNS documented; TTLs sane before cutovers', 'description' => 'lower TTL to 300s before migrations; verify MX survives DNS moves'],
                ['key' => 'hosting.disk-logs', 'label' => 'Log rotation verified (app + docker)', 'description' => 'daily channel + json-file caps ship; confirm on server after a week'],
            ],
        ],
        [
            'key' => 'email',
            'label' => 'Email',
            'items' => [
                ['key' => 'email.spf-dkim-dmarc', 'label' => 'SPF + DKIM + DMARC records set and passing', 'description' => 'test with a mail-tester service; deliverability is DNS'],
                ['key' => 'email.from-domain', 'label' => 'Transactional mail sends from the real domain', 'description' => 'MAIL_FROM_ADDRESS matches; no shared-host default sender'],
                ['key' => 'email.flows-tested', 'label' => 'Verification/reset/notification emails tested end-to-end', 'description' => 'register on production with a real inbox'],
                ['key' => 'email.mailboxes', 'label' => 'Client mailboxes migrated/created where promised', 'description' => 'MX TTL lowered → IMAP import → switch MX → test both ways (playbook)'],
            ],
        ],
        [
            'key' => 'seo',
            'label' => 'SEO & Visibility',
            'items' => [
                ['key' => 'seo.titles-descriptions', 'label' => 'Unique title + meta description on every public page', 'description' => '<Seo> component; ~50-60 / ~150 chars (guides/seo.md)'],
                ['key' => 'seo.og-image', 'label' => 'Real 1200×630 share image set', 'description' => 'SEO_IMAGE — test with a share-preview tool'],
                ['key' => 'seo.sitemap-routes', 'label' => 'All public routes listed in config/seo.php sitemap', 'description' => 'crawlers cannot see Inertia links'],
                ['key' => 'seo.search-console', 'label' => 'Google Search Console: domain verified + sitemap submitted', 'description' => 'the recurring owner-ops item — do it at go-live'],
                ['key' => 'seo.business-profile', 'label' => 'Google Business Profile created/claimed', 'description' => 'the "Google Maps" listing: categories, hours, photos, review flow'],
                ['key' => 'seo.structured-data', 'label' => 'Structured data (JSON-LD) where applicable', 'description' => 'Organization/Product/LocalBusiness in the Blade root for crawler visibility'],
                ['key' => 'seo.redirects', 'label' => '301s for changed/legacy URLs', 'description' => 'especially when replacing an old site'],
                ['key' => 'seo.hreflang', 'label' => 'hreflang + localized URLs when a second public locale ships', 'description' => 'guides/i18n.md — session switching has no SEO surface'],
                ['key' => 'seo.analytics', 'label' => 'Analytics wired (with consent)', 'description' => 'GA4/alternative + Search Console linked'],
            ],
        ],
        [
            'key' => 'legal',
            'label' => 'Legal & Compliance',
            'items' => [
                ['key' => 'legal.privacy', 'label' => 'Privacy policy page (KVKK/GDPR appropriate)', 'description' => 'data residency of hosting counts — document where data lives'],
                ['key' => 'legal.cookies', 'label' => 'Cookie consent if tracking cookies exist', 'description' => 'analytics choice above decides this'],
                ['key' => 'legal.terms', 'label' => 'Terms of service where accounts/commerce exist'],
                ['key' => 'legal.imprint', 'label' => 'Company identity/contact page as locally required'],
                ['key' => 'legal.data-deletion', 'label' => 'Account deletion path communicated', 'description' => 'password-confirmed hard delete ships; policy text is per-client'],
            ],
        ],
        [
            'key' => 'handover',
            'label' => 'Client Handover',
            'items' => [
                ['key' => 'handover.admin-created', 'label' => 'Client admin account created (approval gate decided)', 'description' => 'app:make-admin; REQUIRE_ACCOUNT_APPROVAL per client'],
                ['key' => 'handover.walkthrough', 'label' => 'Admin panel walkthrough done with client'],
                ['key' => 'handover.credentials', 'label' => 'Credential inventory delivered', 'description' => 'what the client owns vs what you hold (deploy.md § Secrets inventory)'],
                ['key' => 'handover.maintenance', 'label' => 'Maintenance agreement/expectations recorded', 'description' => 'updates cadence, backups responsibility, support channel'],
            ],
        ],
    ],

];
