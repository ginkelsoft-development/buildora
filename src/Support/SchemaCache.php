<?php

namespace Ginkelsoft\Buildora\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Class SchemaCache
 *
 * Caches database schema information to avoid expensive Schema::getColumnListing() calls.
 */
class SchemaCache
{
    protected const CACHE_TAG = 'buildora-schema';

    /**
     * Get cached column listing for a table.
     *
     * @param string $table
     * @param string|null $connection
     * @return array
     */
    public static function getColumnListing(string $table, ?string $connection = null): array
    {
        $cacheKey = self::getCacheKey($table, $connection);

        return self::rememberWithTags($cacheKey, function () use ($table, $connection) {
            return Schema::connection($connection)->getColumnListing($table);
        });
    }

    /**
     * Clear cached schema for a specific table.
     *
     * @param string $table
     * @param string|null $connection
     * @return void
     */
    public static function forget(string $table, ?string $connection = null): void
    {
        $cacheKey = self::getCacheKey($table, $connection);
        self::forgetWithTags($cacheKey);
    }

    /**
     * Clear all schema caches.
     *
     * @return void
     */
    public static function flush(): void
    {
        if (self::supportsTags()) {
            Cache::tags([self::CACHE_TAG])->flush();
        }
    }

    /**
     * Generate cache key for table schema.
     *
     * @param string $table
     * @param string|null $connection
     * @return string
     */
    protected static function getCacheKey(string $table, ?string $connection = null): string
    {
        $connection = $connection ?? config('database.default');
        return "buildora.schema.{$connection}.{$table}";
    }

    protected static function supportsTags(): bool
    {
        $store = Cache::getStore();
        return method_exists($store, 'tags');
    }

    protected static function rememberWithTags(string $key, \Closure $callback): array
    {
        $ttl = now()->addHours(24);

        if (self::supportsTags()) {
            return Cache::tags([self::CACHE_TAG])->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    protected static function forgetWithTags(string $key): void
    {
        if (self::supportsTags()) {
            Cache::tags([self::CACHE_TAG])->forget($key);
            return;
        }

        Cache::forget($key);
    }
}
