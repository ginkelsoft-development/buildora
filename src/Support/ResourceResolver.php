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

        $class = 'App\\Buildora\\Resources\\' . ucfirst($slug) . 'Buildora';

        if (!class_exists($class)) {
            throw new BuildoraException("No Buildora resource found for slug [{$slug}].");
        }

        $instance = app($class);

        if (! $instance instanceof BuildoraResource) {
            throw new BuildoraException("Resolved class [$class] is not a valid BuildoraResource.");
        }

        return $instance;
    }
}
