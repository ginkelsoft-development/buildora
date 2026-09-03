<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Datatable;

use Ginkelsoft\Buildora\Datatable\DataFetcher;
use Ginkelsoft\Buildora\Support\SchemaCache;
use Ginkelsoft\Buildora\Tests\Fixtures\Models\DataFetcherTestModel;
use Ginkelsoft\Buildora\Tests\Fixtures\Resources\DataFetcherTestResource;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Issue #119: is DataFetcher kwetsbaar voor SQL injectie via de
 * sort/filter-kolomnaam?
 *
 * Bevinding: klassieke SQL-injectie via de kolomnaam was niet mogelijk,
 * omdat de opgegeven sorteerkolom altijd getoetst werd aan de echte
 * kolomnamen van de tabel (SchemaCache::getColumnListing) voordat hij in de
 * query terechtkwam; een payload als `name); DROP TABLE users;--` matcht
 * geen bestaande kolomnaam en wordt dus genegeerd.
 *
 * Wel bleek de bedoelde whitelist tegen de resource's defineFields() dode
 * code: de vergelijking `$col === $sortBy` kan nooit slagen omdat $col een
 * Field-object is (geen string/array), waardoor elk willekeurig veld dat
 * toevallig een bestaande databasekolom is - ook velden die niet met
 * ->sortable() zijn gemarkeerd, of die helemaal niet in de resource zijn
 * gedefinieerd - gebruikt kon worden als sorteerkolom. Dat is een broken
 * access control-gat (OWASP A01), gefixt door pas te sorteren op een veld
 * dat exact overeenkomt met een door de resource gedefinieerd EN expliciet
 * sortable() veld.
 */
class DataFetcherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('df_test_items');
        Schema::create('df_test_items', function ($table) {
            $table->id();
            $table->string('name');
            $table->integer('secret_score')->default(0);
        });

        DataFetcherTestModel::query()->insert([
            ['name' => 'Banaan', 'secret_score' => 3],
            ['name' => 'Appel', 'secret_score' => 1],
            ['name' => 'Citroen', 'secret_score' => 2],
        ]);

        SchemaCache::forget('df_test_items');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('df_test_items');
        parent::tearDown();
    }

    protected function makeFetcher(): DataFetcher
    {
        $resource = DataFetcherTestResource::make();
        $fields = $resource->resolveFields($resource->getModelInstance());

        return new DataFetcher(DataFetcherTestResource::class, $fields);
    }

    /** @test */
    public function sql_injectie_payload_in_sortby_wordt_genegeerd_en_breekt_de_tabel_niet(): void
    {
        $payload = 'name); DROP TABLE df_test_items;--';

        $result = $this->makeFetcher()->fetch(sortBy: $payload);

        // De payload mag nergens tot een query-fout leiden en de tabel moet
        // gewoon blijven bestaan (het bewijs dat er geen injectie plaatsvond).
        $this->assertTrue(Schema::hasTable('df_test_items'));
        $this->assertSame(3, $result->total());
    }

    /** @test */
    public function sorteren_op_een_niet_sortable_veld_wordt_genegeerd_ook_al_bestaat_de_kolom(): void
    {
        // "secret_score" bestaat echt in de database, maar is in de resource
        // bewust niet als sortable() gemarkeerd. De fix moet dit blokkeren,
        // ook al zou de oude "bestaat de kolom?"-check dit toestaan.
        $result = $this->makeFetcher()->fetch(sortBy: 'secret_score', sortDirection: 'asc');

        // Zonder sortering blijft de invoegvolgorde (per id) behouden i.p.v.
        // gesorteerd te worden op secret_score (wat Appel, Citroen, Banaan
        // zou opleveren).
        $this->assertSame(['Banaan', 'Appel', 'Citroen'], $this->namesOf($result));
    }

    /** @test */
    public function sorteren_op_een_wel_sortable_veld_werkt_gewoon(): void
    {
        $result = $this->makeFetcher()->fetch(sortBy: 'name', sortDirection: 'asc');

        $this->assertSame(['Appel', 'Banaan', 'Citroen'], $this->namesOf($result));
    }

    /**
     * @param \Illuminate\Contracts\Pagination\Paginator $result
     * @return array<int, string>
     */
    protected function namesOf($result): array
    {
        return collect($result->items())
            ->map(function ($resource) {
                foreach ($resource->getFields() as $field) {
                    if ($field->name === 'name') {
                        return $field->value;
                    }
                }

                return null;
            })
            ->values()
            ->all();
    }
}
