<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Closure;
use Ginkelsoft\Buildora\Fields\Field;

/**
 * Read-only field that surfaces a computed string value.
 *
 * Use when you need to show a derived value next to the editable fields —
 * a running total, a formatted timestamp, a status label. The content
 * accepts either a plain string or a Closure that receives the current
 * model and returns a string.
 *
 *   DisplayField::make('total')
 *       ->content(fn (\$order) => '€ ' . number_format(\$order->total, 2));
 *
 * Sibling field types:
 *   - ViewField — when you need a full Blade partial (HTML, charts, etc.)
 *     rather than a single computed string.
 *   - RichTextField — for read-only display of WYSIWYG-authored HTML
 *     (routes through HtmlSanitizer).
 */
class DisplayField extends Field
{
    protected string $view = 'buildora::form-fields.display';

    protected string|Closure|null $content = null;

    /**
     * Create a new DisplayField instance.
     */
    public function __construct(string $name, ?string $label = null, string $type = 'display')
    {
        parent::__construct($name, $label ?? ucfirst($name), $type);
    }

    public static function make(string $name, ?string $label = null, string $type = 'display'): self
    {
        return new self($name, $label, $type);
    }

    public function content(string|Closure $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(mixed $model = null): string
    {
        if ($this->content instanceof Closure) {
            return call_user_func($this->content, $model);
        }

        return (string) $this->content;
    }
}
