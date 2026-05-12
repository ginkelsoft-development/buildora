<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Datatable;

use Ginkelsoft\Buildora\Datatable\RowFormatter;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Fields\Types\ViewField;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;

class RFPStubResource
{
    /** @var array<int, \Ginkelsoft\Buildora\Fields\Field> */
    public array $fieldsToReturn = [];

    public function getFields(): array
    {
        return $this->fieldsToReturn;
    }

    public function getRowActions(object $resource): array
    {
        return [];
    }
}

/**
 * Pins the OCP refactor: RowFormatter no longer branches on the concrete
 * Field type to handle ViewField. It calls Field::renderForDisplay() on
 * every field; the base implementation is a no-op, and ViewField overrides
 * it to materialise its Blade partial.
 *
 * This is the surface area that #134 was about — adding a new
 * "specialty" field type should never again require editing RowFormatter.
 */
class RowFormatterPolymorphismTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // Make a minimal inline view available so ViewField has something
        // to render. Using a closure-backed Blade view keeps the test
        // self-contained.
        View::addNamespace('rfp', __DIR__ . '/views');
    }

    #[Test]
    public function plain_field_renderForDisplay_is_a_noop(): void
    {
        $field = TextField::make('name');
        $field->value = 'unchanged';

        $field->renderForDisplay();

        $this->assertSame('unchanged', $field->value);
    }

    #[Test]
    public function view_field_renderForDisplay_materialises_the_partial(): void
    {
        // Build a one-off view file the field can render.
        $viewDir = __DIR__ . '/views';
        if (! is_dir($viewDir)) {
            mkdir($viewDir, recursive: true);
        }
        $viewFile = $viewDir . '/echo.blade.php';
        file_put_contents($viewFile, '<span>HELLO {{ $payload }}</span>');

        try {
            $field = ViewField::make('payload');
            $field->view('rfp::echo');
            $field->value = 'world';

            $field->renderForDisplay();

            $this->assertStringContainsString('HELLO', $field->value);
            $this->assertStringContainsString('world', $field->value);
        } finally {
            @unlink($viewFile);
        }
    }

    #[Test]
    public function row_formatter_no_longer_references_concrete_field_types(): void
    {
        // Structural assertion: RowFormatter's source must not contain
        // 'instanceof' followed by a concrete *Field type. The only
        // remaining instanceof is the base Field type guard and the
        // RowAction guard, both of which are interface-level.
        $source = file_get_contents(__DIR__ . '/../../../src/Datatable/RowFormatter.php');

        $this->assertStringNotContainsString('instanceof ViewField', $source);
        $this->assertStringNotContainsString('instanceof TextField', $source);
        $this->assertStringNotContainsString('instanceof BelongsToField', $source);
        $this->assertStringNotContainsString('instanceof CurrencyField', $source);
        $this->assertStringNotContainsString('instanceof BooleanField', $source);
        $this->assertStringNotContainsString('instanceof SelectField', $source);
    }
}
