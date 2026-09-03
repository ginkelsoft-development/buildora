<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\HasOneField;
use Ginkelsoft\Buildora\Fields\Types\ViewField;
use Ginkelsoft\Buildora\Tests\TestCase;

/**
 * Reproductie voor issue #142.
 *
 * Field::make() gebruikt `new self()` in plaats van `new static()`. Elke
 * subklasse die make() niet zelf overschrijft (zoals HasOneField) krijgt
 * daardoor een kale Field-instance terug in plaats van een instance van
 * de subklasse zelf (late static binding wordt niet gerespecteerd).
 *
 * Bij ViewField, die make() wél overschrijft, zorgt de standaardwaarde
 * `?string $label = ''` er in combinatie met dezelfde `new self()`-aanpak
 * voor dat het label leeg blijft in plaats van automatisch afgeleid te
 * worden uit de naam (zoals bij Field/TextField wel gebeurt).
 *
 * Beide tests hieronder falen op de huidige code (rood) en tonen daarmee
 * de bug aan. Er is bewust geen productiecode aangepast.
 */
class FieldContractTest extends TestCase
{
    /** @test */
    public function has_one_field_make_geeft_een_has_one_field_instance_terug(): void
    {
        $field = HasOneField::make('author');

        // Verwacht: instance van HasOneField.
        // Werkelijk: Field::make() gebruikt `new self()`, wat lexicaal
        // altijd Field is (de klasse waarin make() gedefinieerd is), dus
        // HasOneField::make() geeft een kale Field-instance terug.
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

        // Verwacht: label wordt afgeleid uit de naam, net als bij Field/TextField: "First Name".
        // Werkelijk: ViewField::make() heeft als standaardwaarde `?string $label = ''`
        // (in plaats van null), waardoor `$label ?? ucfirst(...)` nooit de afleiding
        // triggert en het label leeg ('') blijft.
        $this->assertEquals(
            'First Name',
            $field->label,
            'ViewField::make(\'first_name\') levert een leeg label op in plaats van "First Name".'
        );
    }
}
