# Repository Guidelines

## Project Structure & Module Organization
- `src/` holds the Laravel package core: providers, factories, data tables, and resource builders. Treat it as the source of truth for PHP classes under the `Ginkelsoft\Buildora` namespace.
- `resources/` contains Blade views, Alpine components, and Tailwind assets compiled by Vite; keep component folders scoped by feature.
- `routes/` stores package-specific route stubs consumed during installation. Avoid editing generated application routes here.
- `config/buildora.php` exposes user-facing settings; mirror any new feature toggles here and document defaults.
- `tests/Feature/` is the starting point for package-level integration coverage. Create mirrors of real user flows before adding new service contracts.

## Build, Test, and Development Commands
- `npm run dev` launches the Vite dev server with Tailwind JIT for UI work. Use it when iterating on `resources/`.
- `npm run build` performs a production Vite build and writes to `dist/`. Run before tagging a release to confirm assets compile.
- `composer dump-autoload` refreshes autoloads after adding classes under `src/`.
- `php artisan test` (from a linked Laravel app) or `./vendor/bin/phpunit` executes the package test suite. Use `--filter` for targeted runs.

## Coding Style & Naming Conventions
- Follow PSR-12 for PHP: four-space indentation, short array syntax, typed properties, and return types on public APIs.
- Keep class names singular nouns (`UserResourceFactory`) and align namespaces with directory depth.
- Front-end modules stay in ES modules with camelCase exports; Blade views use kebab-case filenames (`datatable-row.blade.php`).
- Run Tailwind via `@apply` sparingly—prefer component classes scoped to feature directories.

## Testing Guidelines
- Base feature tests on PHPUnit; name files as `<FeatureName>Test.php` under `tests/Feature`.
- Mock external services through Laravel’s container and assert generated resources with fixture snapshots in `tests/Fixtures/` (create when needed).
- Maintain parity between factory defaults in `database/` and expectations in tests to prevent brittle assertions.

## Commit & Pull Request Guidelines
- Emulate the existing Conventional Commit style: `type(scope): summary`, using lowercase imperatives (e.g., `perf(datatable): optimize query caching`).
- Reference relevant issues in the PR description, describe behavioral impact, and include screenshots or GIFs for UI adjustments.
- Ensure tests pass locally, link any config updates, and note breaking changes explicitly before requesting review.
