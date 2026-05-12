<?php

namespace Ginkelsoft\Buildora\Resources\Concerns;

use Ginkelsoft\Buildora\BuildoraQueryBuilder;
use Ginkelsoft\Buildora\Resources\QueryFactory;

/**
 * Static query entry points, extracted from BuildoraResource as the third
 * decomposition step for #135.
 *
 * The two methods here are intentionally minimal — they forward to
 * QueryFactory, which is where the actual list/detail split lives. Putting
 * them in a trait keeps the resource-level call sites (`Order::query()`,
 * `Order::queryWithRelations()`) intact while taking the surface out of
 * BuildoraResource.
 */
trait HasResourceQuery
{
    /**
     * Build a query for *list* contexts (datatable, exports, picklists).
     * Panel relations are NOT eager-loaded for performance — see
     * QueryFactory::forList() vs forDetail() / make() semantics.
     */
    public static function query(): BuildoraQueryBuilder
    {
        return QueryFactory::make(new static(), false);
    }

    /**
     * Build a query for the detail/show view. Panel relations declared by
     * getRelationResources() are eager-loaded.
     */
    public static function queryWithRelations(): BuildoraQueryBuilder
    {
        return QueryFactory::make(new static(), true);
    }
}
