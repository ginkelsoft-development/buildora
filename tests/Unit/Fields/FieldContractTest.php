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

/**
 * Contracttest voor issue #142.
 *
 * `Field::make()` gebruikte `new self()` in plaats van `new static()`.
 * Elke subklasse die `make()` niet zelf overschrijft (zoals `HasOneField`
 * vóór de fix) kreeg daardoor een kale `Field`-instance terug in plaats
 * van een instance van de subklasse zelf (late static binding werd niet
 * gerespecteerd).
 *
 * Bij `ViewField`, die `make()` wél overschreef, zorgde de standaardwaarde
 * `?string $label = ''` ervoor dat het label leeg bleef in plaats van
 * automatisch afgeleid te worden uit de naam (zoals bij `Field`/`TextField`
 * wel gebeurt).
 */
class FieldContractTest extends TestCase
{
    /** @test */
    public function has_one_field_make_geeft_een_has_one_field_instance_terug(): void
    {
        $field = HasOneField::make('author');

        $this->assertInstanceOf(
            HasOneField::class,
            $field,
            'HasOneField::make() geeft een ' . get_class($field) . ' terug in plaats van een HasOneField '
            . '- Field::make() gebruikt "new self()" i.p.v. "new static()".'
        );
    }

    /** @test */
    public function view_field_make_leidt_label_af_uit_de_naam_als_geen_label_is_opgegeven(): void
    {
        $field = ViewField::make('first_name');

        // Verwacht: hetzelfde afgeleide label als de basisklasse Field
        // (ucfirst(str_replace('_', ' ', $name))): "First name".
        $expected = Field::make('first_name')->label;

        $this->assertEquals(
            $expected,
            $field->label,
            "ViewField::make('first_name') levert een label op ('{$field->label}') dat afwijkt van de "
            . "standaard label-afleiding van Field ('{$expected}')."
        );
        $this->assertNotSame('', $field->label, 'ViewField::make(\'first_name\') levert een leeg label op.');
    }

    /**
     * Alle 23 fieldtypes moeten via ::make() een instance van zichzelf
     * teruggeven, of ze de fabrieksmethode nu zelf overschrijven of niet.
     * Dit borgt late static binding structureel, zodat een toekomstige
     * nieuwe fieldtype-klasse zonder eigen make()-override niet opnieuw
     * in de val van issue #142 loopt.
     *
     * @dataProvider fieldTypeClassProvider
     */
    public function test_elk_fieldtype_geeft_zijn_eigen_klasse_terug_via_make(string $fieldClass): void
    {
        /** @var Field $field */
        $field = $fieldClass::make('sample_field');

        $this->assertInstanceOf(
            $fieldClass,
            $field,
            "$fieldClass::make() geeft een " . get_class($field) . ' terug in plaats van een ' . $fieldClass . '.'
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function fieldTypeClassProvider(): array
    {
        return [
            AsyncBelongsToField::class => [AsyncBelongsToField::class],
            BelongsToField::class => [BelongsToField::class],
            BelongsToManyField::class => [BelongsToManyField::class],
            BooleanField::class => [BooleanField::class],
            CheckboxListField::class => [CheckboxListField::class],
            CurrencyField::class => [CurrencyField::class],
            DateField::class => [DateField::class],
            DateTimeField::class => [DateTimeField::class],
            DisplayField::class => [DisplayField::class],
            EditorField::class => [EditorField::class],
            EmailField::class => [EmailField::class],
            FileField::class => [FileField::class],
            HasManyField::class => [HasManyField::class],
            HasOneField::class => [HasOneField::class],
            IDField::class => [IDField::class],
            JsonField::class => [JsonField::class],
            NumberField::class => [NumberField::class],
            PasswordField::class => [PasswordField::class],
            RelationLinkField::class => [RelationLinkField::class],
            RichTextField::class => [RichTextField::class],
            SelectField::class => [SelectField::class],
            TextField::class => [TextField::class],
            ViewField::class => [ViewField::class],
        ];
    }
}
