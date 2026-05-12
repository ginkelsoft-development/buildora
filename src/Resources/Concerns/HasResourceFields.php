<?php

namespace Ginkelsoft\Buildora\Resources\Concerns;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Resources\FieldManager;
use Illuminate\Database\Eloquent\Model;

/**
 * Field-management surface, extracted from BuildoraResource for #135.
 *
 * The trait covers the four non-abstract methods that read/write the
 * resource's field collection. The abstract defineFields() hook itself
 * stays on BuildoraResource — subclasses must implement it, and an
 * abstract method declared via a trait is harder to discover (PHPStorm
 * doesn't surface it as "implement" candidate when you extend the class).
 *
 * State touched by this trait:
 *   - $this->fields            (collection of Field instances)
 *   - $this->parentModel       (set by fill())
 *
 * Both are protected properties declared on BuildoraResource. PHP allows
 * trait code to access them on the using class; nothing on this trait
 * requires re-declaration.
 */
trait HasResourceFields
{
    /**
     * Fill every field's value/displayValue from the given model. Used
     * before rendering a row in a datatable or detail view.
     */
    public function fill(Model $model): self
    {
        $this->parentModel = $model;

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

    /**
     * Replace the resource's fields. Validates that every entry is a
     * Field instance before assigning — guarantees no stray array/string
     * sneaks into the field pipeline.
     *
     * @param array<int, Field> $fields
     * @throws BuildoraException
     */
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
     * Return the current field collection. Performs the same Field-type
     * validation as setFields() — defensive, but the cost is negligible
     * compared with how often callers iterate the result.
     *
     * @return array<int, Field>
     * @throws BuildoraException
     */
    public function getFields(): array
    {
        $fields = $this->fields ?? [];

        foreach ($fields as $field) {
            if (! $field instanceof Field) {
                $type = is_object($field) ? get_class($field) : gettype($field);
                throw new BuildoraException(
                    "Ongeldig veld in " . static::class . ": verwacht een Field-object, kreeg {$type}"
                );
            }
        }

        return $fields;
    }

    /**
     * Re-prepare the field collection for a specific model. Delegates to
     * FieldManager::prepare so the field-type-specific setValue logic
     * stays in one place.
     *
     * @return array<int, Field>
     */
    public function resolveFields($model): array
    {
        return FieldManager::prepare($this->fields, $model);
    }
}
