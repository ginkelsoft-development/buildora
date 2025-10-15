<?php

namespace Ginkelsoft\Buildora\Datatable;

use Ginkelsoft\Buildora\Datatable\DataFetcher;
use Ginkelsoft\Buildora\Datatable\RowFormatter;
use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Ginkelsoft\Buildora\Datatable\ColumnBuilder;
use Ginkelsoft\Buildora\Support\ResourceResolver;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class BuildoraDatatable
{
    public array $columns = [];
    protected array $data = [];
    protected array $pagination = [];
    protected bool $initialized = false;
    protected bool $isRelationMode = false;

    protected BuildoraResource $resource;
    protected string $paginationStrategy;

    public function __construct(BuildoraResource|string $resource)
    {
        $this->resource = is_string($resource)
            ? ResourceResolver::resolve($resource)
            : $resource;

        // Alleen kolommen bouwen, geen fetch!
        $this->columns = ColumnBuilder::build($this->resource);
        $this->paginationStrategy = Config::get('buildora.datatable.pagination_strategy', 'length_aware');
    }

    public static function fromRelation(Relation $relation, BuildoraResource $resource): self
    {
        $datatable = new self($resource); // Kolommen worden in __construct wel opgebouwd
        $datatable->fetchDataUsingRelation($relation);
        $datatable->isRelationMode = true;
        return $datatable;
    }

    public function fetchDataUsingRelation(Relation $relation): void
    {
        $perPage = (int) Request::input('per_page', Config::get('buildora.datatable.default_per_page'));
        if ($perPage < 1) {
            $perPage = (int) Config::get('buildora.datatable.default_per_page', 25);
        }
        $page = (int) Request::input('page', 1);

        // ✅ PERFORMANCE: Select only columns needed for table display
        $selectColumns = $this->getSelectColumnsForRelation($relation);

        $paginator = $this->paginationStrategy === 'simple'
            ? $relation->select($selectColumns)->simplePaginate($perPage)
            : $relation->select($selectColumns)->paginate($perPage, ['*'], 'page', $page);

        // ✅ OPTIMIZATION 5: Reuse resource fields instead of cloning entire resource
        $this->data = $this->formatRecords($paginator->items());

        $this->columns = ColumnBuilder::build($this->resource);
        $this->initialized = true;

        $this->pagination = $this->buildPaginationMeta($paginator, $perPage, $page);
    }

    protected function initialize(
        string $search = '',
        string $sortBy = '',
        string $sortDirection = 'asc',
        int $perPage = null,
        int $page = 1
    ): void {
        if ($this->initialized || $this->isRelationMode) {
            return;
        }

        $this->fetchData($search, $sortBy, $sortDirection, $perPage, $page);
        $this->initialized = true;
    }

    protected function fetchData(
        string $search = '',
        string $sortBy = '',
        string $sortDirection = 'asc',
        int $perPage = null,
        int $page = 1
    ): void {
        $perPage = (int) ($perPage ?? Request::input('per_page', Config::get('buildora.datatable.default_per_page')));
        if ($perPage < 1) {
            $perPage = (int) Config::get('buildora.datatable.default_per_page', 25);
        }
        $page = (int) Request::input('page', $page);

        $fields = $this->resource->resolveFields($this->resource->getModelInstance());
        $fetcher = new DataFetcher(get_class($this->resource), $fields);
        $paginator = $fetcher->fetch($search, $sortBy, $sortDirection, $perPage, $page);

        // ✅ OPTIMIZATION 5: Use optimized record formatting
        $this->data = $this->formatRecords($paginator->items());

        $this->pagination = $this->buildPaginationMeta($paginator, $perPage, $page);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getJsonResponse(
        string $search = '',
        string $sortBy = '',
        string $sortDirection = 'asc',
        int $perPage = null,
        int $page = 1
    ): array {
        $page = Request::input('page', $page);
        $perPage = Request::input('per_page', Config::get('buildora.datatable.default_per_page'));

        $this->initialize($search, $sortBy, $sortDirection, $perPage, $page);

        return [
            'data' => $this->data,
            'columns' => $this->getColumns(),
            'pagination' => $this->pagination,
            'bulk_actions' => array_map(fn($action) => $action->toArray([]), $this->resource->getBulkActions()),
            'pagination_options' => Config::get('buildora.datatable.pagination'),
        ];
    }

    public function getSearchableColumns(): array
    {
        if (method_exists($this->resource, 'getSearchableColumns')) {
            return $this->resource->getSearchableColumns();
        }

        return $this->getColumns();
    }

    /**
     * Get columns to select for relation queries (only table-visible fields).
     *
     * @param Relation $relation
     * @return array
     */
    protected function getSelectColumnsForRelation(Relation $relation): array
    {
        $table = $relation->getRelated()->getTable();
        $columns = ["{$table}.*"]; // Fallback to all columns
        $selectColumns = [];

        foreach ($this->resource->getFields() as $field) {
            // Only process fields visible in table
            if (!($field->visibility['table'] ?? false)) {
                continue;
            }

            // For BelongsTo, we need the foreign key
            if ($field instanceof \Ginkelsoft\Buildora\Fields\Types\BelongsToField) {
                continue; // Will be eager loaded
            }

            $selectColumns[] = $field->name;
        }

        // Always include id and timestamps
        $finalColumns = array_unique(array_merge(
            ["{$table}.id", "{$table}.created_at", "{$table}.updated_at"],
            array_map(fn($col) => "{$table}.{$col}", $selectColumns)
        ));

        return !empty($selectColumns) ? $finalColumns : ["{$table}.*"];
    }

    /**
     * Format records efficiently with minimal resource cloning.
     *
     * @param array $records
     * @return array
     */
    protected function formatRecords(array $records): array
    {
        // ✅ PERFORMANCE: Reduce overhead by caching row actions template
        $rowActionsTemplate = $this->resource->getRowActions($this->resource);

        $formatted = [];
        foreach ($records as $record) {
            $this->resource->fill($record);
            $formatted[] = RowFormatter::format($this->resource, $this->resource);
        }

        return $formatted;
    }

    protected function buildPaginationMeta($paginator, int $perPage, int $fallbackPage = 1): array
    {
        $currentPage = method_exists($paginator, 'currentPage')
            ? (int) $paginator->currentPage()
            : max(1, $fallbackPage);

        $perPage = method_exists($paginator, 'perPage')
            ? (int) $paginator->perPage()
            : max(1, $perPage);

        $total = method_exists($paginator, 'total')
            ? (int) $paginator->total()
            : null;

        $hasMore = method_exists($paginator, 'hasMorePages')
            ? (bool) $paginator->hasMorePages()
            : ($total !== null ? $currentPage * $perPage < $total : false);

        $lastPage = method_exists($paginator, 'lastPage')
            ? (int) $paginator->lastPage()
            : ($hasMore ? $currentPage + 1 : $currentPage);

        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => max(1, $currentPage),
            'last_page' => max(1, $lastPage),
            'has_more' => $hasMore,
        ];
    }
}
