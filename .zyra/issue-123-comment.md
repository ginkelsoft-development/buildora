Reactie voor GitHub-issue #123 (ginkelsoft-development/buildora)
=================================================================

LET OP: Jelle heeft vanuit deze werkplaats geen GitHub-toegang (geen `gh`
CLI, geen credentials) en kan deze reactie dus niet zelf plaatsen. Plak
onderstaande tekst als comment op issue #123, bijvoorbeeld met:

    gh issue comment 123 --repo ginkelsoft-development/buildora --body-file .zyra/issue-123-comment.md

--- comment hieronder dit lijntje ---

## Technisch ontwerp: record-level authorization hook op `BuildoraResource`

### Probleem

`buildora.can:{action}` (middleware `CheckBuildoraPermission`, alias
`buildora.can`) checkt alleen de **resource-level** permission
`{resource}.{action}` (bv. `user.view`). Zodra een gebruiker die permission
heeft, kan hij via een directe URL (`/buildora/user/{id}`,
`/buildora/user/{id}/edit`, `PUT`/`DELETE` idem) **elk** record van dat
model opvragen/wijzigen/verwijderen — dit is de IDOR uit het issue.

Onderzocht (huidige codebase):
- `BuildoraController::show()` haalt op via
  `$resource::queryWithRelations()->findOrFail($id)` → loopt via
  `QueryFactory::make()`.
- `BuildoraController::edit()`, `update()`, `destroy()` halen op via
  `$resource->getModelInstance()->findOrFail($id)` / `->find($id)` →
  **bypassen** `QueryFactory` volledig, rechtstreeks op het Eloquent-model.
- `DataFetcher::fetch()` (lijst/datatable) en `ExportManager::make()`
  (export) gebruiken beide `{Resource}::query()` → lopen ook via
  `QueryFactory::make()`.

Conclusie: één hook in `QueryFactory` dekt lijst/detail/export, maar dekt
**niet** edit/update/destroy, omdat die nu buiten `QueryFactory` om gaan.
Vandaar de twee complementaire hooks uit de acceptatiecriteria.

### Hook 1 — `scopeQuery()` (query-scoping, dekt alle CRUD-paden)

**Signatuur, op `BuildoraResource` (`src/Resources/BuildoraResource.php`):**

```php
use Illuminate\Database\Eloquent\Builder;

/**
 * Beperk de basisquery van deze resource tot de records die de huidige
 * gebruiker/context mag zien. Default: geen beperking (backwards compatible).
 *
 * Wordt toegepast op alle query-paden van deze resource: lijst/datatable,
 * export, en (via newScopedQuery()) op show/edit/update/destroy.
 *
 * @param Builder $query
 * @return Builder
 */
public function scopeQuery(Builder $query): Builder
{
    return $query;
}

/**
 * Nieuwe, aan deze resource gebonden Eloquent-query met scopeQuery()
 * toegepast. Gebruikt door controllers voor single-record lookups
 * (edit/update/destroy) waar een ruwe Model-instance nodig is (in
 * tegenstelling tot ::query(), dat Resource-objecten teruggeeft).
 */
public function newScopedQuery(): Builder
{
    return $this->scopeQuery($this->getModelInstance()->newQuery());
}
```

**Toepassing in `QueryFactory` (`src/Resources/QueryFactory.php`), direct na
het aanmaken van de basisquery, vóór eager-loading en vóór het wrappen in
`BuildoraQueryBuilder`:**

```php
public static function make(BuildoraResource $resource, bool $eagerLoadRelations = false): BuildoraQueryBuilder
{
    $query = $resource->getModelInstance()->newQuery();
    $query = $resource->scopeQuery($query); // ← nieuw

    if ($eagerLoadRelations && method_exists($resource, 'getRelationResources')) {
        // ... bestaande eager-load logica, ongewijzigd
    }

    return new BuildoraQueryBuilder($query, $resource::class);
}
```

Omdat `{Resource}::query()` en `::queryWithRelations()` beide via
`QueryFactory::make()` lopen, dekt dit automatisch:
- `DataFetcher::fetch()` → **lijst/datatable**
- `ExportManager::make()` → **export**
- `BuildoraController::show()` → **detail** (gebruikt al `queryWithRelations()`)

