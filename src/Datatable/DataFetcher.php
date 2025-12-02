<?php

namespace Ginkelsoft\Buildora\Datatable;

use Ginkelsoft\Buildora\Support\SchemaCache;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\BelongsToField;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DataFetcher
 *
 * Responsible for fetching data for Buildora datatables based on search, sort, and pagination.
 */
class DataFetcher
{
    /**
     * The Buildora resource class name.
     *
     * @var class-string
     */
    protected string $resourceClass;

    /**
     * The datatable column definitions (Buildora Fields).
     *
     * @var array<int, Field>
     */
    protected array $columns;

    /**
     * DataFetcher constructor.
     *
     * @param class-string $resourceClass
     * @param array<int, Field> $columns
     */
    public function __construct(string $resourceClass, array $columns)
    {
        $this->resourceClass = $resourceClass;
        $this->columns = $columns;
    }

    /**
     * Fetch paginated results with search and sorting applied.
     *
     * @param string $search The search query.
     * @param string $sortBy The column to sort by.
     * @param string $sortDirection The sort direction (asc|desc).
     * @param int $perPage The number of items per page.
     * @param int $page The current page.
     * @return PaginatorContract The paginated result set.
     */
    public function fetch(
        string $search = '',
        string $sortBy = '',
        string $sortDirection = 'asc',
        int $perPage = 25,
        int $page = 1
    ): PaginatorContract {
        $query = call_user_func([$this->resourceClass, 'query']);

        /** @var Model $modelInstance */
        $modelInstance = call_user_func([$this->resourceClass, 'make'])->getModelInstance();
        $connectionName = $modelInstance->getConnectionName();

        // ✅ OPTIMIZATION 1: Use cached schema instead of querying database
        $databaseColumns = SchemaCache::getColumnListing($modelInstance->getTable(), $connectionName);

        // ✅ OPTIMIZATION 2: Auto eager-load BelongsTo relations to prevent N+1
        $this->eagerLoadBelongsToRelations($query);

        // 🔎 OPTIMIZATION 3: Apply search conditions with optimized joins
        if (!empty($search)) {
            $relationsToJoin = [];

            // First pass: identify which relations need joins
            foreach ($this->columns as $field) {
                if (! $field instanceof Field || ! $field->isSearchable()) {
                    continue;
                }

                $column = $field->getSearchColumn();
                if (str_contains($column, '.')) {
                    [$relation, $relColumn] = explode('.', $column, 2);
                    if (!isset($relationsToJoin[$relation])) {
                        $relationsToJoin[$relation] = [];
                    }
                    $relationsToJoin[$relation][] = $relColumn;
                }
            }

            // Apply joins for relation searches (more efficient than whereHas)
            foreach ($relationsToJoin as $relation => $columns) {
                if (method_exists($modelInstance, $relation)) {
                    $relationInstance = $modelInstance->{$relation}();
                    $relatedTable = $relationInstance->getRelated()->getTable();
                    $foreignKey = $relationInstance->getForeignKeyName();
                    $ownerKey = $relationInstance->getOwnerKeyName();

                    $query->leftJoin(
                        $relatedTable,
                        "{$modelInstance->getTable()}.{$foreignKey}",
                        '=',
                        "{$relatedTable}.{$ownerKey}"
                    );
                }
            }

            // Apply search conditions
            $query->where(function ($q) use ($search, $databaseColumns, $relationsToJoin, $modelInstance) {
                foreach ($this->columns as $field) {
                    if (! $field instanceof Field || ! $field->isSearchable()) {
                        continue;
                    }

                    $column = $field->getSearchColumn();

                    if (str_contains($column, '.')) {
                        [$relation, $relColumn] = explode('.', $column, 2);
                        if (isset($relationsToJoin[$relation])) {
                            $relationInstance = $modelInstance->{$relation}();
                            $relatedTable = $relationInstance->getRelated()->getTable();
                            $q->orWhere("{$relatedTable}.{$relColumn}", 'like', "%{$search}%");
                        }
                    } elseif (in_array($column, $databaseColumns)) {
                        $q->orWhere("{$modelInstance->getTable()}.{$column}", 'like', "%{$search}%");
                    }
                }
            });

            // Ensure we only get distinct results after joins
            $query->distinct();
        }

        // ✅ OPTIMIZATION 4: Qualified column names for sorting
        if (!empty($sortBy)) {
            $sortColumn = collect($this->columns)->first(fn ($col) =>
                (is_array($col) ? ($col['name'] ?? null) : $col) === $sortBy
            );

            $sortColumnName = is_array($sortColumn)
                ? ($sortColumn['search_column'] ?? $sortColumn['name'] ?? null)
                : $sortBy;

            if (in_array($sortColumnName, $databaseColumns)) {
                // Use qualified column name to avoid ambiguity with joins
                $query->orderBy("{$modelInstance->getTable()}.{$sortColumnName}", $sortDirection);
            }
        }

        return $query->paginate($perPage, 'page', $page);
    }

    /**
     * Automatically eager-load all BelongsTo relations from columns to prevent N+1 queries.
     *
     * @param Builder|\Ginkelsoft\Buildora\BuildoraQueryBuilder $query
     * @return void
     */
    protected function eagerLoadBelongsToRelations($query): void
    {
        $relations = [];

        foreach ($this->columns as $field) {
            if ($field instanceof BelongsToField) {
                // BelongsTo field name is usually the relation method name
                $relations[] = $field->name;
            }
        }

        if (!empty($relations)) {
            $query->with($relations);
        }
    }
}
