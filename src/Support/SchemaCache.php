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

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($table, $connection) {
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
        Cache::forget($cacheKey);
    }

    /**
     * Clear all schema caches.
     *
     * @return void
     */
    public static function flush(): void
    {
        Cache::tags(['buildora-schema'])->flush();
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
}
