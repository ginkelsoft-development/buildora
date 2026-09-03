<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Types\FileField;
use Ginkelsoft\Buildora\Tests\TestCase;

/**
 * Onderzoekstests voor issue #121.
 *
 * Deze tests bewijzen op veldniveau de kernoorzaak van het probleem: de
 * configuratiemethodes accept()/maxSize()/imageDimensions() van FileField
 * hebben geen enkele invloed op getValidationRules() (het enige mechanisme
 * dat BuildoraController::store()/update() gebruikt om server-side
 * $request->validate() aan te roepen). Zonder dat een resource-auteur zelf
 * expliciet ->validation(['veld' => 'mimes:...|max:...']) toevoegt, is er
 * dus geen server-side extensie/MIME-whitelist en geen deny-list voor
 * executables (.php, .phtml, .phar, ...).
 */
class FileFieldTest extends TestCase
{
    /** @test */
    public function accept_does_not_translate_into_a_server_side_validation_rule(): void
    {
        $field = FileField::make('attachment')->accept('image/*');

        $this->assertSame('image/*', $field->getAccept());
        $this->assertSame(
            [],
            $field->getValidationRules(),
            'accept() zou geen effect mogen hebben tenzij ->validation() expliciet is gezet — ' .
            'dit bevestigt dat er standaard GEEN server-side mime/extensie-whitelist is.'
        );
    }

    /** @test */
    public function max_size_does_not_translate_into_a_server_side_validation_rule(): void
    {
        $field = FileField::make('attachment')->maxSize(200);

        $this->assertSame(200, $field->getMaxSizeKb());
        $this->assertSame(
            [],
            $field->getValidationRules(),
            'maxSize() wordt nergens omgezet naar een "max:"-validatieregel.'
        );
    }

    /** @test */
    public function image_dimensions_does_not_translate_into_a_server_side_validation_rule(): void
    {
        $field = FileField::make('attachment')->imageDimensions(800, 600);

        $this->assertSame(800, $field->getMaxWidth());
        $this->assertSame(600, $field->getMaxHeight());
        $this->assertSame(
            [],
            $field->getValidationRules(),
            'imageDimensions() wordt nergens omgezet naar een "dimensions:"-validatieregel.'
        );
    }

    /** @test */
    public function a_file_field_without_any_configuration_has_no_validation_rules_at_all(): void
    {
        // Zelfs een "kale" FileField::make() levert geen enkele impliciete
        // server-side beperking op: geen verplichte extensie-whitelist en
        // geen deny-list voor uitvoerbare bestandstypen.
        $field = FileField::make('attachment');

        $this->assertSame([], $field->getValidationRules());
    }
}
