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

    public function __construct(BuildoraResource|string $resource)
    {
        $this->resource = is_string($resource)
            ? ResourceResolver::resolve($resource)
            : $resource;

        // Alleen kolommen bouwen, geen fetch!
        $this->columns = ColumnBuilder::build($this->resource);
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
        $perPage = Request::input('per_page', Config::get('buildora.datatable.default_per_page'));
        $page = Request::input('page', 1);

        $paginator = $relation->paginate($perPage, ['*'], 'page', $page);

        $this->data = array_map(function ($record) {
            $resource = clone $this->resource;
            $resource->fill($record);
            return RowFormatter::format($resource, $this->resource);
        }, $paginator->items());

        $this->columns = ColumnBuilder::build($this->resource);
        $this->initialized = true;

        $this->pagination = [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
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
        $perPage = $perPage ?? Request::input('per_page', Config::get('buildora.datatable.default_per_page'));
        $page = Request::input('page', $page);

        $fields = $this->resource->resolveFields($this->resource->getModelInstance());
        $fetcher = new DataFetcher(get_class($this->resource), $fields);
        $paginator = $fetcher->fetch($search, $sortBy, $sortDirection, $perPage, $page);

        $formattedRows = array_map(function ($record) {
            $resource = clone $this->resource;
            $resource->fill($record);
            return RowFormatter::format($resource, $this->resource);
        }, $paginator->items());

        $this->data = $formattedRows;
        $this->pagination = [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
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
}
