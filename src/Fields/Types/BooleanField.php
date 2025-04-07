<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * A specialized field type for handling boolean values such as toggles or checkboxes.
 */
class BooleanField extends Field
{
    protected array $options = [
        true => 'Yes',
        false => 'No',
    ];

    /**
     * Create a new BooleanField instance.
     */
    public function __construct(string $name = 'is_active', ?string $label = 'Active', string $type = 'boolean')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable();
    }

    /**
     * Factory method to create a new BooleanField instance.
     */
    public static function make(string $name = 'is_active', ?string $label = 'Active', string $type = 'boolean'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Set or get the value of the field.
     */
    public function value(mixed $value = null): mixed
    {
        if (func_num_args() === 0) {
            return is_bool($this->value) ? (string)(int)$this->value : (string)$this->value;
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Set the label options for true/false values.
     */
    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get the current label options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
