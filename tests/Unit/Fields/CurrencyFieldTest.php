<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Types\CurrencyField;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

class CurrencyFieldTest extends TestCase
{
    private function fieldWithValue(mixed $value): CurrencyField
    {
        $field = CurrencyField::make('price');
        return $field;
    }

    private function modelStub(mixed $value): stdClass
    {
        $stub = new stdClass();
        $stub->price = $value;
        return $stub;
    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function formattedInputs(): array
    {
        return [
            'float'           => [12.5,    '€ 12,50'],
            'integer'         => [42,      '€ 42,00'],
            'string decimal'  => ['12.50', '€ 12,50'],
            'string integer'  => ['100',   '€ 100,00'],
            'large number'    => [1234567.89, '€ 1.234.567,89'],
            'zero float'      => [0.0,     '€ 0,00'],
            'zero int'        => [0,       '€ 0,00'],
            'negative'        => [-5.25,   '€ -5,25'],
        ];
    }

    #[Test]
    #[DataProvider('formattedInputs')]
    public function it_formats_numeric_input(mixed $value, string $expected): void
    {
        $field = $this->fieldWithValue($value);

        $this->assertSame($expected, $field->getDisplayValue($this->modelStub($value)));
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function dashInputs(): array
    {
        return [
            'null'            => [null],
            'empty string'    => [''],
            'non-numeric'     => ['n/a'],
            'whitespace text' => ['unknown'],
        ];
    }

    #[Test]
    #[DataProvider('dashInputs')]
    public function it_falls_back_to_a_dash_for_null_or_non_numeric_input(mixed $value): void
    {
        $field = $this->fieldWithValue($value);

        $this->assertSame('-', $field->getDisplayValue($this->modelStub($value)));
    }

    #[Test]
    public function it_does_not_crash_on_string_input_from_decimal_columns(): void
    {
        // Reproduces the original bug: Eloquent DECIMAL casts return strings,
        // and the unfixed implementation passed that straight to
        // number_format() which throws a TypeError.
        $field = $this->fieldWithValue('99.99');

        $this->assertSame('€ 99,99', $field->getDisplayValue($this->modelStub('99.99')));
    }
}
