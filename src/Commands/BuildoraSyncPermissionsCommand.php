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

        $hasLabelColumn = \Schema::hasColumn('permissions', 'label');

        /** @var SplFileInfo[] $files */
        $files = File::allFiles($resourcePath);

        foreach ($files as $file) {
            $class = $namespaceBase . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (!class_exists($class)) {
                $this->warn("Skipping invalid class: $class");
                continue;
            }

            // Special case: PermissionBuildora => use known model name
            if ($class === 'App\\Buildora\\Resources\\PermissionBuildora') {
                $modelName = 'permission';
            } else {
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
            }

            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permissionName = "$modelName.$action";
                $permission = Permission::findOrCreate($permissionName);

                if ($hasLabelColumn && empty($permission->label)) {
                    $generatedLabel = ucfirst($action) . ' ' . str_replace('_', ' ', str($modelName)->headline());
                    $permission->label = $generatedLabel;
                    $permission->save();
                    $this->line(
                        "✓ Registered: <comment>$permissionName</comment> with label <info>$generatedLabel</info>"
                    );
                } else {
                    $this->line("✓ Registered: <comment>$permissionName</comment>");
                }
            }
        }

        $this->info('All Buildora permissions are synced!');
        return self::SUCCESS;
    }
}
