<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Represents a JSON field for storing structured data.
 *
 * This field is hidden by default from the table and export views.
 */
class JsonField extends Field
{
    /**
     * Create a new JsonField instance.
     *
     * @param string $name The field name, defaults to 'metadata'.
     * @param string|null $label The display label for the field.
     * @param string $type The input type, defaults to 'json'.
     */
    public function __construct(string $name = 'metadata', ?string $label = 'Metadata', string $type = 'json')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->hideFromTable()->hideFromExport();
    }

    /**
     * Static factory method for creating a JsonField instance.
     *
     * @param string $name The field name.
     * @param string|null $label The display label.
     * @param string $type The input type.
     * @return self
     */
    public static function make(string $name = 'metadata', ?string $label = 'Metadata', string $type = 'json'): self
    {
        return new self($name, $label, $type);
    }
}
