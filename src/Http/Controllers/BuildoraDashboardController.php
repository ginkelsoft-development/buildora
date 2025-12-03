<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Ginkelsoft\Buildora\Support\ResourceScanner;
use Ginkelsoft\Buildora\Support\ResourceResolver;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class BuildoraDashboardController extends Controller
{
    public function __invoke(string $name = 'main'): View
    {
        $dashboardConfig = config('buildora.dashboards');

        if (!$dashboardConfig || !($dashboardConfig['enabled'] ?? true)) {
            abort(404);
        }

        $this->authorizeDashboard($dashboardConfig);

        $widgets = collect($dashboardConfig['widgets'] ?? [])
            ->map(fn($class) => app($class));

        // Get statistics for the default dashboard
        $stats = $this->getDashboardStats();

        return view('buildora::dashboard', [
            'widgets' => $widgets,
            'title' => $dashboardConfig['label'] ?? __buildora('Dashboard'),
            'stats' => $stats,
        ]);
    }

    protected function authorizeDashboard(array $config): void
    {
        if (isset($config['permission']) && !auth()->user()?->can($config['permission'])) {
            abort(403);
        }
    }

    protected function getDashboardStats(): array
    {
        $stats = [];

        // Get all registered resources using ResourceScanner
        $resources = ResourceScanner::getResources();

        foreach ($resources as $resourceData) {
            try {
                $slug = $resourceData['name'];
                $resource = ResourceResolver::resolve($slug);
                $modelClass = $resource->getModelClass();

                if (class_exists($modelClass)) {
                    $count = $modelClass::count();
                    $stats[] = [
                        'label' => $resource->title(),
                        'value' => $count,
                        'slug' => $slug,
                        'icon' => method_exists($resource, 'icon') ? $resource->icon() : 'fa-solid fa-database',
                        'route' => route('buildora.index', ['resource' => $slug]),
                        'color' => $this->getColorForIndex(count($stats)),
                    ];
                }
            } catch (\Exception $e) {
                // Skip resources that fail
                continue;
            }
        }

        return $stats;
    }

    protected function getColorForIndex(int $index): string
    {
        $colors = [
            '#667eea', // Purple
            '#22c55e', // Green
            '#f59e0b', // Amber
            '#ef4444', // Red
            '#06b6d4', // Cyan
            '#8b5cf6', // Violet
            '#ec4899', // Pink
            '#14b8a6', // Teal
        ];

        return $colors[$index % count($colors)];
    }
}
