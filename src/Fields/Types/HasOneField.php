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
     * Static factory for HasOneField.
     *
     * Field::make() uses `new self()` (not `new static()`), so the inherited
     * factory would otherwise return a base Field instance rather than a
     * HasOneField. We override here to keep callers' chained type-specific
     * methods (relatedTo, setParentModel) working.
     *
     * Signature mirrors the other field factories so callers don't need to
     * special-case HasOneField; the $type argument is intentionally ignored
     * (the constructor always sets 'hasOne').
     *
     * @param string $name
     * @param string|null $label
     * @param string $type Unused; kept for API consistency with other fields.
     */
    public static function make(string $name, ?string $label = null, string $type = 'hasOne'): self
    {
        return new self($name, $label);
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
