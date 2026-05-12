<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\BuildoraQueryBuilder;

/**
 * Class QueryFactory
 *
 * Builds a BuildoraQueryBuilder for a given resource, in one of two modes:
 *
 *  - forList()   — minimal: no panel relations eager-loaded. Used by the
 *                  datatable, exports and any other listing context where
 *                  the panels are never rendered. Without this distinction,
 *                  every datatable row would drag a tree of unused
 *                  relation rows behind it.
 *
 *  - forDetail() — full: eager-loads every relation declared via
 *                  getRelationResources() (the panels Buildora will render
 *                  on the detail page), so the page builds without N+1.
 */
class QueryFactory
{
    /**
     * Build a query for listing contexts (datatable, exports, picklists).
     * Panel relations are NOT eager-loaded.
     */
    public static function forList(BuildoraResource $resource): BuildoraQueryBuilder
    {
        return self::make($resource, false);
    }

    /**
     * Build a query for the detail/show view. Panel relations declared by
     * getRelationResources() are eager-loaded in a single batch.
     */
    public static function forDetail(BuildoraResource $resource): BuildoraQueryBuilder
    {
        return self::make($resource, true);
    }

    /**
     * Generic constructor. Prefer forList() or forDetail() at call sites —
     * they document the intent and avoid the boolean trap of a flag named
     * "$eagerLoadRelations" passed without a label.
     *
     * @param BuildoraResource $resource
     * @param bool $eagerLoadRelations Whether to eager-load panel relations.
     * @return BuildoraQueryBuilder
     */
    public static function make(BuildoraResource $resource, bool $eagerLoadRelations = false): BuildoraQueryBuilder
    {
        $query = $resource->getModelInstance()->newQuery();

        if ($eagerLoadRelations && method_exists($resource, 'getRelationResources')) {
            $relations = collect($resource->getRelationResources())
                ->pluck('relationName')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (! empty($relations)) {
                $query->with($relations);
            }
        }

        return new BuildoraQueryBuilder($query, $resource::class);
    }
}
