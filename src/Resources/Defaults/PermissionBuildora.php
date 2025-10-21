<?php

namespace Ginkelsoft\Buildora\Resources\Defaults;

use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\ModelResource;
use Illuminate\Database\Eloquent\Model;
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

    protected function finalizeFields(array $fields, Model $model): array
    {
        $labelFieldIndex = null;

        foreach ($fields as $index => $field) {
            if ($field->name === 'label') {
                $labelFieldIndex = $index;
                break;
            }
        }

        $labelField = $labelFieldIndex !== null
            ? $fields[$labelFieldIndex]
            : TextField::make('label', 'Label');

        $labelField
            ->validation(['required', 'string', 'max:255'])
            ->help('Dit label wordt gebruikt om permissies leesbaar te tonen in de interface.');

        if ($labelFieldIndex === null) {
            $fields[] = $labelField;
        } else {
            $fields[$labelFieldIndex] = $labelField;
        }

        return parent::finalizeFields($fields, $model);
    }

    public static function modelClass(): string
    {
        return config('permission.models.permission', Permission::class);
    }
}
