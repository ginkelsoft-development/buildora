<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\Actions\BulkAction;
use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Resources\Concerns\HasResourceActions;
use Ginkelsoft\Buildora\Resources\Concerns\HasResourceFields;
use Ginkelsoft\Buildora\Resources\Concerns\HasResourceNavigation;
use Ginkelsoft\Buildora\Resources\Concerns\HasResourceQuery;
use Illuminate\Database\Eloquent\Model;
use Ginkelsoft\Buildora\Fields\Field;
use Exception;

/**
 * Abstract base class for all Buildora Resources.
 *
 * Concerns are pulled into traits under Resources\\Concerns as part of #135.
 * This file should shrink over time; new methods belong in a trait by topic.
 */
abstract class BuildoraResource
{
    use HasResourceActions;
    use HasResourceFields;
    use HasResourceNavigation;
    use HasResourceQuery;

    protected ?Model $parentModel = null;
    protected string $modelClass;
    protected array $fields;
    protected ?string $detailView = null;
    protected array $relationResources = [];

    /**
     * BuildoraResource constructor.
     *
     * Initializes the model and prepares/validates the fields.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->modelClass = ModelResolver::resolve(static::class);
        $modelInstance = new $this->modelClass();

        $this->fields = FieldManager::prepare($this->defineFields(), $modelInstance);
        FieldValidator::validate($this->fields, $modelInstance);
    }

    // title(), searchResultConfig(), showInNavigation() live in
    // HasResourceNavigation (Resources\Concerns\HasResourceNavigation).

    /**
     * Create a new static instance of the resource.
     *
     * @return static
     */
    public static function make(): self
    {
        return new static();
    }

    // fill(), setFields(), getFields(), resolveFields() live in
    // HasResourceFields (Resources\Concerns\HasResourceFields).

    /**
     * Define the widgets for this resource (used on the dashboard).
     *
     * @return array
     */
    public function defineWidgets(): array
    {
        return [];
    }

    // Row/bulk/page action methods now live in the HasResourceActions trait
    // (Resources\Concerns\HasResourceActions). See #135 for the wider
    // decomposition plan.

    /**
     * Define all fields used in this resource.
     *
     * @return Field[]
     */
    abstract public function defineFields(): array;

    // (getFields() / resolveFields() moved to HasResourceFields trait.)

    /**
     * Return a new instance of the underlying model.
     *
     * @return Model
     */
    public function getModelInstance(): Model
    {
        return new $this->modelClass();
    }

    /**
     * Hook for scoping every Buildora-built query for this resource.
     *
     * The default returns the builder unchanged — Buildora keeps its current
     * behaviour of surfacing every row a user with the resource's `*.view`
     * permission has access to. Override in a subclass to enforce row-level
     * authorization, multi-tenant separation, soft-deleted visibility, etc.
     *
     *   public function scopeQuery(Builder $query): Builder
     *   {
     *       return $query->where('owner_id', auth()->id());
     *   }
     *
     * The scope is applied by QueryFactory for both list (query()) and
     * detail (queryWithRelations()) contexts, so a user cannot bypass it by
     * navigating to /resource/{id} directly — the model never resolves.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Model> $query
     * @return \Illuminate\Database\Eloquent\Builder<Model>
     */
    public function scopeQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query;
    }

    /**
     * Return the fully qualified model class name.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    // slug() lives in HasResourceNavigation.

    // query() and queryWithRelations() live in HasResourceQuery
    // (Resources\Concerns\HasResourceQuery).

    public function setDetailView(string $view): static
    {
        $this->detailView = $view;
        return $this;
    }

    public function getDetailView(): ?string
    {
        return $this->detailView;
    }

    public function setParentModel(Model $model): static
    {
        $this->parentModel = $model;
        return $this;
    }

    public function getParentModel(): ?Model
    {
        return $this->parentModel;
    }

    /**
     * Haal de relationele layouts (zoals panels of tabs) op.
     */
    public function getRelationResources(): array
    {
        return $this->relationResources ?: $this->definePanels();
    }

    /**
     * Overschrijf relationele layouts handmatig (bijv. via controller).
     */
    public function setRelationResources(array $resources): void
    {
        $this->relationResources = $resources;
    }

    public function definePanels(): array
    {
        return [];
    }

    public function uriKey(): string
    {
        return strtolower(str_replace('Buildora', '', class_basename(static::class)));
    }

    public function loadWithRelations(
        \Illuminate\Database\Eloquent\Builder $query
    ): \Illuminate\Database\Eloquent\Builder {
        $relations = collect($this->getRelationResources())
            ->pluck('relationName')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query;
    }
}
