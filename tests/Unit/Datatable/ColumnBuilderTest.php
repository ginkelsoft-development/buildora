<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Datatable;

use Ginkelsoft\Buildora\Datatable\ColumnBuilder;
use Ginkelsoft\Buildora\Tests\TestCase;

class ColumnBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // De cache is statisch (process-breed); begin elke test met een schone lei.
        ColumnBuilder::clearCache();
        CountingFieldsResource::$defineFieldsCalls = 0;
        OtherCountingFieldsResource::$defineFieldsCalls = 0;
    }

    /** @test */
    public function het_bouwt_alleen_de_zichtbare_kolommen_op(): void
    {
        $columns = ColumnBuilder::build(new CountingFieldsResource());

        $this->assertSame([
            ['name' => 'naam', 'sortable' => true, 'label' => 'Naam'],
        ], $columns);
    }

    /** @test */
    public function een_tweede_build_voor_dezelfde_resource_class_roept_definefields_niet_opnieuw_aan(): void
    {
        $columns1 = ColumnBuilder::build(new CountingFieldsResource());

        // Bij een cache-miss doorloopt build() de velden (o.a. voor de
        // zichtbaarheidscheck); zolang dat aantal na de tweede aanroep niet
        // stijgt, is aangetoond dat defineFields() niet opnieuw draait.
        $callsAfterFirstBuild = CountingFieldsResource::$defineFieldsCalls;
        $this->assertGreaterThan(0, $callsAfterFirstBuild);

        // Nieuwe instantie van dezelfde resource-class, zoals bij een volgend datatable-request.
        $columns2 = ColumnBuilder::build(new CountingFieldsResource());

        $this->assertSame($columns1, $columns2);
        $this->assertSame(
            $callsAfterFirstBuild,
            CountingFieldsResource::$defineFieldsCalls,
            'defineFields() mag dankzij de ColumnBuilder-cache niet opnieuw worden aangeroepen voor dezelfde resource-class.'
        );
    }

    /** @test */
    public function verschillende_resource_classes_hebben_hun_eigen_cache_entry(): void
    {
        $columnsA = ColumnBuilder::build(new CountingFieldsResource());
        $columnsB = ColumnBuilder::build(new OtherCountingFieldsResource());

        $this->assertNotSame($columnsA, $columnsB);
        $this->assertGreaterThan(0, CountingFieldsResource::$defineFieldsCalls);
        $this->assertGreaterThan(0, OtherCountingFieldsResource::$defineFieldsCalls);
    }
}

/**
 * Test-double die het gedrag van een BuildoraResource nabootst: getFields()
 * levert de velden op basis van defineFields(). De teller maakt zichtbaar
 * hoe vaak defineFields() daadwerkelijk is uitgevoerd.
 */
class CountingFieldsResource
{
    public static int $defineFieldsCalls = 0;

    public function getFields(): array
    {
        return $this->defineFields();
    }

    public function defineFields(): array
    {
        self::$defineFieldsCalls++;

        return [
            (object) [
                'name' => 'naam',
                'label' => 'Naam',
                'sortable' => true,
                'visibility' => ['table' => true],
            ],
            (object) [
                'name' => 'verborgen',
                'label' => 'Verborgen',
                'sortable' => false,
                'visibility' => ['table' => false],
            ],
        ];
    }
}

class OtherCountingFieldsResource
{
    public static int $defineFieldsCalls = 0;

    public function getFields(): array
    {
        return $this->defineFields();
    }

    public function defineFields(): array
    {
        self::$defineFieldsCalls++;

        return [
            (object) [
                'name' => 'email',
                'label' => 'E-mailadres',
                'sortable' => false,
                'visibility' => ['table' => true],
            ],
        ];
    }
}
