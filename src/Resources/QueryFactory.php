<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\BuildoraQueryBuilder;
use Ginkelsoft\Buildora\Resources\BuildoraResource;

/**
 * Class QueryFactory
 *
 * Factory to create a BuildoraQueryBuilder for a given resource.
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
        $query = $resource->getModelInstance()->newQuery();
        return new BuildoraQueryBuilder($query, $resource::class);
    }
}
