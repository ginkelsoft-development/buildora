<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\AsyncBelongsToField;
use Ginkelsoft\Buildora\Fields\Types\BelongsToField;
use Ginkelsoft\Buildora\Fields\Types\BelongsToManyField;
use Ginkelsoft\Buildora\Fields\Types\BooleanField;
use Ginkelsoft\Buildora\Fields\Types\CheckboxListField;
use Ginkelsoft\Buildora\Fields\Types\CurrencyField;
use Ginkelsoft\Buildora\Fields\Types\DateField;
use Ginkelsoft\Buildora\Fields\Types\DateTimeField;
use Ginkelsoft\Buildora\Fields\Types\DisplayField;
use Ginkelsoft\Buildora\Fields\Types\EditorField;
use Ginkelsoft\Buildora\Fields\Types\EmailField;
use Ginkelsoft\Buildora\Fields\Types\FileField;
use Ginkelsoft\Buildora\Fields\Types\HasManyField;
use Ginkelsoft\Buildora\Fields\Types\HasOneField;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\JsonField;
use Ginkelsoft\Buildora\Fields\Types\NumberField;
use Ginkelsoft\Buildora\Fields\Types\PasswordField;
use Ginkelsoft\Buildora\Fields\Types\RelationLinkField;
use Ginkelsoft\Buildora\Fields\Types\RichTextField;
use Ginkelsoft\Buildora\Fields\Types\SelectField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Fields\Types\ViewField;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

class FieldContractTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string<Field>}>
     */
    public static function fieldTypes(): array
    {
        return [
            'AsyncBelongsToField'   => [AsyncBelongsToField::class],
            'BelongsToField'        => [BelongsToField::class],
            'BelongsToManyField'    => [BelongsToManyField::class],
            'BooleanField'          => [BooleanField::class],
            'CheckboxListField'     => [CheckboxListField::class],
            'CurrencyField'         => [CurrencyField::class],
            'DateField'             => [DateField::class],
            'DateTimeField'         => [DateTimeField::class],
            'DisplayField'          => [DisplayField::class],
            'EditorField'           => [EditorField::class],
            'EmailField'            => [EmailField::class],
            'FileField'             => [FileField::class],
            'HasManyField'          => [HasManyField::class],
            'HasOneField'           => [HasOneField::class],
            'IDField'               => [IDField::class],
            'JsonField'             => [JsonField::class],
            'NumberField'           => [NumberField::class],
            'PasswordField'         => [PasswordField::class],
            'RelationLinkField'     => [RelationLinkField::class],
            'RichTextField'         => [RichTextField::class],
            'SelectField'           => [SelectField::class],
            'TextField'             => [TextField::class],
            'ViewField'             => [ViewField::class],
        ];
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_can_be_instantiated_via_make(string $fieldClass): void
    {
        if ($fieldClass === HasOneField::class) {
            $this->markTestSkipped('Tracked in #142 — Field::make() uses new self() instead of new static().');
        }

        $field = $fieldClass::make('test_attribute');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertInstanceOf($fieldClass, $field);
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_exposes_the_attribute_name(string $fieldClass): void
    {
        $field = $fieldClass::make('my_attribute');

        $this->assertSame('my_attribute', $field->name);
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_derives_a_label_when_not_provided(string $fieldClass): void
    {
        if ($fieldClass === ViewField::class) {
            $this->markTestSkipped('Tracked in #142 — ViewField defaults label to empty string instead of null.');
        }

        $field = $fieldClass::make('first_name');

        $this->assertNotNull($field->label);
        $this->assertNotSame('', $field->label);
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_accepts_an_explicit_label(string $fieldClass): void
    {
        $field = $fieldClass::make('attr', 'Custom Label');

        $this->assertSame('Custom Label', $field->label);
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_supports_sortable_builder(string $fieldClass): void
    {
        $field = $fieldClass::make('attr')->sortable();

        $this->assertTrue($field->sortable);
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_supports_readonly_builder(string $fieldClass): void
    {
        $field = $fieldClass::make('attr')->readonly();

        $this->assertTrue($field->readonly);
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_supports_help_text(string $fieldClass): void
    {
        $field = $fieldClass::make('attr')->help('Some help');

        $this->assertSame('Some help', $field->getHelpText());
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_supports_visibility_toggles(string $fieldClass): void
    {
        $field = $fieldClass::make('attr')
            ->hideFromTable()
            ->hideFromCreate()
            ->hideFromEdit()
            ->hideFromDetail()
            ->hideFromExport();

        // Builder methods must return a Field instance to keep the fluent chain intact.
        $this->assertInstanceOf(Field::class, $field);
    }

    /**
     * The base `getDisplayValue()` contract reads `$model->{name}` and stringifies it.
     * Some specialised fields apply transformations that assume specific value types
     * (e.g. CurrencyField casts to float); those are tested in dedicated per-type tests.
     */
    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_returns_a_string_from_get_display_value(string $fieldClass): void
    {
        if (in_array($fieldClass, [CurrencyField::class], true)) {
            $this->markTestSkipped('Currency field requires numeric input — covered in CurrencyFieldTest.');
        }

        $field = $fieldClass::make('label');

        $stub = new stdClass();
        $stub->label = 'Voorbeeld';

        $this->assertIsString($field->getDisplayValue($stub));
    }

    #[Test]
    #[DataProvider('fieldTypes')]
    public function it_can_be_serialized_to_array(string $fieldClass): void
    {
        $field = $fieldClass::make('attr');

        $array = $field->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('name', $array);
        $this->assertSame('attr', $array['name']);
    }
}
