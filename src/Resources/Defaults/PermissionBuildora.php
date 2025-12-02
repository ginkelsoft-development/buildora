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
        return 'Rechten';
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
            IDField::make('id')
                ->readonly()
                ->hideFromTable()
                ->hideFromExport()
                ->hideFromCreate()
                ->hideFromEdit(),

            TextField::make('name', 'Permission')
                ->help('Technische naam van de permissie, bij voorkeur in dot-notatie.')
                ->validation(['required', 'string', 'max:255']),

            TextField::make('label', 'Label')
                ->help('Leesbare label dat in de interface wordt gebruikt.')
                ->validation(['required', 'string', 'max:255']),

            SelectField::make('guard_name', 'Guard')
                ->options($this->guardOptions())
                ->readonly(count($this->guardOptions()) <= 1)
                ->help('Selecteer de guard waarvoor deze permissie geldt.')
                ->validation(['required', 'string', 'max:255']),
        ];
    }

    public function defineRowActions(): array
    {
        return [
            RowAction::make('View', 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('permission.view'),

            RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('permission.edit'),

            RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['id' => 'id'])
                ->permission('permission.delete')
                ->confirm('Are you sure you want to delete this permission?'),
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
