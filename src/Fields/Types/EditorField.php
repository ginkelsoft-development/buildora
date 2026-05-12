<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Form input for WYSIWYG-authored HTML content.
 *
 * Pairs with RichTextField (the read-only display side). Use EditorField on
 * create/edit forms — the editor blade component renders a textarea wired
 * up to CKEditor. Use RichTextField wherever the saved HTML is displayed
 * back to the user.
 *
 * Captured input is stored verbatim. Sanitisation happens on the display
 * path via RichTextField's blade component (#122), not here, so any custom
 * read-only rendering of editor output must also call HtmlSanitizer::clean.
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
