<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Providers;

use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;

class BuildoraServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_routes_under_the_configured_prefix(): void
    {
        $prefix = config('buildora.route_prefix', 'buildora');

        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), $prefix . '/') || $route->uri() === $prefix);

        $this->assertGreaterThan(
            0,
            $routes->count(),
            "Expected Buildora routes to be registered under the '{$prefix}' prefix."
        );
    }

    #[Test]
    public function it_publishes_the_main_config(): void
    {
        $this->assertNotNull(
            config('buildora'),
            'Buildora config should be available after the service provider boots.'
        );

        $this->assertSame('buildora', config('buildora.route_prefix'));
    }

    #[Test]
    public function it_registers_the_buildora_view_namespace(): void
    {
        $this->assertTrue(
            view()->getFinder()->hasHintInformation('buildora::dummy'),
            "Expected 'buildora' view namespace to be registered."
        );
    }
}
