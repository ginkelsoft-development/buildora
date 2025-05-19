<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * A field type specifically for handling email addresses.
 * It renders as a text input with validation and optional sorting.
 */
class EmailField extends Field
{
    /**
     * Create a new EmailField instance.
     *
     * @param string $name The model attribute name (default: 'email').
     * @param string|null $label The display label for the field (default: 'Email').
     * @param string $type The internal field type identifier (default: 'email').
     */
    public function __construct(string $name = 'email', ?string $label = 'Email', string $type = 'email')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable();
    }

    /**
     * Static factory method to quickly create an EmailField.
     *
     * @param string $name The name of the attribute.
     * @param string|null $label Optional label override.
     * @param string $type Field type (default: 'email').
     * @return self
     */
    public static function make(string $name = 'email', ?string $label = 'Email', string $type = 'email'): self
    {
        return new self($name, $label, $type);
    }
}
