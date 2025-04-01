<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * A specialized field type for displaying datetime-related attributes.
 * Typically used for read-only timestamp fields like `created_at` or `updated_at`.
 */
class DateField extends Field
{
    /**
     * Create a new DateField instance.
     *
     * @param string $name The attribute name on the model (default: 'created_at').
     * @param string|null $label The label to be displayed (default: 'Created At').
     * @param string $type The field type identifier (default: 'date').
     */
    public function __construct(string $name = 'created_at', ?string $label = 'Created At', string $type = 'date')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)
            ->sortable()
            ->hideFromCreate()
            ->hideFromEdit();
    }

    /**
     * Factory method to create a new DateField instance.
     *
     * @param string $name The model attribute name.
     * @param string|null $label Optional label to be shown in UI.
     * @param string $type The field type.
     * @return self
     */
    public static function make(string $name = 'created_at', ?string $label = 'Created At', string $type = 'date'): self
    {
        return new self($name, $label, $type);
    }
}
