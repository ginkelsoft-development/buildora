<?php

namespace App\Buildora\Resources {

    use Ginkelsoft\Buildora\Fields\Types\TextField;
    use Ginkelsoft\Buildora\Resources\BuildoraResource;
    use Ginkelsoft\Buildora\Tests\Feature\Fixtures\SearchTestItem;

    /**
     * Test-dubbel voor een Buildora resource. Zit in de namespace die
     * GlobalSearchController hardcoded verwacht: App\Buildora\Resources\{Naam}Buildora.
     */
    class SearchableBuildora extends BuildoraResource
    {
        public static function modelClass(): string
        {
            return SearchTestItem::class;
        }

        public function searchResultConfig(): array
        {
            return [
                'label' => 'name',
                'columns' => ['name'],
            ];
        }

        public function defineFields(): array
        {
            return [
                TextField::make('name'),
            ];
        }
    }
}

namespace Ginkelsoft\Buildora\Tests\Feature\Fixtures {

    use Ginkelsoft\Buildora\Traits\HasBuildora;
    use Illuminate\Database\Eloquent\Model;

    class SearchTestItem extends Model
    {
        use HasBuildora;

        protected $table = 'search_test_items';
        protected $fillable = ['name'];
        public $timestamps = false;
    }
}

namespace Ginkelsoft\Buildora\Tests\Feature {

    use App\Buildora\Resources\SearchableBuildora;
    use Ginkelsoft\Buildora\Http\Controllers\GlobalSearchController;
    use Ginkelsoft\Buildora\Tests\Feature\Fixtures\SearchTestItem;
    use Ginkelsoft\Buildora\Tests\TestCase;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Schema;

    class GlobalSearchControllerTest extends TestCase
    {
        protected function setUp(): void
        {
            parent::setUp();

            Schema::create('search_test_items', function ($table) {
                $table->id();
                $table->string('name');
            });

            // Registreer de test-resource als enige "default" resource, zodat
            // GlobalSearchController alleen deze doorzoekt.
            config(['buildora.resources.defaults' => [
                'searchable' => [
                    'enabled' => true,
                    'class' => SearchableBuildora::class,
                ],
            ]]);

            // 15 rijen die matchen op de zoekterm "Match", dus meer dan de limiet van 10.
            for ($i = 1; $i <= 15; $i++) {
                SearchTestItem::create(['name' => "Match item {$i}"]);
            }
        }

        /** @test */
        public function it_limits_results_per_resource_to_ten_records_even_with_more_matches(): void
        {
            $request = Request::create('/buildora/global-search', 'GET', ['q' => 'Match']);

            $response = (new GlobalSearchController())->__invoke($request);
            $data = $response->getData(true);

            $this->assertCount(
                10,
                $data['results'],
                'Verwacht maximaal 10 resultaten voor deze resource, ondanks 15 matchende records.'
            );
        }

        /** @test */
        public function it_returns_fewer_results_when_less_than_ten_records_match(): void
        {
            SearchTestItem::query()->delete();
            SearchTestItem::create(['name' => 'Unieke term Alpha']);
            SearchTestItem::create(['name' => 'Unieke term Beta']);

            $request = Request::create('/buildora/global-search', 'GET', ['q' => 'Unieke term']);

            $response = (new GlobalSearchController())->__invoke($request);
            $data = $response->getData(true);

            $this->assertCount(2, $data['results']);
        }
    }
}
