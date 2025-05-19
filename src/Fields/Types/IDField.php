<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Represents a primary ID field for a resource.
 *
 * This field is typically hidden from the datatable and export,
 * and is marked as readonly.
 */
class IDField extends Field
{
    /**
     * Create a new IDField instance.
     *
     * @param string $name The field name, defaults to 'id'.
     * @param string|null $label The label to show, defaults to 'ID'.
     * @param string $type The field type, defaults to 'number'.
     */
    public function __construct(string $name = 'id', ?string $label = 'ID', string $type = 'number')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->readonly()->hideFromTable()->hideFromExport();
    }

    /**
     * Static factory method to create an IDField.
     *
     * @param string $name The field name.
     * @param string|null $label The field label.
     * @param string $type The field type.
     * @return self
     */
    public static function make(string $name = 'id', ?string $label = 'ID', string $type = 'number'): self
    {
        return new self($name, $label, $type);
    }
}
