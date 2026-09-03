<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Traits\SanitizesHtml;

/**
 * Represents a field for rendering rich text (HTML) content in views only.
 *
 * De opgeslagen waarde wordt elders ongesanitized gerenderd (o.a.
 * `{!! $value !!}`), wat zonder maatregelen tot stored XSS leidt. Daarom
 * wordt de inkomende waarde standaard door HTMLPurifier gehaald (zie
 * {@see SanitizesHtml}) vlak vóór het opslaan, met een configureerbare
 * allow-list van tags/attributen.
 */
class RichTextField extends Field
{
    use SanitizesHtml;

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
