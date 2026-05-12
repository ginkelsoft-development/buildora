<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Resources\Concerns;

use Ginkelsoft\Buildora\BuildoraQueryBuilder;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Resources\Concerns\HasResourceQuery;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

class RQArticle extends Model
{
    use HasBuildora;
    protected $table = 'rq_articles';
    protected $guarded = [];
    public $timestamps = false;
}

class RQArticleBuildora extends BuildoraResource
{
    public static function modelClass(): string
    {
        return RQArticle::class;
    }

    public function defineFields(): array
    {
        return [];
    }

    public function getRelationResources(): array
    {
        return [
            ['relationName' => 'comments'],
        ];
    }
}

class HasResourceQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('rq_articles', function ($t) {
            $t->increments('id');
            $t->string('title')->nullable();
        });
    }

    #[Test]
    public function query_returns_a_buildora_query_builder(): void
    {
        $this->assertInstanceOf(BuildoraQueryBuilder::class, RQArticleBuildora::query());
    }

    #[Test]
    public function query_does_not_eager_load_panel_relations(): void
    {
        // Behavioural sanity: query() must skip the panel eager-load path
        // (that's the whole point of the list/detail split).
        $eagerLoads = RQArticleBuildora::query()->getEagerLoads();

        $this->assertSame([], array_keys($eagerLoads));
    }

    #[Test]
    public function query_with_relations_eager_loads_declared_panels(): void
    {
        $eagerLoads = RQArticleBuildora::queryWithRelations()->getEagerLoads();

        $this->assertContains('comments', array_keys($eagerLoads));
    }

    #[Test]
    public function buildora_resource_uses_the_query_trait(): void
    {
        $traits = (new ReflectionClass(BuildoraResource::class))->getTraitNames();

        $this->assertContains(HasResourceQuery::class, $traits);
    }

    #[Test]
    public function query_methods_are_no_longer_inlined_in_buildora_resource(): void
    {
        $resourceSource = file_get_contents(
            (new ReflectionClass(BuildoraResource::class))->getFileName()
        );

        foreach (['query', 'queryWithRelations'] as $movedMethod) {
            $this->assertStringNotContainsString(
                "public static function {$movedMethod}(",
                $resourceSource,
                "Method '{$movedMethod}' has been re-inlined into BuildoraResource.php — it should live in HasResourceQuery trait."
            );
        }

        $traitSource = file_get_contents(
            (new ReflectionClass(HasResourceQuery::class))->getFileName()
        );

        foreach (['query', 'queryWithRelations'] as $movedMethod) {
            $this->assertStringContainsString(
                "function {$movedMethod}(",
                $traitSource,
            );
        }
    }
}
