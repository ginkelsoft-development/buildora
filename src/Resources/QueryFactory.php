<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\BuildoraQueryBuilder;

/**
 * Class QueryFactory
 *
 * Factory to create a BuildoraQueryBuilder for a given resource.
 * Can optionally eager-load defined panel relations for detail views.
 */
class QueryFactory
{
    /**
     * Create a new BuildoraQueryBuilder instance for the given resource.
     *
     * @param BuildoraResource $resource
     * @param bool $eagerLoadRelations Whether to eager-load panel relations (default: false for performance)
     * @return BuildoraQueryBuilder
     */
    public static function make(BuildoraResource $resource, bool $eagerLoadRelations = false): BuildoraQueryBuilder
    {
        // Maak de basisquery voor het model van deze resource
        $query = $resource->getModelInstance()->newQuery();

        // ✅ Eager load relaties alleen wanneer expliciet gevraagd (bijv. detail view)
        if ($eagerLoadRelations && method_exists($resource, 'getRelationResources')) {
            $relations = collect($resource->getRelationResources())
                ->pluck('relationName')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (!empty($relations)) {
                $query->with($relations);
            }
        }

        return new BuildoraQueryBuilder($query, $resource::class);
    }
}
