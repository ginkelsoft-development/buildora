<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Represents a numeric input field.
 *
 * This field is sortable by default.
 */
class NumberField extends Field
{
    /**
     * Create a new NumberField instance.
     *
     * @param string $name The name of the field (default: 'amount').
     * @param string|null $label The display label for the field (default: 'Amount').
     * @param string $type The field type (default: 'number').
     */
    public function __construct(string $name = 'amount', ?string $label = 'Amount', string $type = 'number')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable();
    }

    /**
     * Static factory method for creating a new NumberField.
     *
     * @param string $name The name of the field.
     * @param string|null $label The label of the field.
     * @param string $type The input type.
     * @return self
     */
    public static function make(string $name = 'amount', ?string $label = 'Amount', string $type = 'number'): self
    {
        return new self($name, $label, $type);
    }
}
