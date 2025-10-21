<?php

namespace Ginkelsoft\Buildora\Resources\Defaults;

use Ginkelsoft\Buildora\Models\Permission as BuildoraPermission;
use Ginkelsoft\Buildora\Resources\ModelResource;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Spatie\Permission\Models\Permission as SpatiePermission;

class PermissionBuildora extends ModelResource
{
    protected static string $model = BuildoraPermission::class;
    protected array $excludeFields = ['guard_name'];

    public function title(): string
    {
        return 'Permission';
    }

    public function searchResultConfig(): array
    {
        return [
            'label' => 'name',
            'columns' => ['name'],
        ];
    }

    protected function confirmDeleteMessage(): string
    {
        return 'Are you sure you want to delete this permission?';
    }

    public static function modelClass(): string
    {
        $configured = config('permission.models.permission');

        if (! $configured) {
            return BuildoraPermission::class;
        }

        if (! class_exists($configured)) {
            throw new \RuntimeException("Configured permission model [{$configured}] does not exist.");
        }

        if (! in_array(HasBuildora::class, class_uses_recursive($configured))) {
            if ($configured === SpatiePermission::class) {
                return BuildoraPermission::class;
            }

            throw new \RuntimeException(
                "Configured permission model [{$configured}] must use the HasBuildora trait."
            );
        }

        return $configured;
    }
}
