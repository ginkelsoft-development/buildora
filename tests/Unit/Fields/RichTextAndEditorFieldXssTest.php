<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Fields;

use Ginkelsoft\Buildora\Fields\Types\EditorField;
use Ginkelsoft\Buildora\Fields\Types\RichTextField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Tests\TestCase;

/**
 * Regressietests voor issue #122.
 *
 * Bevinding: RichTextField en EditorField sloegen WYSIWYG-HTML ongewijzigd
 * op. Meerdere views renderen fieldwaarden vervolgens unescaped:
 * - resources/views/components/input/richtext.blade.php: `{!! $value !!}`
 * - resources/views/form.blade.php: rendert de per-type input-component
 *   (dus ook richtext.blade.php) met de opgeslagen/`old()`-waarde
 * - resources/views/components/datatable.blade.php: `x-html="formatCell(...)"`
 *   op de ruwe (niet ge-escapte) veldwaarde uit RowFormatter
 *
 * Payload `<script>alert(1)</script>` opgeslagen via deze fieldtypes kwam
 * dus ongestript terug in de gerenderde output = stored XSS (OWASP
 * A03:2021 - Injection).
 *
 * Fix: BuildoraController::store()/update() roept nu
 * `$field->prepareForStorage()` aan met de ruwe request-waarde vlak vóór
 * het opslaan. RichTextField en
 * EditorField gebruiken de `SanitizesHtml`-trait om die waarde standaard
 * (`sanitize(true)` is de default) door HTMLPurifier te halen met een
 * configureerbare allow-list (`->allowedTags()`), zodat `<script>` en
 * andere gevaarlijke opmaak/attributen (bv. `onerror=`, `javascript:`-URI's)
 * al vóór het opslaan verdwijnen in plaats van pas (niet) bij het renderen.
 *
 * @see https://github.com/ginkelsoft-development/buildora/issues/122
 */
class RichTextAndEditorFieldXssTest extends TestCase
{
    public static function richTextAndEditorFieldProvider(): array
    {
        return [
            RichTextField::class => [RichTextField::class],
            EditorField::class => [EditorField::class],
        ];
    }

    /**
     * @dataProvider richTextAndEditorFieldProvider
     * @test
     */
    public function it_strips_a_script_payload_before_save_by_default(string $fieldClass): void
    {
        $field = $fieldClass::make('content');

        $payload = '<script>alert(1)</script>';

        $sanitized = $field->prepareForStorage($payload);

        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringNotContainsString('alert(1)', $sanitized);
    }

    /**
     * @dataProvider richTextAndEditorFieldProvider
     * @test
     */
    public function it_strips_an_inline_event_handler_disguised_as_a_normal_tag(string $fieldClass): void
    {
        $field = $fieldClass::make('content');

        $payload = '<img src="x" onerror="alert(document.cookie)">';

        $sanitized = $field->prepareForStorage($payload);

        $this->assertStringNotContainsString('onerror', $sanitized);
        $this->assertStringNotContainsString('alert(', $sanitized);
    }

    /**
     * @dataProvider richTextAndEditorFieldProvider
     * @test
     */
    public function it_strips_a_javascript_uri_from_a_link(string $fieldClass): void
    {
        $field = $fieldClass::make('content');

        $payload = '<a href="javascript:alert(1)">klik</a>';

        $sanitized = $field->prepareForStorage($payload);

        $this->assertStringNotContainsString('javascript:', $sanitized);
    }

    /**
     * @dataProvider richTextAndEditorFieldProvider
     * @test
     */
    public function it_keeps_ordinary_formatting_from_the_default_allow_list(string $fieldClass): void
    {
        $field = $fieldClass::make('content');

        $payload = '<p>Hallo <strong>wereld</strong></p><ul><li>een</li></ul>';

        $sanitized = $field->prepareForStorage($payload);

        $this->assertStringContainsString('<p>', $sanitized);
        $this->assertStringContainsString('<strong>wereld</strong>', $sanitized);
        $this->assertStringContainsString('<li>een</li>', $sanitized);
    }

    /**
     * @dataProvider richTextAndEditorFieldProvider
     * @test
     */
    public function sanitize_is_enabled_by_default(string $fieldClass): void
    {
        $field = $fieldClass::make('content');

        $this->assertTrue($field->isSanitizeEnabled());
    }

    /**
     * @dataProvider richTextAndEditorFieldProvider
     * @test
     */
    public function allowed_tags_are_configurable_per_field(string $fieldClass): void
    {
        $field = $fieldClass::make('content')->allowedTags(['b']);

        $sanitized = $field->prepareForStorage('<p>tekst</p><b>vet</b>');

        // <p> staat niet meer op de (nu strengere) allow-list en verdwijnt,
        // maar de inhoud blijft staan; <b> is nog wel toegestaan.
        $this->assertStringNotContainsString('<p>', $sanitized);
        $this->assertStringContainsString('<b>vet</b>', $sanitized);
        $this->assertStringContainsString('tekst', $sanitized);
    }

    /**
     * @dataProvider richTextAndEditorFieldProvider
     * @test
     */
    public function sanitize_can_be_explicitly_disabled(string $fieldClass): void
    {
        $field = $fieldClass::make('content')->sanitize(false);

        $payload = '<script>alert(1)</script>';

        $this->assertFalse($field->isSanitizeEnabled());
        $this->assertSame($payload, $field->prepareForStorage($payload));
    }

    /**
     * Sanity check: het gedrag van gewone fieldtypes (die de sanitization
     * trait niet gebruiken) blijft ongewijzigd - Field::prepareForStorage()
     * is de identity-functie.
     *
     * @test
     */
    public function a_regular_field_leaves_the_value_untouched(): void
    {
        $field = TextField::make('title');

        $payload = '<script>alert(1)</script>';

        $this->assertSame($payload, $field->prepareForStorage($payload));
    }
}
