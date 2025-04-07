<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Closure;
use Ginkelsoft\Buildora\Fields\Field;

/**
 * A non-editable display-only field for rendering raw output or computed content.
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
