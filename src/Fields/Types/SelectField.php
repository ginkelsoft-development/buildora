<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use BackedEnum;
use Closure;
use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SelectField
 *
 * Represents a select dropdown field with search functionality.
 */
class SelectField extends Field
{
    protected array|Closure|string $options = [];

    protected bool $nullable = false;

    /**
     * Create a new SelectField instance.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     */
    public function __construct(string $name, ?string $label = null, string $type = 'select')
    {
        parent::__construct($name, $label ?? ucfirst($name), $type);
    }

    /**
     * Factory method.
     */
    public static function make(string $name, ?string $label = null, string $type = 'select'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Set the list of options (array, Closure, or enum class name).
     */
    public function options(array|Closure|string $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Retrieve options, resolving if necessary.
     */
    public function getOptions(): array
    {
        if ($this->options instanceof Closure) {
            return call_user_func($this->options);
        }

        if (is_string($this->options) && enum_exists($this->options)) {
            return collect(($this->options)::cases())
                ->mapWithKeys(fn ($case) => [
                    $case->value => method_exists($case, 'label') ? $case->label() : $case->name,
                ])
                ->toArray();
        }

        return $this->options ?? [];
    }

    public function label(string $value): static
    {
        $this->label = $value;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?? ucfirst($this->name);
    }

    /**
     * Set the field value directly.
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }
}
