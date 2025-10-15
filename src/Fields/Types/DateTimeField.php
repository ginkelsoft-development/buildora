<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * A field type representing a combined date and time value.
 * Commonly used for attributes like `created_at`, `updated_at`, or timestamps.
 */
class DateTimeField extends Field
{
    /**
     * Create a new DateTimeField instance.
     *
     * @param string $name The model attribute name (default: 'created_at').
     * @param string|null $label The label displayed in the UI (default: 'Created At').
     * @param string $type The internal field type (default: 'datetime').
     */
    public function __construct(string $name = 'created_at', ?string $label = 'Created At', string $type = 'datetime')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)
            ->sortable()
            ->hideFromCreate()
            ->hideFromEdit();
    }

    /**
     * Factory method to instantiate a DateTimeField.
     *
     * @param string $name The model attribute name.
     * @param string|null $label Optional custom label.
     * @param string $type The internal field type.
     * @return self
     */
    public static function make(
        string $name = 'created_at',
        ?string $label = 'Created At',
        string $type = 'datetime'
    ): self {
        return new self($name, $label, $type);
    }
}
