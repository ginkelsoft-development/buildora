<?php

namespace Ginkelsoft\Buildora;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Traits\ForwardsCalls;

class BuildoraQueryBuilder
{
    use ForwardsCalls;

    protected Builder $query;
    protected string $resourceClass;

    /**
     * Create a new BuildoraQueryBuilder instance.
     *
     * @param Builder $query
     * @param string $resourceClass
     */
    public function __construct(Builder $query, string $resourceClass)
    {
        $this->query = $query;
        $this->resourceClass = $resourceClass;
    }

    /**
     * Retrieve all models as resources.
     *
     * @return object|null
     */
    public function all(): ?object
    {
        $model = $this->query->all();
        return $model ? $this->convertModelToResource($model) : null;
    }

    /**
     * Find a model by its primary key and return it as a resource.
     *
     * @param mixed $id
     * @return object|null
     */
    public function find(mixed $id): ?object
    {
        $model = $this->query->find($id);
        return $model ? $this->convertModelToResource($model) : null;
    }

    /**
     * Get all models as an array of resources.
     *
     * @return array
     */
    public function get(): array
    {
        $models = $this->query->get();
        return $this->convertModelsToResources($models);
    }

    /**
     * Get the first model as a resource.
     *
     * @return object|null
     */
    public function first(): ?object
    {
        $model = $this->query->first();
        return $model ? $this->convertModelToResource($model) : null;
    }

    /**
     * Get the last model (by primary key descending) as a resource.
     *
     * @return object|null
     */
    public function last(): ?object
    {
        $primaryKey = $this->query->getModel()->getKeyName();
        $model = $this->query->orderBy($primaryKey, 'desc')->first();
        return $model ? $this->convertModelToResource($model) : null;
    }

    /**
     * Paginate the results and convert models to resources.
     *
     * @param int $perPage
     * @param string $pageName
     * @param int|null $page
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 10, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $page = is_numeric($page) ? (int) $page : (int) request()->input($pageName, 1);
        $queryResults = $this->query->paginate($perPage, ['*'], $pageName, $page);
        $resources = $this->convertModelsToResources($queryResults->items());
        $queryResults->setCollection(collect($resources));
        return $queryResults;
    }

    /**
     * Proxy method calls to the underlying query builder.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->forwardCallTo($this->query, $method, $parameters);
    }

    /**
     * Convert an array of models to resources.
     *
     * @param iterable $models
     * @return array
     */
    protected function convertModelsToResources(iterable $models): array
    {
        $resources = [];
        foreach ($models as $model) {
            $resources[] = $this->convertModelToResource($model);
        }
        return $resources;
    }

    /**
     * Convert a single model to a resource.
     *
     * @param mixed $model
     * @return object
     */
    protected function convertModelToResource(mixed $model): object
    {
        $resource = call_user_func([$this->resourceClass, 'make']);
        $resource->fill($model);
        return $resource;
    }
}
