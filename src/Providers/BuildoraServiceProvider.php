<?php

namespace Ginkelsoft\Buildora\Providers;

use Ginkelsoft\Buildora\View\Components\BuildoraIcon;
use Ginkelsoft\Buildora\View\Components\BuildoraLayout;
use Ginkelsoft\Buildora\View\Components\BuildoraGuestLayout;
use Ginkelsoft\Buildora\Support\BreadcrumbBuilder;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Ginkelsoft\Buildora\Http\Middleware\BuildoraAuthenticate;
use Ginkelsoft\Buildora\Http\Middleware\EnsureUserResourceExists;

/**
 * Class BuildoraServiceProvider
 *
 * This service provider bootstraps the Buildora package by:
 * - Registering custom Blade components
 * - Publishing config, views, and asset files
 * - Registering middleware
 * - Registering CLI commands
 * - Loading routes and views
 */
class BuildoraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap Buildora package services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register middleware aliases
        $this->app['router']->aliasMiddleware('buildora.auth', BuildoraAuthenticate::class);
        $this->app['router']->aliasMiddleware('buildora.ensure-user-resource', EnsureUserResourceExists::class);

        // Register package commands
        $this->commands([
            \Ginkelsoft\Buildora\Commands\MakeBuildoraResource::class,
            \Ginkelsoft\Buildora\Commands\CreateUser::class,
            \Ginkelsoft\Buildora\Commands\MakeBuildoraWidget::class,
        ]);

        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../../config/buildora.php' => config_path('buildora.php'),
        ], 'buildora-config');

        // Publish view templates
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/buildora'),
        ], 'buildora-views');

        // Publish JS and CSS assets
        $this->publishes([
            __DIR__ . '/../../resources/css' => resource_path('vendor/buildora/css'),
            __DIR__ . '/../../resources/js' => resource_path('vendor/buildora/js'),
        ], 'buildora-assets');

        // Blade directives
        Blade::if('fontawesome', fn () => config('buildora.enable_fontawesome', true));

        // Blade components
        Blade::component('Ginkelsoft\\Buildora\\View\\Components\\Button\\Back', 'buildora.button.back');
        Blade::component('Ginkelsoft\\Buildora\\View\\Components\\Button\\Save', 'buildora.button.save');
        Blade::componentNamespace('Ginkelsoft\\Buildora\\View\\Components', 'buildora');
        Blade::component('buildora-layout', BuildoraLayout::class);
        Blade::component('buildora-guest-layout', BuildoraGuestLayout::class);
        Blade::component('buildora-icon', BuildoraIcon::class);

        // Share breadcrumbs with all views
        View::composer('*', function ($view): void {
            $view->with('breadcrumbs', BreadcrumbBuilder::generate());
        });
    }

    /**
     * Register Buildora application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register other Buildora service providers
        $this->app->register(\Ginkelsoft\Buildora\Providers\BuildoraDatatableServiceProvider::class);

        // Load Buildora routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/buildora.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/auth.php');

        // Load views and merge configuration
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'buildora');
        $this->mergeConfigFrom(__DIR__ . '/../../config/buildora.php', 'buildora');
    }
}
