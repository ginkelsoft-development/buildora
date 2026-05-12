<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Resources\QueryFactory;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

class QFArticle extends Model
{
    use HasBuildora;

    protected $table = 'qf_articles';
    protected $guarded = [];
    public $timestamps = false;

    public function tags()
    {
        return $this->hasMany(QFTag::class, 'article_id');
    }
}

class QFTag extends Model
{
    use HasBuildora;

    protected $table = 'qf_tags';
    protected $guarded = [];
    public $timestamps = false;
}

class QFResourceWithPanels extends BuildoraResource
{
    public static function modelClass(): string
    {
        return QFArticle::class;
    }

    public function defineFields(): array
    {
        return [];
    }

    public function getRelationResources(): array
    {
        return [
            ['relationName' => 'tags'],
            ['relationName' => null],          // filtered out
            ['relationName' => 'tags'],        // duplicate, deduped
        ];
    }
}

class QFResourceWithoutPanels extends BuildoraResource
{
    public static function modelClass(): string
    {
        return QFArticle::class;
    }

    public function defineFields(): array
    {
        return [];
    }

    public function getRelationResources(): array
    {
        return [];
    }
}

/**
 * QueryFactory exposes two intent-revealing entry points:
 *   - forList()  : never eager-loads panel relations (datatable, exports)
 *   - forDetail(): eager-loads everything declared via getRelationResources()
 *
 * These tests pin the routing: forList must NOT call with(), forDetail must
 * call it with the deduplicated relation names.
 */
class QueryFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('qf_articles', function ($table) {
            $table->increments('id');
            $table->string('title')->nullable();
        });

        Schema::create('qf_tags', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('article_id');
            $table->string('name')->nullable();
        });
    }

    #[Test]
    public function for_list_returns_a_buildora_query_builder(): void
    {
        $builder = QueryFactory::forList(new QFResourceWithPanels());

        $this->assertInstanceOf(\Ginkelsoft\Buildora\BuildoraQueryBuilder::class, $builder);
    }

    #[Test]
    public function for_list_does_not_eager_load_panel_relations(): void
    {
        $builder = QueryFactory::forList(new QFResourceWithPanels());
        $eagerLoads = $builder->getEagerLoads();

        $this->assertSame(
            [],
            array_keys($eagerLoads),
            'forList() must not stage any with() calls — panels are not rendered on list views.'
        );
    }

    #[Test]
    public function for_detail_eager_loads_panel_relations(): void
    {
        $builder = QueryFactory::forDetail(new QFResourceWithPanels());
        $eagerLoads = $builder->getEagerLoads();

        $this->assertContains('tags', array_keys($eagerLoads));
    }

    #[Test]
    public function for_detail_dedupes_and_filters_relation_names(): void
    {
        $builder = QueryFactory::forDetail(new QFResourceWithPanels());
        $eagerLoads = $builder->getEagerLoads();

        $tagsCount = count(array_filter(array_keys($eagerLoads), fn ($k) => $k === 'tags'));
        $this->assertSame(1, $tagsCount);
    }

    #[Test]
    public function for_detail_is_a_noop_when_resource_declares_no_panels(): void
    {
        $builder = QueryFactory::forDetail(new QFResourceWithoutPanels());
        $eagerLoads = $builder->getEagerLoads();

        $this->assertSame([], array_keys($eagerLoads));
    }
}
