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
     * Resolve the model class for a given Buildora resource class.
     *
     * @param string $resourceClass The fully qualified class name of the resource.
     * @return string The fully qualified class name of the associated model.
     */
    public static function resolve(string $resourceClass): string
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
