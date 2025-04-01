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

class BuildoraDatatable
{
    protected BuildoraResource $resource;

    public array $columns = [];
    protected array $data = [];
    protected array $pagination = [];
    protected bool $initialized = false;

    /**
     * BuildoraDatatable constructor.
     *
     * @param string $resourceName
     * @throws BuildoraException
     */
    public function __construct(string $resourceName)
    {
        $this->resource = ResourceResolver::resolve($resourceName);
        $this->columns = ColumnBuilder::build($this->resource);
    }

    /**
     * Initialize and fetch datatable state.
     *
     * @param string $search
     * @param string $sortBy
     * @param string $sortDirection
     * @param int|null $perPage
     * @param int $page
     * @return void
     */
    protected function initialize(string $search = '', string $sortBy = '', string $sortDirection = 'asc', int $perPage = null, int $page = 1): void
    {
        if ($this->initialized) {
            $this->fetchData($search, $sortBy, $sortDirection, $perPage, $page);
            return;
        }

        $this->fetchData($search, $sortBy, $sortDirection, $perPage, $page);
        $this->initialized = true;
    }

    /**
     * Fetch datatable records and apply pagination.
     *
     * @param string $search
     * @param string $sortBy
     * @param string $sortDirection
     * @param int|null $perPage
     * @param int $page
     * @return void
     */
    protected function fetchData(string $search = '', string $sortBy = '', string $sortDirection = 'asc', int $perPage = null, int $page = 1): void
    {
        $perPage = $perPage ?? Request::input('per_page', Config::get('buildora.datatable.default_per_page'));
        $page = Request::input('page', $page);

        $fields = $this->resource->resolveFields($this->resource->getModelInstance());
        $fetcher = new DataFetcher(get_class($this->resource), $fields);
        $paginator = $fetcher->fetch($search, $sortBy, $sortDirection, $perPage, $page);

        $formattedRows = array_map(fn($r) => RowFormatter::format($r, $this->resource), $paginator->items());

        $this->data = $formattedRows;
        $this->pagination = [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    /**
     * Get the visible datatable columns.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Get a JSON-ready array for the frontend.
     *
     * @param string $search
     * @param string $sortBy
     * @param string $sortDirection
     * @param int|null $perPage
     * @param int $page
     * @return array
     */
    public function getJsonResponse(string $search = '', string $sortBy = '', string $sortDirection = 'asc', int $perPage = null, int $page = 1): array
    {
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

    /**
     * Get all searchable columns from the resource.
     *
     * @return array
     */
    public function getSearchableColumns(): array
    {
        if (method_exists($this->resource, 'getSearchableColumns')) {
            return $this->resource->getSearchableColumns();
        }

        return $this->getColumns();
    }
}
