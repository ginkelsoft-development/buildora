<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Read-only display field for HTML content stored against a model.
 *
 * Pairs with EditorField (the input-side WYSIWYG textarea). Use RichTextField
 * on detail/show views to render WYSIWYG-authored HTML; use EditorField on
 * create/edit forms to capture it.
 *
 * Output is routed through HtmlSanitizer in the richtext blade component,
 * which strips script/iframe/event handlers and rejects unsafe URL schemes —
 * see #122. The sanitiser is the security boundary; don't bypass it by
 * rendering field->value directly with {!! !!} in custom views.
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
