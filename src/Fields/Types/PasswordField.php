<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Represents a password input field.
 *
 * This field is not searchable for security reasons.
 */
class PasswordField extends Field
{
    /**
     * Create a new PasswordField instance.
     *
     * @param string $name The name of the field (default: 'password').
     * @param string|null $label The display label (default: 'Password').
     * @param string $type The field type (default: 'password').
     */
    public function __construct(string $name = 'password', ?string $label = 'Password', string $type = 'password')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable();
    }

    /**
     * Static factory method to create a PasswordField instance.
     *
     * @param string $name The field name.
     * @param string|null $label Optional label.
     * @param string $type The input type (default: 'password').
     * @return self
     */
    public static function make(string $name = 'password', ?string $label = 'Password', string $type = 'password'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Indicates whether this field supports searching.
     *
     * @return bool Always false for password fields.
     */
    public function supportsSearch(): bool
    {
        return false;
    }
}
