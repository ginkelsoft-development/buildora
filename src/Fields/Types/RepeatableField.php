<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Support\Arr;

/**
 * Class RepeatableField
 *
 * Flexible repeatable field: supports JSON array storage.
 */
class RepeatableField extends Field
{
    protected array $subfields = [];
    protected string $view = 'buildora::fields.repeatable';

    public function __construct(string $name, ?string $label = null, string $type = 'repeatable')
    {
        parent::__construct($name, $label ?? ucfirst($name), $type);
    }

    public static function make(string $name, ?string $label = null, string $type = 'repeatable'): self
    {
        return new self($name, $label, $type);
    }

    public function addField(Field $field): static
    {
        $this->subfields[] = $field;
        return $this;
    }

    public function getSubfields(): array
    {
        return $this->subfields;
    }

    public function getCast(): ?string
    {
        return 'array';
    }

    public function fillFromRequest(array $request): static
    {
        $this->value = $request[$this->name] ?? [];
        return $this;
    }

    public function setValue(mixed $value): static
    {
        if ($value instanceof Model) {
            $attr = $value->{$this->name};

            if (is_string($attr)) {
                $this->value = json_decode($attr, true) ?? [];
            } elseif (is_array($attr)) {
                $this->value = $attr;
            } else {
                $this->value = [];
            }
        } elseif (is_string($value)) {
            $this->value = json_decode($value, true) ?? [];
        } elseif (is_array($value)) {
            $this->value = $value;
        } else {
            $this->value = [];
        }

        return $this;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function rows(): array
    {
        $value = $this->value;

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (!is_array($value)) {
            return [];
        }

        if (array_key_exists('${index}', $value)) {
            return [ $value['${index}'] ];
        }

        if (Arr::isAssoc($value)) {
            return array_values($value);
        }

        return $value;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'subfields' => collect($this->subfields)->map->toArray()->toArray(),
        ]);
    }
}
