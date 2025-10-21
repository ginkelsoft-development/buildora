<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BelongsToField
 *
 * Represents a "belongs to" relation field for use in forms and tables.
 */
class BelongsToField extends Field
{
    protected ?string $relatedModel = null;
    public string $returnColumn = 'id';
    public string $displayColumn = 'name';
    protected ?Model $parentModel = null;
    public bool $createInForm = false;

    /**
     * BelongsToField constructor.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     */
    public function __construct(string $name, ?string $label = null, string $type = 'belongsTo')
    {
        parent::__construct($name, $label ?? ucfirst($name), $type);
    }

    /**
     * Factory method to create a new BelongsToField instance.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     * @return self
     */
    public static function make(string $name, ?string $label = null, string $type = 'belongsTo'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Manually set the related model class.
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
     * Set the parent model for relation inference.
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
     * Get the related model class name, either explicitly set or inferred from parent model.
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

        throw new BuildoraException("BelongsToField: Related model for '{$this->name}' not found.");
    }

    /**
     * Set the return and display columns for the pluck.
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
     * Set the field value from a model instance (used when populating forms or tables).
     *
     * @param mixed $model
     * @return self
     */
    public function setValue(mixed $model): self
    {
        if ($model instanceof Model && method_exists($model, $this->name) && $model->exists) {
            $relation = $model->{$this->name}();

            if ($relation instanceof BelongsTo) {
                $relatedInstance = $relation->getResults();

                if ($relatedInstance) {
                    $this->value = $relatedInstance->{$this->displayColumn};
                } else {
                    $this->value = null;
                }
            } else {
                $this->value = null;
            }
        } else {
            $this->value = null;
        }

        return $this;
    }

    /**
     * Get a list of key-value options (id => label) from the related model.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        $relatedModel = $this->getRelatedModel();
        $table = (new $relatedModel())->getTable();

        return $relatedModel::query()
            ->pluck("{$table}.{$this->displayColumn}", "{$table}.{$this->returnColumn}")
            ->toArray();
    }
}
