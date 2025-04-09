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

        // Verzamel handmatig gedefinieerde resources om duplicatie te vermijden
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

        // Automatisch Buildora resources toevoegen (tenzij uitgesloten)
        if ($includeResources) {
            $resources = ResourceScanner::getResources();

            $filtered = array_filter($resources, function ($resource) use ($manualResources) {
                $slug = Str::kebab(str_replace('Buildora', '', $resource['resource']));

                // Sla handmatige items over
                if (in_array($slug, $manualResources)) {
                    return false;
                }

                // Check permissie: alleen tonen als user mag "resource.view"
                return auth()->check() && auth()->user()->can("{$slug}.view");
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

        // ⛔ Filter alles op rechten (ook handmatig gedefinieerde items)
        return self::filterNavigationByPermissions($navigation);
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

    /**
     * Recursively filter navigation items based on permissions.
     *
     * @param array<string, mixed> $items
     * @return array<string, mixed>
     */
    protected static function filterNavigationByPermissions(array $items): array
    {
        return collect($items)->filter(function ($item) {
            // Check direct resource
            if (isset($item['params']['resource'])) {
                $resource = $item['params']['resource'];
                $permission = "{$resource}.view";
                if (!auth()->check() || !auth()->user()->can($permission)) {
                    return false;
                }
            }

            // Check children recursively
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = self::filterNavigationByPermissions($item['children']);
                return count($item['children']) > 0;
            }

            return true;
        })->values()->all();
    }
}
