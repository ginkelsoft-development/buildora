<?php

namespace Ginkelsoft\Buildora\Providers;

use Illuminate\Support\ServiceProvider;

class BuildoraDatatableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services related to Buildora Datatable.
     *
     * This method is reserved for binding classes or configuration required by the datatable component.
     *
     * @return void
     */
    public function register(): void
    {
        // Placeholder for future bindings (e.g., filters, formatters, exporters)
    }

    /**
     * Bootstrap any services required by the Buildora Datatable package.
     *
     * This method is typically used to publish config files, register macros, or define extensions.
     *
     * @return void
     */
    public function boot(): void
    {
        // Placeholder for publishing config, registering macros or extensions
    }
}
