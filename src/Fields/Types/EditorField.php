<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Represents a WYSIWYG HTML editor field using a rich editor like CKEditor.
 */
class EditorField extends Field
{
    /**
     * Create a new EditorField instance.
     *
     * @param string $name
     * @param string|null $label
     */
    public function __construct(string $name = 'content', ?string $label = 'Inhoud', string $type = 'editor')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable(false);
    }

    /**
     * Static factory method to create a new EditorField.
     *
     * @param string $name
     * @param string|null $label
     * @return self
     */
    public static function make(string $name, ?string $label = null, string $type = 'editor'): self
    {
        return new self($name, $label ?? 'Inhoud', $type);
    }

    public function supportsSearch(): bool
    {
        return false;
    }
}
