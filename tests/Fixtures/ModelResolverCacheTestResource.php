<?php

namespace Ginkelsoft\Buildora\Tests\Fixtures;

/**
 * Fixture resource used to test ModelResolver caching behaviour.
 *
 * Tracks how many times modelClass() has been called so tests can assert
 * that ModelResolver::resolve() only computes the resolution once per
 * resource class.
 */
class ModelResolverCacheTestResource
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
