<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class BuildoraDashboardController extends Controller
{
    public function __invoke(string $name = 'main'): View
    {
        $dashboardConfig = config("buildora.dashboards.children.{$name}");

        abort_unless($dashboardConfig, 404);

        $this->authorizeDashboard($dashboardConfig);

        $widgets = collect($dashboardConfig['widgets'] ?? [])
            ->map(fn($class) => app($class));

        return view('buildora::dashboard', [
            'widgets' => $widgets,
            'title' => $dashboardConfig['label'] ?? ucfirst($name),
        ]);
    }

    protected function authorizeDashboard(array $config): void
    {
        if (isset($config['permission']) && !auth()->user()?->can($config['permission'])) {
            abort(403);
        }
    }
}
