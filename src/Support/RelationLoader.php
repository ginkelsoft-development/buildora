<?php

namespace Ginkelsoft\Buildora\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Picks the already-loaded relation off a model when possible, and falls
 * back to a fresh query only when nothing was eager-loaded.
 *
 * Every relation field (HasMany, BelongsToMany, BelongsTo, HasOne) used to
 * do `$model->relation()->pluck(...)` or `$relation->getResults()` —
 * both of those bypass the in-memory cache that Eloquent populates when
 * the caller does `Model::with('relation')->get()` or `$collection->
 * loadMissing('relation')` upstream. The result was a quiet N+1: 25 rows
 * in a datatable with a HasMany column = 25 extra queries, even with
 * eager-loading set up correctly.
 *
 * Going through this loader makes the intent explicit: prefer the loaded
 * cache, fall back to a query, and let DataFetcher / BuildoraDatatable
 * upstream do the batch load.
 */
final class RelationLoader
{
    /**
     * Get a Collection for a *-to-many relation. Returns an empty Collection
     * when the relation is missing or the model isn't persisted.
     */
    public static function manyFor(Model $model, string $relationName): Collection
    {
        if (! $model->exists || ! method_exists($model, $relationName)) {
            return new Collection();
        }

        if ($model->relationLoaded($relationName)) {
            $loaded = $model->getRelation($relationName);
            return $loaded instanceof Collection ? $loaded : Collection::wrap($loaded);
        }

        $relation = $model->{$relationName}();
        return Collection::wrap($relation->get());
    }

    /**
     * Get a single related model for a *-to-one relation. Returns null when
     * the relation is missing, the model isn't persisted, or no related row
     * exists.
     */
    public static function oneFor(Model $model, string $relationName): ?Model
    {
        if (! $model->exists || ! method_exists($model, $relationName)) {
            return null;
        }

        if ($model->relationLoaded($relationName)) {
            $loaded = $model->getRelation($relationName);
            return $loaded instanceof Model ? $loaded : null;
        }

        $relation = $model->{$relationName}();
        $result = $relation->getResults();

        return $result instanceof Model ? $result : null;
    }
}
