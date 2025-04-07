<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a HasOne relationship field in a Buildora resource.
 */
class HasOneField extends Field
{
    /**
     * The related model class name (optional).
     *
     * @var string|null
     */
    protected ?string $relatedModel = null;

    /**
     * The parent model instance (used to resolve relationships).
     *
     * @var Model|null
     */
    protected ?Model $parentModel = null;

    /**
     * Create a new HasOneField instance.
     *
     * @param string $name The name of the relation/method.
     * @param string|null $label The label shown in UI.
     */
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label ?? ucfirst($name), 'hasOne');
    }

    /**
     * Set the related model class manually.
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
     * Set the parent model so that the related model can be auto-resolved.
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
     * Resolve the related model class.
     *
     * @return string
     * @throws BuildoraException If the relationship cannot be resolved.
     */
    public function getRelatedModel(): string
    {
        if ($this->relatedModel) {
            return $this->relatedModel;
        }

        if ($this->parentModel && method_exists($this->parentModel, $this->name)) {
            return get_class($this->parentModel->{$this->name}()->getRelated());
        }

        throw new BuildoraException("HasOneField: Related model for '{$this->name}' not found.");
    }
}
