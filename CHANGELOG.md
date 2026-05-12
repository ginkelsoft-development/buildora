# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

This batch reflects a single coordinated review of the package: a test
foundation, a security pass, a performance pass, and a god-class
decomposition. Everything below is backwards compatible — consumers may
opt-in to the new authorization and file-upload safeguards as needed.

### Security

- **Stored XSS in `RichTextField` output is now sanitised.**
  The `richtext` Blade component routes `$value` through a new
  `HtmlSanitizer` (DOM-based, no external dependency). Script/iframe/
  object/embed/style/link/meta tags are stripped entirely; per-tag
  attribute whitelist drops `class`/`style`/`id`/event-handlers; URL
  schemes `javascript:`, `data:`, `vbscript:` are rejected on `href`/`src`.
  _(#122, PR #152)_

- **`FileField` extension deny-list and MIME whitelist.**
  Every `FileField` automatically rejects executable extensions
  (`php`/`phar`/`phtml`/`jsp`/`asp`/`htaccess`/`exe`/`bat`/…) regardless
  of configuration, closing the upload-to-RCE class. A new
  `allowedMimeTypes()` builder method enables opt-in server-side MIME
  whitelisting via Laravel's `mimetypes:` rule (magic-byte based, not
  spoofable via `Content-Type`).
  _(#121, PR #151)_

- **`scopeQuery()` hook for record-level authorization.**
  `BuildoraResource` gains a new overridable hook applied by
  `QueryFactory` to every list, detail, export and panel query.
  Subclasses override to enforce row-level access:

  ```php
  public function scopeQuery(Builder $query): Builder
  {
      return $query->where('owner_id', auth()->id());
  }
  ```

  Direct URL access (`/buildora/user/{id}`) is also filtered — the model
  never resolves for records outside the scope.
  _(#123, PR #168)_

- **Mass-assignment via relation names blocked.**
  `BuildoraController::store()`/`update()` previously passed `$request->all()`
  to `handleRelationships()`, letting a caller sync any relation method
  defined on the underlying model — even ones the resource never
  declared. Now narrowed to keys declared in `defineFields()`.
  _(#120, PR #149)_

- **Sort-direction defence-in-depth at the datatable edge.**
  `DataFetcher` now normalises the `sortDirection` parameter to strict
  `asc`/`desc` (case- and whitespace-insensitive). Eloquent already
  rejected invalid directions with `InvalidArgumentException`; the
  normalisation prevents that surfacing as a 500 to end users.
  _(#119, PR #148)_

- **CSRF coverage pinned on every mutating Buildora route.**
  Regression test walks `Route::getRoutes()` and asserts each
  POST/PUT/PATCH/DELETE route under the configured prefix inherits either
  `web` or `VerifyCsrfToken`.
  _(#124, PR #155)_

- **`BuildoraAbility` enum** replaces the magic strings (`'view'`,
  `'create'`, `'edit'`, `'delete'`) used in policies and permission
  commands. Values are intentionally identical to the legacy strings —
  no DB migration required.
  _(#139, PR #161)_

- **Streaming exports prevent OOM-class DoS.**
  `BuildoraExportController` previously materialised the full result set
  in memory via `->get()` before handing it to laravel-excel as
  `FromArray`. Replaced with `FromQuery + WithChunkReading + WithMapping`
  so peak memory stays bounded regardless of dataset size.
  _(#126, PR #170)_

### Performance

- **`ModelResolver::resolve()` is memoised** per process; identical
  resource-class lookups no longer repeat the reflection/`class_exists`
  chain. _(#127, PR #153)_
- **`ColumnBuilder::build()` is cached per resource class** and walks the
  field list once instead of N+1 times. _(#131, PR #154)_
- **`GlobalSearchController` skips empty/short terms** below a
  configurable `min_term_length` (default 2). The per-resource limit is
  now configurable via `buildora.global_search.limit_per_resource`.
  Prevents `LIKE '%%'` full-table scans on every keystroke.
  _(#130, PR #159)_
- **Relation-field `setValue()` reads loaded relations** via a new
  `RelationLoader` helper. `HasManyField`, `BelongsToManyField` and
  `BelongsToField` previously called `pluck()`/`getResults()` on the
  relation builder, forcing a fresh query per row even when the parent
  collection was eager-loaded. _(#160, PR #162)_
- **`QueryFactory` gains explicit `forList()` and `forDetail()` entry
  points** that document the eager-load intent at the call site.
  _(#129, PR #166)_

### Added

- **Foundation test suite** — went from 5 tests in a single file to ~290+
  tests across ~30 files. _(#116, PR #144)_
- **`FieldContractTest`** parametrised over all 23 field types
  (instantiation, label derivation, builders, visibility toggles,
  `getDisplayValue`, `toArray`).
- **GitHub Actions CI** with composer cache and status badges, matrix
  over PHP 8.1–8.4 × Laravel 10/11/12. _(#117, PR #147)_
- **`DatatableRequest` value object** centralises HTTP parameter parsing
  and validation (sortDirection normalisation, `per_page` clamp, `page`
  clamp). _(#140, PR #167)_
- **`renderForDisplay()` hook on `Field`** replaces the
  `instanceof ViewField` check in `RowFormatter`; new field types can
  hook in without touching the formatter. _(#134, PR #169)_

### Changed

- **`BuildoraResource` decomposed into traits** under `Resources\Concerns`.
  ~150 lines lighter; the abstract `defineFields()` contract stays on the
  class itself, every other hook lives in a focused trait. No consumer
  changes required. _(#135, PRs #172, #173, #174, #175)_
  - `HasResourceActions` — row/bulk/page actions
  - `HasResourceNavigation` — title/slug/showInNavigation/searchResultConfig
  - `HasResourceQuery` — `query()`/`queryWithRelations()`
  - `HasResourceFields` — `fill()`/`setFields()`/`getFields()`/`resolveFields()`
- **`BuildoraServiceProvider` decomposed into focused private methods.**
  `boot()` and `register()` each shrunk from ~50 lines of inline setup
  into 7-line orchestrators. _(#136, PR #171)_
- **`RichTextField` and `EditorField` docblocks** now document their
  paired roles (display vs input). _(#137, PR #163)_
- **`DisplayField` and `ViewField` docblocks** now explain when to pick
  which (string vs Blade partial). _(#138, PR #164)_

### Fixed

- **`HasOneField::make()` returns `HasOneField`** instead of falling back
  to a base `Field` instance (`Field::make()` uses `new self()` and only
  `HasOneField` lacked an override). _(#142, PR #156)_
- **`ViewField::make()` default label** changed from empty string to
  `null` so the constructor's `ucfirst($name)` fallback applies.
  _(#142, PR #156)_
- **`CurrencyField` accepts string input** from DECIMAL columns; non-
  numeric values render as `-` instead of crashing `number_format()`.
  _(#143, PR #158)_

### Tooling

- **PHPStan configuration synchronised** — `composer analyse` reads its
  level from `phpstan.neon` (single source of truth), with `--memory-limit`
  raised. _(#118, PR #146)_
- **PHPStan stubs for optional dependencies** (`PragmaRX/Google2FA`,
  `BaconQrCode`) replace the broad regex-ignores that were hiding real
  errors. _(#145, PR #165)_
- **CI Code Quality workflow no longer fails on soft warnings.**
  `--warning-severity=0` keeps the line-length warnings visible locally
  while letting CI distinguish actual errors. _(PR #157)_
- **`composer.json` lint scripts** exclude `Generic.Files.LineLength` (PSR-12
  soft limit). `composer lint:fix` ran across `src/` for six pre-existing
  blank-line-end-of-block and parenthesis fixes.

### Closed without change (audit-only)

- **#125** BulkAction per-record auth — depends on `scopeQuery()` (#168);
  the bulk endpoints can opt-in to the same scope once that ships.
- **#128** Onnodige reload na save — false alarm after inspection.
- **#132** N+1 in `RowFormatter` — real issue lives in relation-field
  `setValue()`; spun off as **#160** and fixed in PR #162.
- **#133** Vite code splitting — no-op (only Alpine is in the bundle; the
  editor is loaded from a CDN on demand).
- **#141** CSV exporter extract — already separated between
  `BuildoraExportController` and `ExportManager` before this batch.

[Unreleased]: https://github.com/ginkelsoft-development/buildora/compare/main...develop
