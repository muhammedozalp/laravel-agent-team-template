# SEO baseline

**Idea:** shared `<Head>` wrapper (title template, meta description, OG/Twitter
tags, canonical), `spatie/laravel-sitemap` on the scheduler, environment-aware
robots.txt (disallow all unless production — staging vhost currently allows
crawlers), and decide per project whether SEO-critical pages need Inertia SSR
(config ships off, ADR 0007).

**Why parked:** table stakes for client sites, but the right defaults depend on
the first real project's pages. Graduate when one exists.
