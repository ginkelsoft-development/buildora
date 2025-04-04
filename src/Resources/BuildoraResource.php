<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\Actions\BulkAction;
use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Illuminate\Database\Eloquent\Model;
use Ginkelsoft\Buildora\Fields\Field;
use Exception;

/**
 * Abstract base class for all Buildora Resources.
 */
abstract class BuildoraResource
{
    protected string $modelClass;
    protected array $fields;
    protected ?string $detailView = null;

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

    /**
     * Create a new static instance of the resource.
     *
     * @return static
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * Fill the resource fields with values from the given model instance.
     *
     * @param Model $model
     * @return $this
     */
    public function fill($model): self
    {
        $this->fields = FieldManager::prepare($this->fields, $model);
        return $this;
    }

    /**
     * Define the row actions for this resource.
     *
     * @return array
     */
    public function defineRowActions(): array
    {
        return [];
    }

    /**
     * Define the bulk actions for this resource.
     *
     * @return array
     */
    public function defineBulkActions(): array
    {
        return [];
    }

    /**
     * Define the widgets for this resource (used on the dashboard).
     *
     * @return array
     */
    public function defineWidgets(): array
    {
        return [];
    }

    /**
     * Return the row actions for a specific resource instance.
     *
     * @param object $resource
     * @return array
     */
    public function getRowActions(object $resource): array
    {
        return ActionManager::resolveRowActions($this->defineRowActions(), $resource);
    }

    /**
     * Return the bulk actions, including default export actions.
     *
     * @return array
     */
    public function getBulkActions(): array
    {
        $custom = collect(static::defineBulkActions());
        $default = collect(\Ginkelsoft\Buildora\Exports\ExportManager::defaultBulkActions(static::slug()));

        return $custom
            ->keyBy(fn($a) => $a->label)
            ->union($default->keyBy(fn($a) => $a->getLabel()))
            ->values()
            ->toArray();
    }

    /**
     * Define all fields used in this resource.
     *
     * @return Field[]
     */
    abstract public function defineFields(): array;

    /**
     * Get all fields for this resource.
     *
     * @return Field[]
     */
    public function getFields(): array
    {
        return collect($this->fields)->map(function ($field) {
            if (! $field instanceof Field) {
                throw new Exception("Invalid field type in resource: " . get_class($field));
            }
            return $field;
        })->toArray();
    }

    /**
     * Prepare the fields with data from a specific model.
     *
     * @param Model $model
     * @return Field[]
     */
    public function resolveFields($model): array
    {
        return FieldManager::prepare($this->fields, $model);
    }

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
     * Generate a slug for the resource based on its class name.
     *
     * @return string
     */
    public static function slug(): string
    {
        return str_replace('buildora', '', strtolower(class_basename(static::class)));
    }

    /**
     * Return the query builder for this resource.
     *
     * @return \Ginkelsoft\Buildora\BuildoraQueryBuilder
     */
    public static function query(): \Ginkelsoft\Buildora\BuildoraQueryBuilder
    {
        return QueryFactory::make(new static());
    }

    public function setDetailView(string $view): static
    {
        $this->detailView = $view;
        return $this;
    }

    public function getDetailView(): ?string
    {
        return $this->detailView;
    }
}
