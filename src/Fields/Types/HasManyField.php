<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a HasMany relationship field in a resource.
 */
class HasManyField extends Field
{
    protected ?string $relatedModel = null;
    protected string $returnColumn = 'id';
    protected string $displayColumn = 'name';
    protected ?Model $parentModel = null;

    /**
     * HasManyField constructor.
     *
     * @param string $name The field name.
     * @param string|null $label Optional label.
     * @param string $type Field type, defaults to 'hasMany'.
     */
    public function __construct(string $name, ?string $label = null, string $type = 'hasMany')
    {
        parent::__construct($name, $label ?? ucfirst($name), $type);
    }

    /**
     * Static factory method.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     * @return self
     */
    public static function make(string $name, ?string $label = null, string $type = 'hasMany'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Manually set the related model class name.
     *
     * @param string $model
     * @return self
     */
    public function relatedTo(string $model): self
    {
        $this->relatedModel = $model;
        return $this;
    }

    /**
     * Set the parent model so we can resolve the relation method dynamically.
     *
     * @param Model $parentModel
     * @return self
     */
    public function setParentModel(Model $parentModel): self
    {
        $this->parentModel = $parentModel;
        return $this;
    }

    /**
     * Resolve the related model class name.
     *
     * @return string
     * @throws BuildoraException
     */
    public function getRelatedModel(): string
    {
        if ($this->relatedModel) {
            return $this->relatedModel;
        }

        if ($this->parentModel && method_exists($this->parentModel, $this->name)) {
            return get_class($this->parentModel->{$this->name}()->getRelated());
        }

        throw new BuildoraException("HasManyField: Related model for '{$this->name}' not found.");
    }

    /**
     * Define which columns should be used in pluck (key/value).
     *
     * @param string $returnColumn
     * @param string $displayColumn
     * @return self
     */
    public function pluck(string $returnColumn, string $displayColumn): self
    {
        $this->returnColumn = $returnColumn;
        $this->displayColumn = $displayColumn;
        return $this;
    }

    /**
     * Set the field value by retrieving related model values.
     *
     * @param mixed $model
     * @return self
     */
    public function setValue(mixed $model): self
    {
        if ($model instanceof Model && method_exists($model, $this->name) && $model->exists) {
            $relation = $model->{$this->name}();

            if ($relation instanceof HasMany) {
                $table = (new ($this->getRelatedModel()))->getTable();

                $this->value = $relation
                    ->pluck("{$table}.{$this->displayColumn}", "{$table}.{$this->returnColumn}")
                    ->toArray();
            } else {
                $this->value = [];
            }
        } else {
            $this->value = [];
        }

        return $this;
    }

    /**
     * Get all available options for this field (e.g. for multi-select UI).
     *
     * @return array
     * @throws BuildoraException
     */
    public function getOptions(): array
    {
        if ($this->parentModel && method_exists($this->parentModel, $this->name)) {
            return $this->parentModel->{$this->name}()
                ->pluck($this->displayColumn, $this->returnColumn)
                ->toArray();
        }

        $relatedModel = $this->getRelatedModel();

        return $relatedModel::query()
            ->pluck($this->displayColumn, $this->returnColumn)
            ->toArray();
    }
}
