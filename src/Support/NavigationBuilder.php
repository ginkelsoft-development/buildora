<?php

namespace Ginkelsoft\Buildora\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Ginkelsoft\Buildora\Support\ResourceScanner;

class NavigationBuilder
{
    /**
     * Generate the navigation structure based on config and available resources.
     *
     * @return array<string, mixed>
     */
    public static function getNavigation(): array
    {
        $navigation = config('buildora.navigation', []);
        $includeResources = $navigation['include_resources'] ?? true;
        unset($navigation['include_resources']);

        // Verzamel alle resources die al handmatig zijn toegevoegd
        $manualResources = collect($navigation)
            ->flatMap(function ($item) {
                if (!is_array($item)) return [];
                if (isset($item['params']['resource'])) {
                    return [$item['params']['resource']];
                }
                if (isset($item['children'])) {
                    return collect($item['children'])
                        ->pluck('params.resource')
                        ->filter()
                        ->all();
                }
                return [];
            })
            ->map(fn($slug) => Str::kebab($slug))
            ->values()
            ->all();

        if ($includeResources) {
            $resources = ResourceScanner::getResources();

            $filtered = array_filter($resources, function ($resource) use ($manualResources) {
                $slug = Str::kebab(str_replace('Buildora', '', $resource['resource']));
                return !in_array($slug, $manualResources);
            });

            if (!empty($filtered)) {
                $resourceNav = [
                    'label' => 'Resources',
                    'icon' => 'fa fa-database',
                    'children' => array_map(function ($resource) {
                        $class = 'App\\Buildora\\Resources\\' . ucfirst($resource['name']) . 'Buildora';

                        $label = Str::title(str_replace('Buildora', '', $resource['resource']));

                        if (class_exists($class)) {
                            $instance = new $class;
                            if (method_exists($instance, 'title')) {
                                $label = $instance->title();
                            }
                        }

                        return [
                            'label' => $label,
                            'icon' => 'fa fa-table',
                            'route' => 'buildora.index',
                            'params' => [
                                'resource' => Str::kebab(str_replace('Buildora', '', $resource['resource']))
                            ],
                        ];
                    }, $filtered),
                ];

                $navigation[] = $resourceNav;
            }
        }

        return $navigation;
    }

    /**
     * Generate a URL for a navigation item.
     *
     * @param array<string, mixed> $item
     * @return string|null
     */
    public static function getNavigationItemUrl(array $item): ?string
    {
        if (!isset($item['route']) || !Route::has($item['route'])) {
            return null;
        }

        return route($item['route'], $item['params'] ?? []);
    }

    /**
     * Determine whether a navigation item is active based on current URL.
     *
     * @param array<string, mixed> $item
     * @return bool
     */
    public static function isActive(array $item): bool
    {
        $currentUrl = request()->url();
        $url = self::getNavigationItemUrl($item);

        if (!$url) {
            return false;
        }

        return Str::startsWith($currentUrl, $url);
    }

    /**
     * Check if a parent navigation item has any active children.
     *
     * @param array<string, mixed> $item
     * @return bool
     */
    public static function isParentActive(array $item): bool
    {
        if (!isset($item['children']) || !is_array($item['children'])) {
            return false;
        }

        return collect($item['children'])->contains(fn($child) => self::isActive($child));
    }
}