**Toepassing in `BuildoraController`
(`src/Http/Controllers/BuildoraController.php`) voor edit/update/destroy —
deze paden bypassen nu `QueryFactory` en moeten omgezet worden naar
`newScopedQuery()`:**

| Methode     | Huidige fetch                                            | Nieuwe fetch                              |
|-------------|-----------------------------------------------------------|--------------------------------------------|
| `edit()`    | `$resource->getModelInstance()->findOrFail($id)`          | `$resource->newScopedQuery()->findOrFail($id)` |
| `update()`  | `$modelInstance->findOrFail($id)`                          | `$resource->newScopedQuery()->findOrFail($id)` |
| `destroy()` | `$resource->getModelInstance()->find($id)`                 | `$resource->newScopedQuery()->find($id)`   |

`findOrFail()`/`find()` blijven ongewijzigd Eloquent-Builder-methoden
(geretourneerd object is nog steeds een `Model`, niet een `BuildoraResource`
— dat blijft nodig voor `$item->update()`/`$item->delete()`), dus geen
verdere aanpassing in de rest van deze methoden nodig.

**Effect**: zodra een resource `scopeQuery()` overschrijft, geeft een niet
toegankelijk record overal een **404** (via `findOrFail`), consistent met
hoe Laravel al met "niet-bestaande" records omgaat. Met de default
(no-op) is de query exact gelijk aan de huidige — dus **geen
gedragsverandering** voor bestaande resources.

### Hook 2 — `policy()` (Laravel Policy / Gate, expliciete 403)

Voor teams die liever met standaard Laravel Policies werken (of een
duidelijker 403 willen i.p.v. 404), een tweede, onafhankelijke hook.

**Signatuur, op `BuildoraResource`:**

```php
/**
 * Naam van de Gate-ability die gecontroleerd wordt vóór show/edit/update/
 * destroy van een specifiek record. Default: null = geen extra check
 * (backwards compatible).
 *
 * Voorbeeld: return 'view'; roept Gate::authorize('view', $model) aan,
 * wat UserPolicy::view(User $authUser, User $model) aanroept via
 * Laravel's standaard policy-discovery/registratie.
 *
 * @return string|null
 */
public function policy(): ?string
{
    return null;
}
```

**Toepassing in `BuildoraController`** — nieuwe protected helper:

```php
use Illuminate\Support\Facades\Gate;

protected function authorizeRecord(object $resource, Model $item): void
{
    $ability = $resource->policy();

    if ($ability !== null) {
        Gate::authorize($ability, $item);
    }
}
```

Aanroepen direct na het ophalen van `$item`, vóór verdere logica, in:
- `show()` — na `$item = $resource::queryWithRelations()->findOrFail($id);`
- `edit()` — na de (nieuwe) `newScopedQuery()->findOrFail($id)`
- `update()` — idem, vóór validatie/opslaan
- `destroy()` — na de null-check (`if (!$item) { ... }`), vóór `$item->delete()`

`Gate::authorize()` gooit een `AuthorizationException`, die Laravel
standaard naar een **403**-response vertaalt (net als de bestaande
`abort(403, ...)`-calls in `CheckBuildoraPermission`).

**Waarom niet ook op lijst/export?** Een Gate-check per rij op een lijst
van 25+ records is N+1 authorization calls en hoort qua verantwoordelijkheid
bij `scopeQuery()` (filteren op querylevel), niet bij `policy()` (single-record
gate). `policy()` is bewust beperkt tot de vier single-record endpoints.

### Combineren

Beide hooks zijn onafhankelijk en mogen los of samen gebruikt worden:
- Alleen `scopeQuery()`: alle paden (lijst/detail/export/edit/update/destroy)
  filteren op querylevel, ontoegankelijke records → 404.
- Alleen `policy()`: alleen de vier single-record endpoints, expliciete
  403 via een Laravel Policy-klasse.
- Beide: `scopeQuery()` voor de lijst/export, `policy()` voor een
  expliciete 403 op single-record endpoints (in plaats van de 404 die
  `scopeQuery()` daar ook al zou geven).

### Backwards compatibility (expliciet)

- `scopeQuery()` default: `return $query;` → query ongewijzigd →
  `newScopedQuery()` is dan functioneel identiek aan
  `getModelInstance()->newQuery()` → **geen gedragsverandering**.
