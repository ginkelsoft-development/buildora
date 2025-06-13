<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class RepeatableField
 *
 * Flexible repeatable field: supports JSON array storage.
 */
class RepeatableField extends Field
{
    /** @var array<int, Field> */
    protected array $subfields = [];

    public string $type = 'repeatable';

    public function __construct(string $name, ?string $label = null, string $type = 'repeatable')
    {
        parent::__construct($name, $label ?? ucfirst($name), $type);
    }

    public static function make(string $name, ?string $label = null, string $type = 'repeatable'): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Define the subfields schema.
     *
     * @param array<int, Field> $fields
     */
    public function addFields(array $fields): static
    {
        $this->subfields = $fields;
        return $this;
    }

    /**
     * Get the subfields schema.
     *
     * @return array<int, Field>
     */
    public function getSubfields(): array
    {
        return $this->subfields;
    }

    /**
     * Return the rows as an array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rows(): array
    {
        $value = $this->value;

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (!is_array($value)) {
            return [];
        }

        if (Arr::isAssoc($value)) {
            $value = array_values($value);
        }

        // Voeg uuid toe
        foreach ($value as &$row) {
            if (!isset($row['__uuid'])) {
                $row['__uuid'] = (string) Str::uuid();
            }
        }

        return $value;
    }

    public function getValidationRules(mixed $model = null): array
    {
        $rules = $this->validationRules;

        if (is_callable($rules)) {
            $rules = call_user_func($rules, $model);
        }

        // Zorg dat we altijd een array teruggeven
        return is_array($rules) ? $rules : [];
    }

    /**
     * Convert the field to an array representation for the frontend.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return parent::toArray() + [
                'rows' => $this->rows(),
                'subfields' => collect($this->subfields)->map->toArray()->values()->all(),
            ];
    }
}
