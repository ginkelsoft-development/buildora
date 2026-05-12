<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Exports;

use Ginkelsoft\Buildora\Exports\BuildoraResourceExport;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

class XPProduct extends Model
{
    use HasBuildora;

    protected $table = 'xp_products';
    protected $guarded = [];
    public $timestamps = false;
}

class XPProductResource extends BuildoraResource
{
    public static function modelClass(): string
    {
        return XPProduct::class;
    }

    public function defineFields(): array
    {
        return [
            TextField::make('name', 'Naam'),
            TextField::make('sku', 'SKU')->hideFromExport(),
            TextField::make('price', 'Prijs'),
        ];
    }
}

/**
 * Coverage for the streaming export pipeline (#126).
 *
 * The original ExportManager materialised the whole result set with
 * $query->get() before returning. These tests check the contract of the
 * replacement BuildoraResourceExport:
 *
 *   - Headings are derived from declared fields, filtering out
 *     export-hidden ones.
 *   - Rows are mapped on a per-record basis (suitable for laravel-excel's
 *     chunked-reading pipeline).
 *   - query() returns the Eloquent builder unchanged so chunkReading can
 *     drive iteration itself.
 *   - chunkSize() returns a sensible bounded value.
 *
 * The headline assertion is the absence of a get() call: iterating the
 * full builder issues queries only via the chunkReading machinery, so
 * peak memory does not scale with row count.
 */
class BuildoraResourceExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('xp_products', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('sku');
            $table->string('price');
        });

        for ($i = 1; $i <= 5; $i++) {
            XPProduct::create([
                'name'  => "Product {$i}",
                'sku'   => "SKU-{$i}",
                'price' => (string) ($i * 10),
            ]);
        }
    }

    private function makeExport(): BuildoraResourceExport
    {
        $resource = new XPProductResource();
        return new BuildoraResourceExport(
            resource: $resource,
            query: XPProduct::query(),
            title: 'Products',
        );
    }

    #[Test]
    public function headings_come_from_export_visible_fields_only(): void
    {
        $export = $this->makeExport();

        $headings = $export->headings();

        // 'SKU' was hidden via ->hideFromExport().
        $this->assertSame(['Naam', 'Prijs'], $headings);
    }

    #[Test]
    public function map_returns_export_visible_values_for_a_single_record(): void
    {
        $export = $this->makeExport();
        $product = XPProduct::find(1);

        $row = $export->map($product);

        // The SKU column is hidden, so the row has two cells: name + price.
        $this->assertCount(2, $row);
        $this->assertContains('Product 1', $row);
        $this->assertContains('10', $row);
        $this->assertNotContains('SKU-1', $row);
    }

    #[Test]
    public function map_handles_array_and_object_values_by_serialising(): void
    {
        // Confirm the array/object coercion path is exercised through the
        // public surface. We can't trivially seed an array-valued field
        // without a relation, so we hit the private formatCellValue helper
        // via reflection.
        $export = $this->makeExport();
        $ref = new \ReflectionMethod($export, 'formatCellValue');
        $ref->setAccessible(true);

        $this->assertSame('a, b, c', $ref->invoke($export, ['a', 'b', 'c']));
        $this->assertSame('{"k":"v"}', $ref->invoke($export, (object) ['k' => 'v']));
        $this->assertSame('plain', $ref->invoke($export, 'plain'));
        $this->assertNull($ref->invoke($export, null));
    }

    #[Test]
    public function query_returns_the_eloquent_builder_unchanged(): void
    {
        $builder = XPProduct::query()->where('id', '>', 2);
        $resource = new XPProductResource();

        $export = new BuildoraResourceExport(
            resource: $resource,
            query: $builder,
            title: 'Filtered',
        );

        $this->assertInstanceOf(Builder::class, $export->query());
        // Same instance — no clone, no rebuild.
        $this->assertSame($builder, $export->query());
    }

    #[Test]
    public function chunk_size_is_a_sensible_bounded_default(): void
    {
        $export = $this->makeExport();

        $size = $export->chunkSize();

        $this->assertGreaterThan(0, $size);
        $this->assertLessThanOrEqual(1000, $size, 'Chunk size should stay small enough to bound peak memory.');
    }

    #[Test]
    public function chunk_size_is_configurable(): void
    {
        $resource = new XPProductResource();
        $export = new BuildoraResourceExport(
            resource: $resource,
            query: XPProduct::query(),
            title: 'Small chunks',
            chunkSize: 50,
        );

        $this->assertSame(50, $export->chunkSize());
    }

    #[Test]
    public function constructing_the_export_does_not_execute_a_query(): void
    {
        // The whole point of the refactor: instantiating the export must
        // not load any rows. laravel-excel pulls them itself, in chunks,
        // once download() runs.
        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->makeExport();

        $this->assertCount(
            0,
            DB::getQueryLog(),
            'Building the export must be lazy — no queries until the writer pulls chunks.'
        );
    }

    #[Test]
    public function title_is_returned_verbatim(): void
    {
        $export = $this->makeExport();

        $this->assertSame('Products', $export->title());
    }
}
