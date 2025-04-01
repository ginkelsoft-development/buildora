<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Represents a text input field that is sortable and searchable.
 */
class TextField extends Field
{
    /**
     * Create a new TextField instance.
     *
     * @param string $name The name of the field (default: 'name').
     * @param string|null $label The display label (default: 'Name').
     * @param string $type The field type (default: 'text').
     */
    public function __construct(string $name = 'name', ?string $label = 'Name', string $type = 'text')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable();
    }

    /**
     * Static factory method to create a new TextField.
     *
     * @param string $name The name of the field.
     * @param string|null $label Optional label.
     * @param string $type The input type (default: 'text').
     * @return self
     */
    public static function make(string $name = 'name', ?string $label = 'Name', string $type = 'text'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Indicates whether this field supports searching.
     *
     * @return bool Always true for text fields.
     */
    public function supportsSearch(): bool
    {
        return true;
    }
}
