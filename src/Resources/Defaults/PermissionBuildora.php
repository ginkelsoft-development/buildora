<?php

namespace Ginkelsoft\Buildora\Resources\Defaults;

use Ginkelsoft\Buildora\Resources\ModelResource;
use Spatie\Permission\Models\Permission;

class PermissionBuildora extends ModelResource
{
    protected static string $model = Permission::class;
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

        if ($configured) {
            return $configured;
        }

        return parent::modelClass();
    }
}
