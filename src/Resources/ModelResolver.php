<?php

namespace Ginkelsoft\Buildora\Resources;

use Illuminate\Support\Str;
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
        $modelClass = $resourceClass::$model ?? "App\\Models\\" . str_replace('Buildora', '', class_basename($resourceClass));

        BuildoraValidator::assertValidModel($modelClass);

        return $modelClass;
    }
}
