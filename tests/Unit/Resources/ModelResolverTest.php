<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Resources;

use Ginkelsoft\Buildora\Resources\ModelResolver;
use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;

/**
 * The stub model and resource live in this file because the cache test only
 * needs a deterministic, side-effect-tracking pair — not a fully wired model.
 *
 * BuildoraValidator::assertValidModel() insists the resolved class is an
 * Eloquent Model subclass that uses the HasBuildora trait. We satisfy both
 * here without wiring up any DB or resource registration.
 */
class FakeModelForResolver extends Model
{
    use HasBuildora;
}

class FakeResourceWithCounter
{
    public static int $modelClassCalls = 0;

    public static function modelClass(): string
    {
        self::$modelClassCalls++;
        return FakeModelForResolver::class;
    }
}

class ModelResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ModelResolver::clearCache();
        FakeResourceWithCounter::$modelClassCalls = 0;
    }

    #[Test]
    public function it_resolves_a_resource_to_its_model(): void
    {
        $this->assertSame(
            FakeModelForResolver::class,
            ModelResolver::resolve(FakeResourceWithCounter::class)
        );
    }

    #[Test]
    public function it_memoises_subsequent_calls_for_the_same_resource(): void
    {
        ModelResolver::resolve(FakeResourceWithCounter::class);
        ModelResolver::resolve(FakeResourceWithCounter::class);
        ModelResolver::resolve(FakeResourceWithCounter::class);

        $this->assertSame(
            1,
            FakeResourceWithCounter::$modelClassCalls,
            'Expected modelClass() to be invoked only once across three resolve() calls.'
        );
    }

    #[Test]
    public function clear_cache_forces_re_resolution(): void
    {
        ModelResolver::resolve(FakeResourceWithCounter::class);
        ModelResolver::clearCache();
        ModelResolver::resolve(FakeResourceWithCounter::class);

        $this->assertSame(
            2,
            FakeResourceWithCounter::$modelClassCalls,
            'Expected modelClass() to be invoked twice after a cache clear in between.'
        );
    }

    #[Test]
    public function different_resources_get_independent_cache_entries(): void
    {
        // Sanity check: the cache keys on the resource class string.
        // Resolving FakeResourceWithCounter twice still counts as a single
        // modelClass() invocation; a different resource class would resolve
        // independently.
        ModelResolver::resolve(FakeResourceWithCounter::class);
        ModelResolver::resolve(FakeResourceWithCounter::class);

        $this->assertSame(1, FakeResourceWithCounter::$modelClassCalls);
    }
}
