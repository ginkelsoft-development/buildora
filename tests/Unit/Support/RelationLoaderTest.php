<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Support;

use Ginkelsoft\Buildora\Support\RelationLoader;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

class RelationAuthor extends Model
{
    protected $table = 'rel_authors';
    protected $guarded = [];
    public $timestamps = false;

    public function posts(): HasMany
    {
        return $this->hasMany(RelationPost::class, 'author_id');
    }
}

class RelationPost extends Model
{
    protected $table = 'rel_posts';
    protected $guarded = [];
    public $timestamps = false;

    public function author(): BelongsTo
    {
        return $this->belongsTo(RelationAuthor::class, 'author_id');
    }
}

/**
 * Integration coverage for the eager-load-friendly RelationLoader and the
 * three relation fields that now route through it.
 *
 * The "expected query count" assertions are the core of the fix: previous
 * implementations issued one query per record per relation-field, even when
 * the caller eager-loaded upstream. After the change, the count must be
 * constant regardless of how many models are in the parent collection.
 */
class RelationLoaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('rel_authors', function ($table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('rel_posts', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('author_id');
            $table->string('title');
        });

        $a1 = RelationAuthor::create(['name' => 'Author One']);
        $a2 = RelationAuthor::create(['name' => 'Author Two']);

        RelationPost::create(['author_id' => $a1->id, 'title' => 'A1-P1']);
        RelationPost::create(['author_id' => $a1->id, 'title' => 'A1-P2']);
        RelationPost::create(['author_id' => $a2->id, 'title' => 'A2-P1']);
    }

    #[Test]
    public function manyFor_uses_the_loaded_relation_when_available(): void
    {
        $author = RelationAuthor::with('posts')->first();

        DB::enableQueryLog();
        DB::flushQueryLog();

        $items = RelationLoader::manyFor($author, 'posts');

        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(2, $items);
        $this->assertCount(
            0,
            DB::getQueryLog(),
            'Expected zero extra queries when the relation was already loaded.'
        );
    }

    #[Test]
    public function manyFor_falls_back_to_a_query_when_relation_is_not_loaded(): void
    {
        $author = RelationAuthor::first();

        DB::enableQueryLog();
        DB::flushQueryLog();

        $items = RelationLoader::manyFor($author, 'posts');

        $this->assertCount(2, $items);
        $this->assertGreaterThanOrEqual(
            1,
            count(DB::getQueryLog()),
            'Expected at least one query when the relation was not eager-loaded.'
        );
    }

    #[Test]
    public function manyFor_returns_empty_collection_for_unpersisted_models(): void
    {
        $author = new RelationAuthor(['name' => 'Unsaved']);

        $items = RelationLoader::manyFor($author, 'posts');

        $this->assertCount(0, $items);
    }

    #[Test]
    public function manyFor_returns_empty_collection_when_relation_method_is_missing(): void
    {
        $author = RelationAuthor::first();

        $items = RelationLoader::manyFor($author, 'nonExistentRelation');

        $this->assertCount(0, $items);
    }

    #[Test]
    public function oneFor_uses_the_loaded_relation_when_available(): void
    {
        $post = RelationPost::with('author')->first();

        DB::enableQueryLog();
        DB::flushQueryLog();

        $related = RelationLoader::oneFor($post, 'author');

        $this->assertInstanceOf(Model::class, $related);
        $this->assertSame('Author One', $related->name);
        $this->assertCount(
            0,
            DB::getQueryLog(),
            'Expected zero extra queries when the belongs-to relation was already loaded.'
        );
    }

    #[Test]
    public function oneFor_returns_null_when_no_related_row_exists(): void
    {
        $orphan = RelationPost::create(['author_id' => 999, 'title' => 'orphan']);

        $related = RelationLoader::oneFor($orphan, 'author');

        $this->assertNull($related);
    }

    #[Test]
    public function batch_iteration_with_eager_load_is_constant_query_count(): void
    {
        // This is the headline regression test for the N+1 fix. Iterating
        // 100 records and consulting RelationLoader for each must NOT scale
        // linearly with the row count.

        for ($i = 0; $i < 50; $i++) {
            $a = RelationAuthor::create(['name' => "Bulk {$i}"]);
            RelationPost::create(['author_id' => $a->id, 'title' => "p-{$i}"]);
        }

        $authors = RelationAuthor::with('posts')->get();

        DB::enableQueryLog();
        DB::flushQueryLog();

        foreach ($authors as $author) {
            RelationLoader::manyFor($author, 'posts');
        }

        $this->assertCount(
            0,
            DB::getQueryLog(),
            'Expected zero extra queries while iterating an eager-loaded collection.'
        );
    }
}
