<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Represents a field for rendering rich text (HTML) content in views only.
 */
class RichTextField extends Field
{
    /**
     * Create a new RichTextField instance.
     *
     * @param string $name
     * @param string|null $label
     */
    public function __construct(string $name = 'content', ?string $label = 'Inhoud', string $type = 'richtext')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable(false);
    }

    /**
     * Static factory method to create a new RichTextField.
     *
     * @param string $name
     * @param string|null $label
     * @param string|null $type
     * @return static
     */
    public static function make(string $name, ?string $label = null, string $type = 'richtext'): self
    {
        return new self($name, $label ?? 'Inhoud', $type);
    }

    /**
     * Indicates whether this field supports searching.
     *
     * @return bool
     */
    public function supportsSearch(): bool
    {
        return false;
    }
}
