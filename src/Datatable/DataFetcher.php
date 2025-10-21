<?php

namespace Ginkelsoft\Buildora\Datatable;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Ginkelsoft\Buildora\Fields\Field;
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
     * @return LengthAwarePaginator The paginated result set.
     */
    public function fetch(
        string $search = '',
        string $sortBy = '',
        string $sortDirection = 'asc',
        int $perPage = 25,
        int $page = 1
    ): LengthAwarePaginator {
        /** @var Builder $query */
        $query = call_user_func([$this->resourceClass, 'query']);

        /** @var Model $modelInstance */
        $modelInstance = call_user_func([$this->resourceClass, 'make'])->getModelInstance();
        $connectionName = $modelInstance->getConnectionName();
        $databaseColumns = Schema::connection($connectionName)->getColumnListing($modelInstance->getTable());

        // 🔎 Apply search conditions
        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search, $databaseColumns) {
                foreach ($this->columns as $field) {
                    if (! $field instanceof Field || ! $field->isSearchable()) {
                        continue;
                    }

                    $column = $field->getSearchColumn();

                    if (str_contains($column, '.')) {
                        [$relation, $relColumn] = explode('.', $column, 2);
                        $q->orWhereHas($relation, fn ($sub) => $sub->where($relColumn, 'like', "%{$search}%"));
                    } elseif (in_array($column, $databaseColumns)) {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        if (!empty($sortBy)) {
            $sortColumn = collect($this->columns)->first(fn ($col) =>
                (is_array($col) ? ($col['name'] ?? null) : $col) === $sortBy
            );

            $sortColumnName = is_array($sortColumn)
                ? ($sortColumn['search_column'] ?? $sortColumn['name'] ?? null)
                : $sortBy;

            if (in_array($sortColumnName, $databaseColumns)) {
                $query->orderBy($sortColumnName, $sortDirection);
            }
        }

        return $query->paginate($perPage, 'page', $page);
    }
}
