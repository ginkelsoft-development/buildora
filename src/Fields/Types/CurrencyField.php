<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

/**
 * Represents a currency input field with two-decimal precision.
 */
class CurrencyField extends Field
{
    /**
     * Create a new CurrencyField instance.
     *
     * @param string $name
     * @param string|null $label
     */
    public function __construct(string $name = 'content', ?string $label = 'Inhoud', string $type = 'currency')
    {
        parent::__construct($name, $label, $type);
        $this->label($label)->sortable(false)
            ->help('Bedragen in euro’s. Gebruik een punt als decimaalteken.');
    }

    /**
     * Static factory method to create a new RichTextField.
     *
     * @param string $name
     * @param string|null $label
     * @param string|null $type
     * @return static
     */
    public static function make(string $name, ?string $label = null, string $type = 'currency'): self
    {
        return new self($name, $label ?? 'Inhoud', $type);
    }

    public function supportsSearch(): bool
    {
        return false;
    }

    public function getDisplayValue(mixed $model): string
    {
        $value = $model->{$this->name} ?? null;

        // Defensief casten: null, lege string en niet-numerieke waarden
        // (bijv. corrupte data of een leeg formulierveld) mogen niet
        // crashen op number_format() (zie issue #143).
        if (is_null($value) || $value === '' || ! is_numeric($value)) {
            return '-';
        }

        return '€ ' . number_format((float) $value, 2, ',', '.');
    }
}
