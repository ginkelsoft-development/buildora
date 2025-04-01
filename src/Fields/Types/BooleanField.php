<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * A specialized field type for handling boolean values such as toggles or checkboxes.
 */
class BooleanField extends Field
{
    /**
     * Create a new BooleanField instance.
     *
     * @param string $name The attribute name on the model (default: 'is_active').
     * @param string|null $label The display label for the field (default: 'Active').
     * @param string $type The field type identifier (default: 'boolean').
     */
    public function __construct(string $name = 'is_active', ?string $label = 'Active', string $type = 'boolean')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable();
    }

    /**
     * Factory method to create a new BooleanField instance.
     *
     * @param string $name The attribute name on the model.
     * @param string|null $label The label for the field.
     * @param string $type The field type.
     * @return self
     */
    public static function make(string $name = 'is_active', ?string $label = 'Active', string $type = 'boolean'): self
    {
        return new self($name, $label, $type);
    }
}
