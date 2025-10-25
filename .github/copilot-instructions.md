## Quick orientation for AI coding agents

This Laravel (v11) + Vite app uses PHP 8.2. The goal of this file is to give concise, actionable guidance so an AI coder can be productive immediately.

- Project roots: routes live in `routes/` (see `routes/web.php` for the main app flow). Controllers are under `app/Http/Controllers`. Models live under `app/` (PSR-4 `App\`). Services are in `app/Services` (see `NationService.php`).
- Build/test/asset commands (verified from repo files):

```bash
# PHP / Laravel
composer install
composer run-script lint   # runs laravel/pint
composer run-script stan   # runs phpstan
composer test               # runs pest or phpunit (see composer.json)

# JS / assets
npm install
npm run dev    # vite dev server
npm run build  # builds assets into public/build
```

- Testing notes: `phpunit.xml` config sets testing env values (CACHE_DRIVER=array, QUEUE_CONNECTION=sync, MAIL_MAILER=array). Tests live in `tests/Unit` and `tests/Feature`. Use `composer test`.

## Architecture & conventions (concrete, code-backed)

- Routes: `routes/web.php` is deliberately structured and commented.
  - There is a stable `/health` route used for CI/monitoring (useful for smoke checks).
  - Static file serving example: `/adwords/files/{filename}` performs `basename()` and a strict regex to avoid traversal — follow this pattern when serving stored files.
  - Most application routes are grouped under `Route::middleware('auth')->group(...)` and use controller methods named `index`, `new`, `store`, `edit`, `update`, `destroy` (see `CustomerController`, `SchedinaController`, `ComponentiController`, etc.). Match these names and HTTP verbs when adding features.
  - A catch-all route is registered at the end of `web.php`: `Route::get('{any}', ...) ->where('any', '.*')` — do not add routes after this unless intentionally overriding.

- Controllers & services
  - Controllers are thin and follow the Laravel controller/action pattern. Business logic that must be reused or tested lives in `app/Services` (e.g., `NationService` reads JSON files under `storage_path('app/gi_*.json')`). Prefer adding small service classes rather than large controller methods.

- Data files & localization
  - The repo uses JSON lookups in `storage/app/` (e.g., `gi_nazioni.json`, `gi_comuni.json`, `gi_comuni_cap.json`) accessed by services. When adding or refactoring features that use geographic data, re-use `NationService` patterns.
  - Language files exist under `lang/` for multiple locales (`en`, `it`, `sp`, etc.). The app has a locale route `index/{locale}` wired to `HomeController::lang` — use translation strings rather than hard-coded UI text.

## Patterns to follow (examples)
- Route -> Controller wiring example (follow the same style):

  Route::get('/customers', [App\\Http\\Controllers\\CustomerController::class, 'index'])->name('customers');

- File-serving safety (use basename + strict regex) as seen in `routes/web.php` before returning a stored file.

## Developer workflows & quick checks
- Use `composer` for PHP tasks and `npm` for frontend. Assets are built to `public/build` via Vite. If files in `public/build` are outdated, run `npm run build`.
- Linting: `composer run-script lint` (pint). Static analysis: `composer run-script stan` (phpstan).
- Tests: `composer test`. The test environment uses memory-safe phpunit config (`.phpunit.cache`) and sets fast bcrypt rounds and in-memory friendly drivers; ensure DB config for tests matches `phpunit.xml` or use the SQLite memory adapter when needed.

## Integration points & external dependencies
- Auth: Laravel UI + routes provided by `Auth::routes()` are used; many pages require `auth` middleware.
- API: `routes/api.php` uses `auth:sanctum` for the `/user` endpoint. If adding API endpoints, register them in `routes/api.php` and follow Sanctum auth patterns.
- Frontend: Vite + `laravel-vite-plugin` — entry points live under `resources/js` and `resources/scss`.

## When to ask for clarification
- If a change touches deployment/build artifacts (public/build, published assets), ask how the environment builds and deploys assets.
- If a change modifies authentication/roles or the catch-all route ordering, ask before modifying route order.

## Files to read first (fast path for context)
- `routes/web.php` — control flow and route conventions
- `app/Http/Controllers/*Controller.php` — controller naming & action patterns
- `app/Services/NationService.php` — example of service + storage JSON usage
- `composer.json`, `package.json`, `phpunit.xml` — build/test scripts and PHP/Node requirements

---
If you'd like, I can: create a short checklist for PRs in this repo (format, tests to run, files to check), or add examples of common unit test stubs that follow the project's test configuration. Any sections unclear or incomplete? Tell me which area you'd like expanded.
