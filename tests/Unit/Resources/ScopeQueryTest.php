<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Resources\QueryFactory;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

class SQDocument extends Model
{
    use HasBuildora;

    protected $table = 'sq_documents';
    protected $guarded = [];
    public $timestamps = false;
}

/**
 * Stub resource with no scope override — must surface every row.
 */
class SQOpenResource extends BuildoraResource
{
    public static function modelClass(): string
    {
        return SQDocument::class;
    }

    public function defineFields(): array
    {
        return [];
    }
}

/**
 * Stub resource that restricts queries to documents owned by a hard-coded
 * user id. Stand-in for the typical row-level auth pattern:
 *   ->where('owner_id', auth()->id())
 */
class SQOwnerScopedResource extends BuildoraResource
{
    public const ALLOWED_OWNER = 7;

    public static function modelClass(): string
    {
        return SQDocument::class;
    }

    public function defineFields(): array
    {
        return [];
    }

    public function scopeQuery(Builder $query): Builder
    {
        return $query->where('owner_id', self::ALLOWED_OWNER);
    }
}

/**
 * Pin the scopeQuery() hook contract:
 *   - Default: no-op (BC for existing consumers).
 *   - Override: SQL contains the scope's where clause, regardless of
 *     list vs detail mode, and the query genuinely filters at the DB.
 */
class ScopeQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('sq_documents', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id');
            $table->string('title')->nullable();
        });

        SQDocument::create(['owner_id' => 7,  'title' => 'mine']);
        SQDocument::create(['owner_id' => 7,  'title' => 'also mine']);
        SQDocument::create(['owner_id' => 99, 'title' => 'someone else']);
    }

    #[Test]
    public function default_scope_is_a_noop(): void
    {
        $sql = QueryFactory::make(new SQOpenResource())->toSql();

        $this->assertStringNotContainsString('where', strtolower($sql));
    }

    #[Test]
    public function override_adds_a_where_clause_to_list_queries(): void
    {
        $sql = QueryFactory::make(new SQOwnerScopedResource(), false)->toSql();

        $this->assertStringContainsString('owner_id', $sql);
    }

    #[Test]
    public function override_also_applies_in_detail_mode(): void
    {
        // The hook must run regardless of eager-loading. Otherwise a caller
        // could bypass auth by hitting /resource/{id} (detail view).
        $sql = QueryFactory::make(new SQOwnerScopedResource(), true)->toSql();

        $this->assertStringContainsString('owner_id', $sql);
    }

    #[Test]
    public function scope_actually_filters_rows_at_the_database(): void
    {
        $results = QueryFactory::make(new SQOwnerScopedResource())->get();

        $this->assertCount(2, $results, 'Expected only the two rows owned by user 7.');
    }

    #[Test]
    public function find_returns_null_for_records_outside_the_scope(): void
    {
        // Critical: a user with `*.view` cannot grab a record outside the
        // scope by going straight to /resource/{id}. The model just doesn't
        // resolve.
        $unauthorised = SQDocument::where('owner_id', 99)->first();

        $result = QueryFactory::make(new SQOwnerScopedResource())->find($unauthorised->id);

        $this->assertNull($result);
    }
}
