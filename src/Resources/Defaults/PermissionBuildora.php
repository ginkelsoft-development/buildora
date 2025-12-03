<?php

namespace Ginkelsoft\Buildora\Resources\Defaults;

use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\SelectField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Spatie\Permission\Models\Permission;

class PermissionBuildora extends BuildoraResource
{
    protected static string $model = Permission::class;

    public function title(): string
    {
        return __buildora('Permissions');
    }

    public function searchResultConfig(): array
    {
        return [
            'label' => 'label',
            'columns' => ['name', 'label'],
        ];
    }

    public function defineFields(): array
    {
        return [
            IDField::make('id', __buildora('ID'))
                ->readonly()
                ->hideFromTable()
                ->hideFromExport()
                ->hideFromCreate()
                ->hideFromEdit(),

            TextField::make('label', __buildora('Label'))
                ->sortable()
                ->searchable()
                ->hideFromCreate()
                ->hideFromEdit(),

            TextField::make('name', __buildora('Name'))
                ->help(__buildora('Technical name of the permission, preferably in dot notation.'))
                ->validation(['required', 'string', 'max:255']),

            SelectField::make('guard_name', __buildora('Guard'))
                ->options($this->guardOptions())
                ->readonly(count($this->guardOptions()) <= 1)
                ->help(__buildora('Select the guard for which this permission applies.'))
                ->validation(['required', 'string', 'max:255']),
        ];
    }

    public function defineRowActions(): array
    {
        return [
            RowAction::make(__buildora('View'), 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('permission.view'),

            RowAction::make(__buildora('Edit'), 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('permission.edit'),

            RowAction::make(__buildora('Delete'), 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['id' => 'id'])
                ->permission('permission.delete')
                ->confirm(__buildora('Are you sure you want to delete this permission?')),
        ];
    }

    public function defineBulkActions(): array
    {
        return [];
    }

    public function defineWidgets(): array
    {
        return [];
    }

    public function definePanels(): array
    {
        return [];
    }

    private function guardOptions(): array
    {
        $guards = array_keys(config('auth.guards', ['web' => []]));

        if (empty($guards)) {
            $guards = ['web'];
        }

        $options = [];

        foreach ($guards as $guard) {
            $options[$guard] = ucfirst(str_replace(['_', '-'], ' ', $guard));
        }

        return $options;
    }
}
