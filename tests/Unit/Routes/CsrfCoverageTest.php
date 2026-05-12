<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Routes;

use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Routing\Route as RouteInstance;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;

/**
 * Audit guard for issue #124.
 *
 * Buildora's routes are registered inside a group with the configurable
 * `buildora.middleware` stack — which defaults to ['web', …]. The `web`
 * middleware group is what gives every non-GET endpoint its CSRF token
 * check (VerifyCsrfToken sits in the framework's web group).
 *
 * That setup is fine *as long as* nobody removes 'web' from the middleware
 * config in their consuming app. This test pins the invariant: every
 * POST/PUT/PATCH/DELETE route Buildora registers must inherit either 'web'
 * (the group) or VerifyCsrfToken (the concrete middleware) — so a regression
 * (someone trimming the middleware list, or registering a new POST route
 * outside the group) is caught in CI rather than in production.
 */
class CsrfCoverageTest extends TestCase
{
    private const NON_GET_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    #[Test]
    public function the_default_middleware_config_includes_web(): void
    {
        $middleware = config('buildora.middleware', []);

        $this->assertContains(
            'web',
            $middleware,
            "Removing 'web' from buildora.middleware drops CSRF protection from every Buildora POST/PUT/DELETE route. If you have a deliberate reason to do this, this guard is the wrong place — register your own VerifyCsrfToken middleware instead."
        );
    }

    #[Test]
    public function every_non_get_buildora_route_has_csrf_middleware(): void
    {
        $offenders = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (! $this->isBuildoraRoute($route)) {
                continue;
            }

            $mutatingMethods = array_intersect($route->methods(), self::NON_GET_METHODS);
            if (empty($mutatingMethods)) {
                continue;
            }

            if (! $this->hasCsrfCoverage($route)) {
                $offenders[] = sprintf(
                    '%s %s (middleware: %s)',
                    implode('|', $mutatingMethods),
                    $route->uri(),
                    implode(', ', $this->routeMiddlewareSnapshot($route)) ?: '<none>'
                );
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Found Buildora routes that mutate state without CSRF coverage:\n - " . implode("\n - ", $offenders)
        );
    }

    private function isBuildoraRoute(RouteInstance $route): bool
    {
        $prefix = config('buildora.route_prefix', 'buildora');

        return str_starts_with($route->uri(), $prefix);
    }

    private function hasCsrfCoverage(RouteInstance $route): bool
    {
        // We deliberately use middleware() rather than gatherMiddleware():
        // gatherMiddleware() resolves the controller (so it can collect
        // controller-level middleware), which means Laravel instantiates the
        // controller class. Some Buildora controllers depend on optional
        // packages (e.g. PragmaRX\Google2FA) that may not be installed in
        // every environment. For CSRF coverage we only care about the route-
        // and group-level middleware anyway — that is where 'web' lives.
        $middleware = $route->middleware();

        foreach ($middleware as $entry) {
            if ($entry === 'web') {
                return true;
            }

            if (is_string($entry) && str_contains($entry, 'VerifyCsrfToken')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function routeMiddlewareSnapshot(RouteInstance $route): array
    {
        return array_filter($route->middleware(), 'is_string');
    }
}
