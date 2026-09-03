<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\Support\BuildoraValidator;

/**
 * Class ModelResolver
 *
 * Resolves the associated model class from a given Buildora resource class.
 */
class ModelResolver
{
    /**
     * Per-process cache of resolved model classes, keyed by resource class.
     *
     * @var array<string, string>
     */
    private static array $cache = [];

    /**
     * Resolve the model class for a given Buildora resource class.
     *
     * Results are cached per process so that repeated resolutions of the
     * same resource class (e.g. across a single request) don't redo the
     * same reflection/config lookups and validation.
     *
     * @param string $resourceClass The fully qualified class name of the resource.
     * @return string The fully qualified class name of the associated model.
     */
    public static function resolve(string $resourceClass): string
    {
        return self::$cache[$resourceClass] ??= self::doResolve($resourceClass);
    }

    /**
     * Clear the per-process resolution cache.
     *
     * Mainly useful in tests to avoid state leaking between test cases.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Perform the actual resolution of the model class for a resource.
     *
     * @param string $resourceClass The fully qualified class name of the resource.
     * @return string The fully qualified class name of the associated model.
     */
    private static function doResolve(string $resourceClass): string
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
