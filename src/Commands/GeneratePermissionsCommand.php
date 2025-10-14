<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Spatie\Permission\Models\Permission;
use ReflectionClass;
use Illuminate\Support\Facades\File;

class GeneratePermissionsCommand extends Command
{
    protected $signature = 'buildora:generate-permissions';

    protected $description = 'Generate permissions for all Buildora resources.';

    public function handle(): void
    {
        if (! class_exists(\Spatie\Permission\Models\Permission::class)) {
            $this->error(
                'Spatie Permissions package is not installed. Please run: composer require spatie/laravel-permission'
            );
            return;
        }

        // Forceer het laden van alle Buildora Resource klassen
        foreach (File::allFiles(app_path('Buildora/Resources')) as $file) {
            require_once $file->getPathname();
        }

        $resources = collect(get_declared_classes())
            ->filter(fn ($class) =>
                is_subclass_of($class, BuildoraResource::class)
                && !(new ReflectionClass($class))->isAbstract());

        if ($resources->isEmpty()) {
            $this->warn('No Buildora resources found.');
            return;
        }

        foreach ($resources as $resourceClass) {
            $resource = new $resourceClass();
            $resourceName = $resource->uriKey();

            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                Permission::findOrCreate("{$resourceName}.{$action}");
                $this->info("Created permission: {$resourceName}.{$action}");
            }
        }

        $this->info('Permissions successfully generated.');
    }
}
