<?php

namespace Ginkelsoft\Buildora\Tests\Fixtures;

/**
 * Second fixture resource used to test ModelResolver caching behaviour.
 *
 * Used alongside ModelResolverCacheTestResource to assert that cache
 * entries are kept separate per resource class.
 */
class ModelResolverCacheTestResourceTwo
{
    public static int $callCount = 0;

    public static function modelClass(): string
    {
        self::$callCount++;

        return ModelResolverCacheTestModel::class;
    }

    public static function reset(): void
    {
        self::$callCount = 0;
    }
}
