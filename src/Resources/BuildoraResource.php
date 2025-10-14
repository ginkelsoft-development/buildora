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

    public function title(): string
    {
        return class_basename($this->modelClass);
    }

    /**
     * Configuratie voor zoekresultaten.
     *
     * @return array{label: string|array|callable, columns: string[]}
     */
    public function searchResultConfig(): array
    {
        return [
            'label' => ['voornaam', 'achternaam'],
            'columns' => ['voornaam', 'achternaam', 'emailadres'],
        ];
    }

    public function showInNavigation(): bool
    {
        return true;
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
    public function fill(Model $model): self
    {
        foreach ($this->fields as $field) {
            if (method_exists($field, 'setParentModel')) {
                $field->setParentModel($model);
            }

            if (method_exists($field, 'setValue')) {
                $field->setValue($model);
            } else {
                $field->value = $model->{$field->name} ?? null;
            }

            if (method_exists($field, 'getDisplayValue')) {
                $field->displayValue = $field->getDisplayValue($model);
            } else {
                $field->displayValue = $field->value;
            }
        }

        return $this;
    }

    public function setFields(array $fields): void
    {
        foreach ($fields as $field) {
            if (! $field instanceof Field) {
                $type = is_object($field) ? get_class($field) : gettype($field);
                throw new BuildoraException(
                    "Ongeldig veld in " . static::class . ": verwacht een Field-object, kreeg {$type}"
                );
            }
        }

        $this->fields = $fields;
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

    public function getFields(): array
    {
        $fields = $this->fields ?? [];

        foreach ($fields as $field) {
            if (! $field instanceof \Ginkelsoft\Buildora\Fields\Field) {
                $type = is_object($field) ? get_class($field) : gettype($field);
                throw new BuildoraException(
                    "Ongeldig veld in " . static::class . ": verwacht een Field-object, kreeg {$type}"
                );
            }
        }

        return $fields;
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
