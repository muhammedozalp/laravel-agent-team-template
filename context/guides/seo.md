# SEO baseline

_What ships and how to extend it per project. Verified constraint: an SSR-off
Inertia app renders links and head tags client-side — Google executes JS, many
other crawlers don't, so the baseline is server-rendered defaults + explicit
lists, with per-page overrides on top._

## What ships

| Piece | Where | Notes |
|---|---|---|
| Server-rendered defaults (description, OG/Twitter tags, `og:image`) | `resources/views/app.blade.php` reading `config/seo.php` | what JS-less crawlers see; set `SEO_DESCRIPTION`/`SEO_IMAGE` per project |
| Per-page overrides | `<Seo title description image canonical>` — `resources/js/components/seo.tsx` | wraps Inertia `<Head>`; title gets the app-name suffix automatically |
| `robots.txt` | route → `SeoController::robots` | **production: allow + sitemap line; anywhere else: `Disallow: /`** — staging can never be indexed |
| `sitemap.xml` | route → `SeoController::sitemap`, cached 1h | pages listed explicitly in `config/seo.php` `sitemap` (crawler-based generation can't see Inertia links); spatie/laravel-sitemap renders it |
| HTML validity | `npm run html:check` (`guides/testing.md`) | |
| a11y/SEO scoring | Lighthouse probe — admin checklists page | |

## Per project

- Fill `SEO_DESCRIPTION` + a real 1200×630 `SEO_IMAGE`; use `<Seo>` on every
  public page (unique title ~50-60 chars, description ~150).
- Add every public route to `config/seo.php` `sitemap` as you build it.
- After go-live: verify the domain in **Google Search Console**, submit the
  sitemap, create/claim the **Google Business Profile** — manual steps tracked
  on the admin checklists page.
- SEO-critical content that must be visible without JS → consider enabling
  Inertia SSR (`config/inertia.php`, off by default — ADR 0007) or structured
  data (JSON-LD) rendered in the Blade root.
- One `<h1>` per page, semantic headings, `alt` on content images — enforced by
  the browser suite's axe checks.
