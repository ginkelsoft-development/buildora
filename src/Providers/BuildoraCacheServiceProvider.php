<?php

namespace Ginkelsoft\Buildora\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\FileStore;
use Illuminate\Filesystem\Filesystem;

class BuildoraCacheServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Registreer eigen cache driver
        Cache::extend('buildora_file', function () {
            return Cache::repository(new FileStore(
                new Filesystem(),
                storage_path('framework/cache/buildora')
            ));
        });

        // Dynamisch een cache store toevoegen aan Laravel config
        config()->set('cache.stores.buildora', [
            'driver' => 'buildora_file',
        ]);
    }
}
