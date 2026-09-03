<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Resources;

use Ginkelsoft\Buildora\Resources\ModelResolver;
use Ginkelsoft\Buildora\Tests\Fixtures\ModelResolverCacheTestModel;
use Ginkelsoft\Buildora\Tests\Fixtures\ModelResolverCacheTestResource;
use Ginkelsoft\Buildora\Tests\Fixtures\ModelResolverCacheTestResourceTwo;
use Ginkelsoft\Buildora\Tests\TestCase;

class ModelResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Avoid state leaking between tests: each test starts with an empty
        // resolver cache and a clean call counter on the fixture resources.
        ModelResolver::clearCache();
        ModelResolverCacheTestResource::reset();
        ModelResolverCacheTestResourceTwo::reset();
    }

    protected function tearDown(): void
    {
        ModelResolver::clearCache();
        ModelResolverCacheTestResource::reset();
        ModelResolverCacheTestResourceTwo::reset();

        parent::tearDown();
    }

    /** @test */
    public function it_resolves_the_model_class_for_a_resource(): void
    {
        $modelClass = ModelResolver::resolve(ModelResolverCacheTestResource::class);

        $this->assertSame(ModelResolverCacheTestModel::class, $modelClass);
    }

    /** @test */
    public function it_caches_the_resolution_so_repeated_calls_do_not_redo_the_work(): void
    {
        ModelResolver::resolve(ModelResolverCacheTestResource::class);
        ModelResolver::resolve(ModelResolverCacheTestResource::class);
        $modelClass = ModelResolver::resolve(ModelResolverCacheTestResource::class);

        // modelClass() should only have been invoked once: subsequent calls
        // must be served from the per-process cache.
        $this->assertSame(1, ModelResolverCacheTestResource::$callCount);
        $this->assertSame(ModelResolverCacheTestModel::class, $modelClass);
    }

    /** @test */
    public function it_keeps_separate_cache_entries_per_resource_class(): void
    {
        ModelResolver::resolve(ModelResolverCacheTestResource::class);
        ModelResolver::resolve(ModelResolverCacheTestResourceTwo::class);
        ModelResolver::resolve(ModelResolverCacheTestResource::class);
        ModelResolver::resolve(ModelResolverCacheTestResourceTwo::class);

        $this->assertSame(1, ModelResolverCacheTestResource::$callCount);
        $this->assertSame(1, ModelResolverCacheTestResourceTwo::$callCount);
    }

    /** @test */
    public function clear_cache_forces_the_next_call_to_resolve_again(): void
    {
        ModelResolver::resolve(ModelResolverCacheTestResource::class);
        ModelResolver::clearCache();
        ModelResolver::resolve(ModelResolverCacheTestResource::class);

        $this->assertSame(2, ModelResolverCacheTestResource::$callCount);
    }
}