- `policy()` default: `return null;` → `authorizeRecord()` doet niets →
  **geen gedragsverandering**.
- Bestaande resources die geen van beide overschrijven, blijven exact
  hetzelfde gedrag vertonen (geen nieuwe 403/404's, geen performance-impact
  behalve één extra, no-op methodeaanroep per query/record).

### Testscenario (ontwerp, voor de bouwtaak)

Voorstel: `tests/Feature/RecordAuthorizationTest.php`, met een simpel
testmodel + testresource (`owner_id`-kolom) en twee users A/B, beiden met
permission `testmodel.view`/`edit`/`delete`:

1. **Regressie (geen hook ingesteld)**: user A doet `GET
   /buildora/testmodel/{id-van-B}` → **200 OK** (huidig gedrag, ongewijzigd).
2. **`scopeQuery()` ingesteld** (filtert op `owner_id = auth()->id()`):
   - user A doet `GET /buildora/testmodel/{id-van-B}` → **404**.
   - user A doet `GET /buildora/testmodel/{eigen-id}` → **200 OK**.
   - user A doet `PUT`/`DELETE` op `{id-van-B}` → **404** (via
     `newScopedQuery()`).
3. **`policy()` ingesteld** (Policy-ability `view` → `owner_id ===
   auth()->id()`):
   - user A doet `GET /buildora/testmodel/{id-van-B}/edit` →
     **403** (`AuthorizationException`).
   - user A doet `GET /buildora/testmodel/{eigen-id}/edit` → **200 OK**.

Dit dekt beide acceptatiecriteria-opties en de backwards-compat-eis
(scenario 1) in één testklasse.

### CLAUDE.md-documentatievoorbeeld

Toe te voegen onder "Common Workflows" in `CLAUDE.md`, ná "Adding Relation
Panels":

```markdown
### Record-Level Authorization (Preventing IDOR)

By default, any user with the resource-level permission (e.g. `user.view`)
can view/edit/delete **any** record of that resource — Buildora only
checks the permission, not record ownership. To restrict access per
record, override one (or both) hooks on your resource:

**Option A — `scopeQuery()`**: filters every query for this resource
(list, export, show, edit, update, destroy). Inaccessible records
result in a 404.

\`\`\`php
public function scopeQuery(Builder $query): Builder
{
    return $query->where('owner_id', auth()->id());
}
\`\`\`

**Option B — `policy()`**: delegates to a standard Laravel Policy for
show/edit/update/destroy. Inaccessible records result in a 403.

\`\`\`php
public function policy(): ?string
{
    return 'view'; // calls Gate::authorize('view', $model)
}
\`\`\`

Both default to a no-op (unrestricted, current behavior) if not
overridden.
```

### Bekende, bewust buiten scope gelaten kanttekening

`RelationDatatableController::__invoke()` haalt het **parent**-record op
via `$resource->getModelInstance()->findOrFail($id)` (voor relatiepanelen
op de detailpagina), en gaat daarmee ook langs `QueryFactory` heen. Dat is
een vergelijkbaar IDOR-risico, maar valt buiten de scope van dit issue
(dat expliciet show/edit/update/destroy van `BuildoraController` noemt).
Voorstel: apart issue aanmaken om daar `newScopedQuery()` toe te passen
zodra deze hook gebouwd is.

### Samenvatting voor de bouwtaak

Bestanden om aan te passen:
1. `src/Resources/BuildoraResource.php` — `scopeQuery()`, `newScopedQuery()`, `policy()` toevoegen.
2. `src/Resources/QueryFactory.php` — `scopeQuery()` toepassen in `make()`.
3. `src/Http/Controllers/BuildoraController.php` — `authorizeRecord()` helper; `edit()`/`update()`/`destroy()` omzetten naar `newScopedQuery()`; `Gate::authorize()`-call in `show()`/`edit()`/`update()`/`destroy()`.
4. `tests/Feature/RecordAuthorizationTest.php` — nieuw, scenario's zoals hierboven.
5. `CLAUDE.md` — documentatiesectie zoals hierboven.

Geen wijzigingen nodig aan routes/middleware: `buildora.can:*` blijft de
resource-level check, deze hooks komen er onafhankelijk bovenop.
