<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Finder\SplFileInfo;

class BuildoraSyncPermissionsCommand extends Command
{
    protected $signature = 'buildora:sync-permissions';
    protected $description = 'Generate CRUD permissions for all Buildora resources';

    public function handle(): int
    {
        $resourcePath = app_path('Buildora/Resources');
        $namespaceBase = 'App\\Buildora\\Resources\\';

        if (!File::exists($resourcePath)) {
            $this->error('Resource path not found: ' . $resourcePath);
            return self::FAILURE;
        }

        /** @var SplFileInfo[] $files */
        $files = File::allFiles($resourcePath);

        foreach ($files as $file) {
            $class = $namespaceBase . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (!class_exists($class)) {
                $this->warn("Skipping invalid class: $class");
                continue;
            }

            try {
                $resource = new $class();
                $modelClass = get_class($resource->getModelInstance());
            } catch (\Throwable $e) {
                $this->warn("Skipping $class — instantiation failed: " . $e->getMessage());
                continue;
            }

            if (!$modelClass || !class_exists($modelClass)) {
                $this->warn("Skipping resource without valid model: $class");
                continue;
            }

            $modelName = str(class_basename($modelClass))->lower();

            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permission = "$modelName.$action";
                Permission::findOrCreate($permission);
                $this->line("✓ Registered: <comment>$permission</comment>");
            }
        }

        $this->info('All Buildora permissions are synced!');
        return self::SUCCESS;
    }
}
