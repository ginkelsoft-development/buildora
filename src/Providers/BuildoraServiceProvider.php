<?php

namespace Ginkelsoft\Buildora\Providers;

use Ginkelsoft\Buildora\Commands\BuildoraSyncPermissionsCommand;
use Ginkelsoft\Buildora\Commands\CreateUser;
use Ginkelsoft\Buildora\Commands\GeneratePermissionsCommand;
use Ginkelsoft\Buildora\Commands\GrantUserResourcePermissions;
use Ginkelsoft\Buildora\Commands\InstallBuildoraCommand;
use Ginkelsoft\Buildora\Commands\MakeBuildoraResource;
use Ginkelsoft\Buildora\Commands\MakeBuildoraWidget;
use Ginkelsoft\Buildora\Commands\MakePermissionResourceCommand;
use Ginkelsoft\Buildora\Commands\UpgradeBuildoraCommand;
use Ginkelsoft\Buildora\Http\Middleware\BuildoraAuthenticate;
use Ginkelsoft\Buildora\Http\Middleware\CheckBuildoraPermission;
use Ginkelsoft\Buildora\Http\Middleware\EnsureUserResourceExists;
use Ginkelsoft\Buildora\Support\BuildoraBreadcrumbBuilder;
use Ginkelsoft\Buildora\View\Components\Button\Back;
use Ginkelsoft\Buildora\View\Components\Button\Save;
use Ginkelsoft\Buildora\View\Components\BuildoraGuestLayout;
use Ginkelsoft\Buildora\View\Components\BuildoraIcon;
use Ginkelsoft\Buildora\View\Components\BuildoraLayout;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Class BuildoraServiceProvider
 *
 * Top-level Laravel service provider for the Buildora package.
 *
 * The work is split into focused private methods so each concern is
 * isolated and individually testable. boot() and register() act as
 * orchestrators only — they call the helpers in the same order the
 * inline implementation used to, so boot-order behaviour is preserved.
 *
 * Future split into dedicated provider classes (BuildoraRouteServiceProvider,
 * BuildoraViewServiceProvider, etc.) can be done incrementally by moving
 * methods out of this class into a sub-provider that this one registers.
 * Until then, the structure here makes it obvious what would go where.
 */
class BuildoraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap Buildora package services.
     */
    public function boot(): void
    {
        $this->aliasMiddleware();
        $this->registerCommands();
        $this->registerPublishables();
        $this->registerBladeDirectives();
        $this->registerBladeComponents();
        $this->registerViewComposers();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->bindVersionToConfig();
    }

    /**
     * Register Buildora application services.
     */
    public function register(): void
    {
        $this->registerSubProviders();
        $this->loadTranslations();
        $this->loadHelpers();
        $this->loadRoutes();
        $this->loadViews();
        $this->mergePackageConfig();
    }

    // ---------------------------------------------------------------- boot()

    /**
     * Expose Buildora middleware under short aliases so routes can use them
     * without referencing the FQCN.
     */
    private function aliasMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('buildora.auth',                 BuildoraAuthenticate::class);
        $router->aliasMiddleware('buildora.ensure-user-resource', EnsureUserResourceExists::class);
        $router->aliasMiddleware('buildora.can',                  CheckBuildoraPermission::class);
    }

    /**
     * Register every artisan command shipped with the package.
     */
    private function registerCommands(): void
    {
        $this->commands([
            MakeBuildoraResource::class,
            CreateUser::class,
            MakeBuildoraWidget::class,
            GeneratePermissionsCommand::class,
            GrantUserResourcePermissions::class,
            InstallBuildoraCommand::class,
            UpgradeBuildoraCommand::class,
            BuildoraSyncPermissionsCommand::class,
            MakePermissionResourceCommand::class,
        ]);
    }

    /**
     * Publishable assets: theme, static files, config, docs, migrations.
     * Grouped under sensible vendor:publish tags.
     */
    private function registerPublishables(): void
    {
        $this->publishes([
            __DIR__ . '/../../resources/css/buildora-theme.css' => resource_path('buildora/buildora-theme.css'),
        ], 'buildora-theme');

        $this->publishes([
            __DIR__ . '/../../resources/assets' => public_path('vendor/buildora'),
        ], 'buildora-assets');

        $this->publishes([
            __DIR__ . '/../../config/buildora.php' => config_path('buildora.php'),
        ], 'buildora-config');

        $this->publishes([
            __DIR__ . '/../../config/larecipe.php' => config_path('larecipe.php'),
        ], 'buildora-docs-config');

        $this->publishes([
            __DIR__ . '/../../resources/docs' => resource_path('docs'),
        ], 'buildora-docs');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'buildora-migrations');
    }

    /**
     * Buildora-specific Blade directives.
     */
    private function registerBladeDirectives(): void
    {
        Blade::if('fontawesome', fn () => config('buildora.enable_fontawesome', true));
    }

    /**
     * Blade components and their tag aliases.
     */
    private function registerBladeComponents(): void
    {
        Blade::component(Back::class, 'buildora.button.back');
        Blade::component(Save::class, 'buildora.button.save');
        Blade::componentNamespace('Ginkelsoft\\Buildora\\View\\Components', 'buildora');
        Blade::component('buildora-layout',       BuildoraLayout::class);
        Blade::component('buildora-guest-layout', BuildoraGuestLayout::class);
        Blade::component('buildora-icon',         BuildoraIcon::class);
    }

    /**
     * Share the breadcrumb collection with every view.
     */
    private function registerViewComposers(): void
    {
        View::composer('*', function ($view): void {
            $view->with('buildoraBreadcrumbs', BuildoraBreadcrumbBuilder::generate());
        });
    }

    /**
     * Read the package version out of composer.json's extra section and
     * surface it at config('buildora.version'). Tolerant: missing file or
     * malformed JSON is a no-op rather than a boot failure.
     */
    private function bindVersionToConfig(): void
    {
        $composerPath = dirname(__DIR__, 2) . '/composer.json';

        if (! file_exists($composerPath)) {
            return;
        }

        $composer = json_decode(file_get_contents($composerPath), true);
        $version = $composer['extra']['buildora-version'] ?? 'dev';

        config(['buildora.version' => $version]);
    }

    // ------------------------------------------------------------ register()

    private function registerSubProviders(): void
    {
        $this->app->register(BuildoraDatatableServiceProvider::class);
    }

    private function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'buildora');
    }

    private function loadHelpers(): void
    {
        $helpers = __DIR__ . '/../helpers.php';
        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    private function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/buildora.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/auth.php');
    }

    private function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'buildora');
    }

    private function mergePackageConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/buildora.php', 'buildora');
    }
}
