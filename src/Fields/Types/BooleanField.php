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

    public function value(mixed $value = null): mixed
    {
        if (func_num_args() === 0) {
            return (string)(int) filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
        }

        $this->value = $value;
        return $this;
    }

    public function setValue(mixed $model): self
    {
        $rawValue = $model instanceof \Illuminate\Database\Eloquent\Model
            ? $model->{$this->name}
            : $model;

        // Zorg dat het een boolean is (of castbaar)
        $boolKey = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        // fallback op originele waarde als parsing faalt
        $this->value = $this->options[$boolKey] ?? $rawValue;

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
