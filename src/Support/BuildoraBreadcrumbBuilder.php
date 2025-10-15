<?php

namespace Ginkelsoft\Buildora\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

/**
 * Class BreadcrumbBuilder
 *
 * Generates breadcrumb navigation for Buildora routes.
 */
class BuildoraBreadcrumbBuilder
{
    /**
     * Generate breadcrumbs based on the current URL and available Buildora resources.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    public static function generate(): array
    {
        $buildoraBreadcrumbs = [];
        $segments = Request::segments();
        $path = '';

        foreach ($segments as $index => $segment) {
            // Skip numeric segments and certain reserved keywords
            if (is_numeric($segment) || in_array(strtolower($segment), ['buildora', 'resource'])) {
                continue;
            }

            $path = '/buildora/resource/' . $segment;
            $isLast = ($index === array_key_last($segments));

            $label = ucfirst(str_replace(['-', '_'], ' ', $segment));

            // Override with the resource label if available
            if ($resourceMeta = self::findResourceBySegment($segment)) {
                $class = 'App\\Buildora\\Resources\\' . ucfirst($resourceMeta['name']) . 'Buildora';

                if (class_exists($class)) {
                    $resource = new $class();
                    $label = method_exists($resource, 'title') ? $resource->title() : $resourceMeta['label'] ?? $label;
                }
            }

            $buildoraBreadcrumbs[] = [
                'label' => $label,
                'url' => $isLast ? null : url($path),
            ];
        }

        return $buildoraBreadcrumbs;
    }

    /**
     * Find a Buildora resource based on the URL segment.
     *
     * @param string $segment
     * @return array|null
     */
    private static function findResourceBySegment(string $segment): ?array
    {
        foreach (ResourceScanner::getResources() as $resource) {
            if (Str::kebab($resource['name']) === Str::kebab($segment)) {
                return $resource;
            }
        }

        return null;
    }
}
