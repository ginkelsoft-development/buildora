<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Traits\SanitizesHtml;

/**
 * Represents a WYSIWYG HTML editor field using a rich editor like CKEditor.
 *
 * De WYSIWYG-HTML uit deze editor komt rechtstreeks van de gebruiker en
 * wordt elders ongesanitized gerenderd. Daarom wordt de inkomende waarde
 * standaard door HTMLPurifier gehaald (zie {@see SanitizesHtml}) vlak vóór
 * het opslaan, met een configureerbare allow-list van tags/attributen.
 */
class EditorField extends Field
{
    use SanitizesHtml;

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
