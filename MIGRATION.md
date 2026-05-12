# Migration guide

Notes for consumers of `ginkelsoft/buildora` upgrading across the recent
review batch. **Everything in this batch is backwards compatible** —
you do not have to change any code to keep your application running.
This guide is for picking up the new safeguards and capabilities.

## Security: opt-in row-level authorization

Buildora now ships a `scopeQuery()` hook on `BuildoraResource`. The
default is a no-op, so existing resources keep their current behaviour
(any user with `{resource}.view` Spatie permission can see every row).

To enforce row-level access on a resource, override the hook:

```php
use Illuminate\Database\Eloquent\Builder;

class OrderBuildora extends BuildoraResource
{
    public function scopeQuery(Builder $query): Builder
    {
        return $query->where('account_id', auth()->user()->account_id);
    }

    // ...
}
```

The scope is applied by `QueryFactory` to every list, detail, export
and panel query — direct URL access (`/buildora/orders/123`) is also
filtered. A record outside the scope returns `null` from `find()`; the
detail page 404s.

## Security: opt-in MIME whitelist on `FileField`

The deny-list of executable extensions (`php`, `phar`, `jsp`, `htaccess`,
`exe`, …) is now **always active** — no consumer action required.

To narrow what your form accepts further, declare a MIME whitelist:

```php
FileField::make('avatar')
    ->allowedMimeTypes(['image/jpeg', 'image/png'])
    ->maxSize(2048);
```

The check is server-side via Laravel's `mimetypes:` rule (magic-byte
based, not spoofable via the request's `Content-Type` header). The
existing `accept()` builder still emits the HTML `accept` attribute as
a UX hint only.

## Security: `RichTextField` output sanitisation

WYSIWYG content stored against a model is now sanitised by the
`HtmlSanitizer` helper before being rendered by the `richtext` Blade
component. This is automatic; existing data continues to render, but
embedded `<script>`/`<iframe>`/event-handler attributes and dangerous
URL schemes are stripped.

If you render `RichTextField` content from a custom view (bypassing the
package's component), make the same call:

```blade
{!! \Ginkelsoft\Buildora\Support\HtmlSanitizer::clean($model->content) !!}
```

## Performance: global search tunables

Global search now skips empty and very-short terms (the previous
behaviour ran a `LIKE '%%'` full-scan on every keystroke). The defaults
are sensible; override in `config/buildora.php` if needed:

```php
'global_search' => [
    'min_term_length'    => 2,
    'limit_per_resource' => 5,
],
```

For large catalogues, also consider adding a FULLTEXT index on the
columns listed in each resource's `searchResultConfig()`, or swapping
the backend for Laravel Scout + Meilisearch/Typesense.

## Permissions: `BuildoraAbility` enum (optional)

The literal strings `'view'`, `'create'`, `'edit'`, `'delete'` used in
policies and commands are now backed by `BuildoraAbility`. Existing
Spatie permission rows keep working — the enum values are identical to
the previous strings.

If you write your own policies and want compile-time safety:

```php
use Ginkelsoft\Buildora\Authorization\BuildoraAbility;

// Old:
return $user->hasPermissionTo("{$resource}.view");

// New:
return $user->hasPermissionTo(BuildoraAbility::View->permissionString($resource));
```

## Resource class: traits (internal)

`BuildoraResource` now uses four traits for the previously-inline
hooks: `HasResourceActions`, `HasResourceNavigation`, `HasResourceQuery`,
`HasResourceFields`. **No consumer change required** — every public
method signature is identical and trait dispatch is invisible to
`$this->...` calls in your subclasses.

If you read the class for orientation, the structure changed:

- `defineFields()` — abstract, stays on `BuildoraResource`
- `define*()` action/widget hooks — in `HasResourceActions` /
  `defineWidgets()` still on the class
- `title()`/`slug()`/`searchResultConfig()` — in `HasResourceNavigation`
- `query()`/`queryWithRelations()` — in `HasResourceQuery`
- `fill()`/`getFields()`/`setFields()`/`resolveFields()` — in `HasResourceFields`

## Field type bug fixes worth knowing

- **`HasOneField::make('relation')`** now returns a `HasOneField`. Before
  this batch it returned a base `Field` instance; chained type-specific
  methods such as `->relatedTo()` would not be available on a `Field`
  type-hint and could fail in static analysers.
- **`ViewField::make('first_name')`** now produces the auto-derived
  label `"First_name"` instead of the empty string. If you depended on
  the empty label, pass an explicit `''` second argument.
- **`CurrencyField`** no longer crashes on string input from DECIMAL
  columns. Non-numeric values render as `-`.

## Export streaming

`Excel::download()` and CSV export are unchanged in usage. Internally
the package now uses chunked reading instead of materialising the whole
result set, so exports of large tables no longer hit PHP's
`memory_limit`. No consumer change required.

## Default `searchResultConfig()` returns Dutch field names

This is **not new** — the default has been `['voornaam', 'achternaam']`
since before this batch — but it's worth flagging because the
docs around `HasResourceNavigation` make it visible. Every consumer
resource that doesn't speak Dutch should already be overriding
`searchResultConfig()`. The default exists for the package's own
built-in `UserBuildora`.
