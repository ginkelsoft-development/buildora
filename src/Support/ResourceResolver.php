<?php

namespace Ginkelsoft\Buildora\Support;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Resources\BuildoraResource;

class ResourceResolver
{
    /**
     * Resolve the Buildora resource class for a given slug (e.g. 'users').
     *
     * @param string $slug The slug for the resource (e.g. 'user').
     * @return BuildoraResource|null The resolved resource instance or null if excluded.
     * @throws BuildoraException If the class doesn't exist or is invalid.
     */
    public static function resolve(string $slug): BuildoraResource|null
    {
        $excluded = ['dashboard', 'search'];

        if (in_array($slug, $excluded, true)) {
            return null;
        }

        $classBase = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $slug)));
        $class = "App\\Buildora\\Resources\\{$classBase}Buildora";

        if (!class_exists($class)) {
            throw new BuildoraException("No Buildora resource found for slug [{$slug}].");
        }

        $instance = app($class);

        if (! $instance instanceof BuildoraResource) {
            throw new BuildoraException("Resolved class [$class] is not a valid BuildoraResource.");
        }

        return $instance;
    }

    public static function resolveFromMethod(object $model, string $method): BuildoraResource
    {
        if (! method_exists($model, $method)) {
            throw new \Exception("Method [{$method}] does not exist on model [" . get_class($model) . "]");
        }

        $relation = $model->{$method}();

        if (! $relation instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
            throw new \Exception("Method [{$method}] is not a valid Eloquent relation.");
        }

        $relatedModel = $relation->getRelated();
        $base = class_basename($relatedModel);

        $resourceClass = "App\\Buildora\\Resources\\{$base}Buildora";

        if (! class_exists($resourceClass)) {
            throw new \Exception("Buildora resource [{$resourceClass}] not found.");
        }

        return app($resourceClass);
    }
}
