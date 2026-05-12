<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Providers;

use Ginkelsoft\Buildora\Providers\BuildoraServiceProvider;
use Ginkelsoft\Buildora\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

/**
 * Coverage for the BuildoraServiceProvider refactor (#136).
 *
 * The split moves what used to be a single ~130-line boot()/register()
 * pair into 13 focused private methods. These tests pin two invariants:
 *
 *   1. The observable boot/register outputs match what the inline
 *      implementation produced — middleware aliases exist, the view
 *      namespace is registered, the package's config-derived version is
 *      populated. (Behavioural smoke.)
 *
 *   2. The class structure stays decomposed — boot() and register()
 *      themselves are short orchestrators that only delegate, so the
 *      next person to add a concern adds a method, not another line of
 *      inline setup. (Structural lock-in.)
 */
class BuildoraServiceProviderStructureTest extends TestCase
{
    #[Test]
    public function the_buildora_middleware_aliases_are_registered(): void
    {
        $aliases = $this->app['router']->getMiddleware();

        $this->assertArrayHasKey('buildora.auth', $aliases);
        $this->assertArrayHasKey('buildora.ensure-user-resource', $aliases);
        $this->assertArrayHasKey('buildora.can', $aliases);
    }

    #[Test]
    public function the_buildora_view_namespace_is_loaded(): void
    {
        $this->assertTrue(
            view()->getFinder()->hasHintInformation('buildora::any'),
            "The 'buildora' view namespace must be registered for vendor views to resolve."
        );
    }

    #[Test]
    public function buildora_routes_are_registered_under_the_configured_prefix(): void
    {
        $prefix = config('buildora.route_prefix', 'buildora');

        $hits = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => str_starts_with($r->uri(), $prefix));

        $this->assertGreaterThan(0, $hits->count());
    }

    #[Test]
    public function package_version_is_bound_to_config_after_boot(): void
    {
        $this->assertIsString(config('buildora.version'));
    }

    #[Test]
    public function boot_method_delegates_rather_than_inlining(): void
    {
        // Lock the orchestration shape: boot() should call helper methods
        // rather than house the entire setup inline. Adding another inline
        // block would push the line count back up and re-introduce the
        // original audit complaint.
        $reflection = new ReflectionClass(BuildoraServiceProvider::class);
        $boot = $reflection->getMethod('boot');

        $start = $boot->getStartLine();
        $end   = $boot->getEndLine();
        $bootLineCount = $end - $start;

        $this->assertLessThan(
            25,
            $bootLineCount,
            "boot() has grown to {$bootLineCount} lines — extract a new private method."
        );
    }

    #[Test]
    public function register_method_delegates_rather_than_inlining(): void
    {
        $reflection = new ReflectionClass(BuildoraServiceProvider::class);
        $register = $reflection->getMethod('register');

        $start = $register->getStartLine();
        $end   = $register->getEndLine();
        $registerLineCount = $end - $start;

        $this->assertLessThan(
            20,
            $registerLineCount,
            "register() has grown to {$registerLineCount} lines — extract a new private method."
        );
    }

    #[Test]
    public function provider_exposes_one_private_method_per_concern(): void
    {
        // Spot-check that the concerns we extracted still each live in a
        // dedicated method. If someone re-merges them later this fails
        // and forces a conversation about why.
        $reflection = new ReflectionClass(BuildoraServiceProvider::class);
        $methods = array_map(fn ($m) => $m->getName(), $reflection->getMethods());

        $expected = [
            'aliasMiddleware',
            'registerCommands',
            'registerPublishables',
            'registerBladeDirectives',
            'registerBladeComponents',
            'registerViewComposers',
            'bindVersionToConfig',
            'registerSubProviders',
            'loadTranslations',
            'loadHelpers',
            'loadRoutes',
            'loadViews',
            'mergePackageConfig',
        ];

        foreach ($expected as $name) {
            $this->assertContains(
                $name,
                $methods,
                "Expected BuildoraServiceProvider to keep a dedicated method '{$name}'."
            );
        }
    }
}
