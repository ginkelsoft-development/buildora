<?php

namespace Ginkelsoft\Buildora\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Class UrlBuilder
 *
 * This utility class is responsible for generating dynamic URLs for different action types.
 * It supports route-based URLs within Laravel, as well as external URLs.
 *
 * Usage:
 * - Generates URLs for named routes by extracting required parameters dynamically.
 * - Supports direct URL linking for external pages.
 * - Ensures that required parameters are always provided.
 */
class UrlBuilder
{
    /**
     * Builds a URL based on the given action type and value.
     *
     * @param string      $actionType       The type of action (e.g., 'route', 'url').
     * @param object|null $item             The resource instance containing required fields (optional).
     * @param array       $extraArguments   Additional parameters for route generation.
     *
     * @return string      The generated URL.
     * @throws InvalidArgumentException If an invalid action type is provided.
     */
    public static function build(
        string $actionType,
        string $actionValue,
        object $item = null,
        array $extraArguments = []
    ): string {
        if ($actionType === 'route') {
            return self::buildRoute($actionValue, $item, $extraArguments);
        }

        // ✅ Support external URLs
        if ($actionType === 'url') {
            return filter_var($actionValue, FILTER_VALIDATE_URL) ? $actionValue : URL::to($actionValue);
        }

        throw new InvalidArgumentException("Invalid action type: $actionType");
    }

    /**
     * Generates a Laravel route URL dynamically.
     *
     * @param string      $routeName        The name of the Laravel route.
     * @param object|null $item             The resource instance containing required fields (optional).
     * @param array       $extraArguments   Additional parameters for route generation.
     *
     * @return string      The generated route URL.
     * @throws InvalidArgumentException If the route does not exist or required parameters are missing.
     */
    private static function buildRoute(string $routeName, object $item = null, array $extraArguments = []): string
    {
        $routeDefinition = Route::getRoutes()->getByName($routeName);
        if (!$routeDefinition) {
            throw new InvalidArgumentException("Route [$routeName] not found.");
        }

        $parameters = [];

        // ✅ Identify required parameters from the route definition
        foreach ($routeDefinition->parameterNames() as $param) {
            // ✅ 1. 'resource' is always the resource slug, never from fields
            if ($param === 'resource') {
                $parameters['resource'] = self::extractResourceName($item, $routeName);
                continue;
            }

            // ✅ 2. Check if the parameter exists in the extra arguments
            if (isset($extraArguments[$param])) {
                $parameters[$param] = $extraArguments[$param];
                continue;
            }

            // ✅ 3. Check if the parameter exists in the resource fields
            if ($item && method_exists($item, 'getFields')) {
                $field = collect($item->getFields())->firstWhere('name', $param);

                if ($field && isset($field->value)) {
                    $parameters[$param] = $field->value;
                    continue;
                }
            }
        }

        // ✅ 5. Ensure all required parameters are present
        foreach ($routeDefinition->parameterNames() as $param) {
            if (!isset($parameters[$param])) {
                throw new InvalidArgumentException("Missing required route parameter [$param] for route [$routeName].");
            }
        }

        return route($routeName, $parameters);
    }

    /**
     * Extracts the resource name from the provided object or request.
     *
     * @param object|null $item       The resource instance (optional).
     * @param string      $routeName  The route name being processed.
     *
     * @return string      The extracted resource name in kebab-case.
     * @throws InvalidArgumentException If no resource name can be determined.
     */
    private static function extractResourceName(?object $item, string $routeName): string
    {
        if ($item) {
            return Str::kebab(str_replace('Buildora', '', class_basename($item)));
        }

        // 🚨 **Fallback: Extract from the current request if possible**
        if (request()->route('resource')) {
            return request()->route('resource');
        }

        throw new InvalidArgumentException("Missing required 'resource' parameter for route [$routeName].");
    }
}
