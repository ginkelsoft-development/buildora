<?php

namespace Ginkelsoft\Buildora\Dashboards;

class DashboardManager
{
    public static function all(): array
    {
        return array_filter(config('buildora.dashboards', []), fn ($dashboard) => $dashboard['enabled'] ?? false);
    }

    public static function get(string $name): ?array
    {
        $dashboard = config("buildora.dashboards.$name");
        return ($dashboard && $dashboard['enabled']) ? $dashboard : null;
    }

    public static function widgets(string $name): array
    {
        $dashboard = self::get($name);
        if (! $dashboard) {
            return [];
        }

        return collect($dashboard['widgets'])
            ->map(fn (string $widgetClass) => app($widgetClass))
            ->filter()
            ->toArray();
    }
}
