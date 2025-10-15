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

    /** @var array<string, mixed>|null Cache for resolved options within a request */
    protected ?array $resolvedOptions = null;

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
        $this->resolvedOptions = null;

        return $this;
    }

    /**
     * Retrieve options, resolving if necessary.
     */
    public function getOptions(): array
    {
        if ($this->resolvedOptions !== null) {
            return $this->resolvedOptions;
        }

        if ($this->options instanceof Closure) {
            $this->resolvedOptions = call_user_func($this->options);
            return $this->resolvedOptions;
        }

        if (is_string($this->options) && enum_exists($this->options)) {
            $this->resolvedOptions = collect(($this->options)::cases())
                ->mapWithKeys(fn ($case) => [
                    $case->value => method_exists($case, 'label') ? $case->label() : $case->name,
                ])
                ->toArray();
            return $this->resolvedOptions;
        }

        $this->resolvedOptions = $this->options ?? [];
        return $this->resolvedOptions;
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
    public function setValue(mixed $model): self
    {
        $rawValue = $model instanceof Model ? $model->{$this->name} : $model;

        // Als het een enum instance is: meteen gebruiken
        if ($rawValue instanceof BackedEnum) {
            $this->value = method_exists($rawValue, 'label') ? $rawValue->label() : $rawValue->name;
            return $this;
        }

        // Als het een enum class is (via options)
        if (is_string($this->options) && enum_exists($this->options)) {
            $enumClass = $this->options;

            /** @var BackedEnum|null $case */
            $case = $enumClass::tryFrom($rawValue);
            if ($case) {
                $this->value = method_exists($case, 'label') ? $case->label() : $case->name;
            } else {
                $this->value = $rawValue;
            }

            return $this;
        }

        // Als het een gewone key in een array is
        $options = $this->getOptions();
        $this->value = $options[$rawValue] ?? $rawValue;

        return $this;
    }
}
