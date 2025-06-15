<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a BelongsToMany relation field for Buildora forms and datatables.
 */
class BelongsToManyField extends Field
{
    protected ?string $relatedModel = null;
    public string $returnColumn = 'id';
    public string $displayColumn = 'name';
    protected ?Model $parentModel = null;

    /**
     * BelongsToManyField constructor.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     */
    public function __construct(string $name, ?string $label = null, string $type = 'belongsToMany')
    {
        parent::__construct($name, $label ?? ucfirst($name), $type);
    }

    /**
     * Factory method to instantiate the field.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     * @return self
     */
    public static function make(string $name, ?string $label = null, string $type = 'belongsToMany'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Manually define the related model class.
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
     * Set the parent model to automatically infer the relation.
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
     * Resolve and return the related model class.
     *
     * @return string
     *
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

        throw new BuildoraException("BelongsToManyField: Related model for '{$this->name}' not found.");
    }

    /**
     * Define which columns are used for value and label.
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
     * Set the selected values from the model relation.
     *
     * @param mixed $model
     * @return self
     */
    public function setValue(mixed $model): self
    {
        if ($model instanceof Model && method_exists($model, $this->name) && $model->exists) {
            $relation = $model->{$this->name}();

            if ($relation instanceof BelongsToMany) {
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
     * Retrieve all available options from the related model.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        $relatedModel = $this->getRelatedModel();
        $table = (new $relatedModel)->getTable();

        return $relatedModel::query()
            ->pluck("{$table}.{$this->displayColumn}", "{$table}.{$this->returnColumn}")
            ->toArray();
    }
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->getOptions(),
            'returnColumn' => $this->returnColumn,
            'displayColumn' => $this->displayColumn,
            'relatedModel' => $this->relatedModel,
        ]);
    }
}
