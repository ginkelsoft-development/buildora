<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Datatable;

use Ginkelsoft\Buildora\Datatable\ColumnBuilder;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Stub resource that exposes:
 *   - a getFields() method returning a fixed set of Field instances
 *   - a counter for the number of getFields() invocations, so the cache
 *     behaviour can be observed without timing tricks.
 *
 * Two separate stub classes (A and B) are used to verify that cache keys
 * are scoped per resource class.
 */
class StubResourceA
{
    public static int $calls = 0;

    public function getFields(): array
    {
        self::$calls++;

        return [
            tap(TextField::make('name'), function ($field) {
                $field->visibility = ['table' => true];
                $field->sortable = true;
            }),
            tap(TextField::make('secret'), function ($field) {
                $field->visibility = ['table' => false];
            }),
            tap(TextField::make('email'), function ($field) {
                $field->visibility = ['table' => true];
                $field->sortable = false;
            }),
        ];
    }
}

class StubResourceB
{
    public static int $calls = 0;

    public function getFields(): array
    {
        self::$calls++;

        return [
            tap(TextField::make('title'), function ($field) {
                $field->visibility = ['table' => true];
                $field->sortable = false;
            }),
        ];
    }
}

class ColumnBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ColumnBuilder::clearCache();
        StubResourceA::$calls = 0;
        StubResourceB::$calls = 0;
    }

    #[Test]
    public function it_returns_only_table_visible_columns(): void
    {
        $columns = ColumnBuilder::build(new StubResourceA());

        $names = array_column($columns, 'name');
        $this->assertContains('name', $names);
        $this->assertContains('email', $names);
        $this->assertNotContains('secret', $names);
    }

    #[Test]
    public function each_column_has_the_expected_shape(): void
    {
        $columns = ColumnBuilder::build(new StubResourceA());

        foreach ($columns as $column) {
            $this->assertIsString($column['name']);
            $this->assertIsBool($column['sortable']);
            $this->assertIsString($column['label']);
        }
    }

    #[Test]
    public function sortable_flag_reflects_the_field_setting(): void
    {
        $columns = ColumnBuilder::build(new StubResourceA());
        $byName = array_column($columns, null, 'name');

        $this->assertTrue($byName['name']['sortable']);
        $this->assertFalse($byName['email']['sortable']);
    }

    #[Test]
    public function it_memoises_the_result_per_resource_class(): void
    {
        ColumnBuilder::build(new StubResourceA());
        ColumnBuilder::build(new StubResourceA());
        ColumnBuilder::build(new StubResourceA());

        $this->assertSame(
            1,
            StubResourceA::$calls,
            'Expected getFields() to be invoked only once across three build() calls.'
        );
    }

    #[Test]
    public function cache_is_keyed_on_resource_class_not_instance(): void
    {
        ColumnBuilder::build(new StubResourceA());
        ColumnBuilder::build(new StubResourceA()); // different instance, same class

        $this->assertSame(1, StubResourceA::$calls);
    }

    #[Test]
    public function different_resource_classes_have_independent_caches(): void
    {
        ColumnBuilder::build(new StubResourceA());
        ColumnBuilder::build(new StubResourceB());

        $this->assertSame(1, StubResourceA::$calls);
        $this->assertSame(1, StubResourceB::$calls);
    }

    #[Test]
    public function clear_cache_forces_recomputation(): void
    {
        ColumnBuilder::build(new StubResourceA());
        ColumnBuilder::clearCache();
        ColumnBuilder::build(new StubResourceA());

        $this->assertSame(2, StubResourceA::$calls);
    }
}
