<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\Support\BuildoraValidator;

/**
 * Class ModelResolver
 *
 * Resolves the associated model class from a given Buildora resource class.
 *
 * The mapping is deterministic and immutable for the duration of a process,
 * so the resolution result is memoised per request to avoid repeating the
 * method_exists / property_exists / class_exists checks (which trigger the
 * autoloader on every call) for resources that are touched multiple times
 * per request — common when panels, datatables and detail views reference
 * the same resource.
 */
class ModelResolver
{
    /**
     * @var array<string, string> resourceClass => modelClass
     */
    private static array $cache = [];

    /**
     * Resolve the model class for a given Buildora resource class.
     *
     * @param string $resourceClass The fully qualified class name of the resource.
     * @return string The fully qualified class name of the associated model.
     */
    public static function resolve(string $resourceClass): string
    {
        if (isset(self::$cache[$resourceClass])) {
            return self::$cache[$resourceClass];
        }

        return self::$cache[$resourceClass] = self::resolveUncached($resourceClass);
    }

    /**
     * Drop the memoised mappings. Primarily for tests; in normal operation
     * the cache is naturally bounded by the number of registered resources.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    private static function resolveUncached(string $resourceClass): string
    {
        if (method_exists($resourceClass, 'modelClass')) {
            $modelClass = $resourceClass::modelClass();
        } else {
            $modelClass = null;

            if (property_exists($resourceClass, 'model')) {
                $modelClass = $resourceClass::$model ?? null;
            }

            if (! $modelClass) {
                $namespace = config('buildora.models_namespace', 'App\\Models\\');
                $modelClass = $namespace . str_replace('Buildora', '', class_basename($resourceClass));
            }
        }

        BuildoraValidator::assertValidModel($modelClass);

        return $modelClass;
    }
}
