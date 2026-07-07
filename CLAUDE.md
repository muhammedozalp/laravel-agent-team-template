# CLAUDE.md

_**Thin router for agents.** The full project docs live in **`context/`** — this file
only orients you and points there so every fact has one home
(`context/project-overview.md` owns what/stack/architecture; this file links, never
restates). **Use it** as the entry point, then read the linked `context/` doc._

**Full-stack Laravel project template** — a reusable starting point for production
Laravel websites. **Laravel 13 (official React starter kit: React 19 + Inertia 2 +
TypeScript + Fortify auth) + PostgreSQL**, running entirely inside **Docker (native
engine)** behind a local **HTTPS vhost** (`https://examplesite.local`), developed by
a **4-agent team** (CEO → Senior/Dev/Runner), with multi-layer testing (unit/feature/browser —
e2e, smoke, a11y, cross-browser/device), CI/CD, and a token/cost-conscious workflow.

> **This is a template.** When starting a real project from it, run the bootstrap
> steps in `context/guides/new-project-from-template.md` (rename, env, git remote).

## Start here → `context/`

| You need… | Read |
|---|---|
| What it is + stack + architecture (+ full router) | `context/project-overview.md` |
| Conventions — naming / env / code style / lint | `context/coding-standards.md` |
| How we ship — workflow, agent team, pre-merge gate | `context/ai-interaction.md` |
| Where we are now + history | `context/current-feature.md` |
| Why a choice was made (ADRs) | `context/decisions/` |
| Feature / fix specs (work + status) | `context/feature/` · `context/fix/` |
| Undecided ideas / discussions | `context/backlog/` |
| Agent coordination (CEO → Senior/Dev/Runner) | `context/agent_team/index.md` (charter; logs + board are local) |
| Token / context / session cost policy | `context/token-optimization.md` |
| Docker environment how-to | `context/guides/docker.md` |
| Auth: what ships, hardening, admin panel (Filament) | `context/guides/auth.md` |
| Claude Code tooling (plugins / MCPs / skills) | `context/guides/claude-tooling.md` |
| Testing strategy (unit / feature / browser / static) | `context/guides/testing.md` |
| Database & PostgreSQL practices | `context/guides/database.md` |
| Deploy / CI-CD how-to | `context/guides/deploy.md` |
| SEO baseline (robots / sitemap / meta) | `context/guides/seo.md` |
| i18n — Turkish default, adding languages | `context/guides/i18n.md` |
| Start a new project from this template | `context/guides/new-project-from-template.md` |

## Commands

```bash
# All app commands run inside Docker — never on the host.
docker compose up -d                  # start the full stack (all services — see context/guides/docker.md)
docker compose exec app php artisan   # artisan entry point
docker compose exec app composer      # composer entry point
docker compose exec app php artisan test --testsuite=Unit,Feature   # backend suite
docker compose run --rm browser php artisan test --testsuite=Browser   # e2e/smoke/a11y
docker compose exec app ./vendor/bin/pint             # code style (Laravel Pint)
docker compose exec app ./vendor/bin/phpstan analyse  # static analysis (Larastan)
docker compose exec node npm run dev                  # Vite dev server
docker compose exec node npm run build                # production asset build
docker compose exec node npm run lint:check           # ESLint (frontend gate)
docker compose exec node npm run format:check         # Prettier check (frontend gate)
docker compose exec node npm run types:check          # TypeScript check (frontend gate)
docker compose exec node npm run html:check           # HTML validation of rendered pages
```

## Non-negotiables (detail in `context/`)

- **Git:** PR → `main`; **never commit to `main`**; **never delete branches**;
  milestone tags only. (`context/ai-interaction.md`)
- **Never edit** `vendor/`, `node_modules/`, `public/build/`, `storage/`, or `.env*`; **`.env` is
  never committed** (only `.env.example`). Guardrail hooks enforce this.
  (`context/coding-standards.md`)
- **Everything runs in Docker** — no host PHP/composer/node/psql. (`context/guides/docker.md`)
- **Migrations are append-only** once merged — never edit a merged migration; write a
  new one. (`context/guides/database.md`)
- **Tests gate every merge** — Pint + PHPStan + Pest must pass locally and in CI
  before a PR is merged. (`context/guides/testing.md`)
- **Test-first (TDD):** production code exists to make a failing test pass —
  red → green → refactor; exemptions are declared in the REPORT.
  (`context/guides/testing.md` § TDD, ADR 0006)
- **Keep docs in sync:** a behavior change updates the affected docs **in the same
  PR.** (`context/ai-interaction.md`)
- **Mind the token budget:** follow `context/token-optimization.md` — read only the
  `context/` doc you need, prefer skills/subagents over pasting large files, and use
  the configured MCP servers (e.g. **Context7** for library docs) instead of guessing.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- filament/filament (FILAMENT) - v5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA_REACT) - v3
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>
