<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\BuildoraQueryBuilder;

/**
 * Class QueryFactory
 *
 * Factory to create a BuildoraQueryBuilder for a given resource,
 * automatically eager-loading defined panel relations.
 */
class QueryFactory
{
    /**
     * Create a new BuildoraQueryBuilder instance for the given resource.
     *
     * @param BuildoraResource $resource
     * @return BuildoraQueryBuilder
     */
    public static function make(BuildoraResource $resource): BuildoraQueryBuilder
    {
        // Maak de basisquery voor het model van deze resource
        $query = $resource->getModelInstance()->newQuery();

        // ✅ Automatisch eager loaden van relaties uit definePanels()
        if (method_exists($resource, 'getRelationResources')) {
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
