<?php

namespace Ginkelsoft\Buildora\Resources\Defaults;

use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Fields\Types\CheckboxListField;
use Ginkelsoft\Buildora\Fields\Types\EmailField;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\PasswordField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Spatie\Permission\Models\Permission;

class UserBuildora extends BuildoraResource
{
    public function title(): string
    {
        return __buildora('Users');
    }

    public function searchResultConfig(): array
    {
        return [
            'label' => ['name', 'email'],
            'columns' => ['name', 'email'],
        ];
    }

    protected function confirmDeleteMessage(): string
    {
        return __buildora('Are you sure you want to delete this user?');
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

            TextField::make('name', __buildora('Name'))
                ->help(__buildora('Full name of the user.'))
                ->validation(['required', 'string', 'max:255']),

            EmailField::make('email', __buildora('Email'))
                ->help(__buildora('Unique email address for login and notifications.'))
                ->validation(['required', 'email', 'max:255']),

            PasswordField::make('password', __buildora('Password'))
                ->hideFromTable()
                ->hideFromDetail()
                ->help(__buildora('Leave empty to keep the password unchanged.'))
                ->validation(fn ($model) => $model && $model->exists
                    ? ['nullable', 'string', 'min:8']
                    : ['required', 'string', 'min:8']
                ),

            CheckboxListField::make('permissions', __buildora('Permissions'))
                ->relatedTo(Permission::class)
                ->pluck('id', 'name')
                ->groupByPrefix(true, '.')
                ->hideFromTable()
                ->columnSpan(['default' => 12])
                ->help(__buildora('Select the permissions for this user.')),
        ];
    }

    public function defineRowActions(): array
    {
        return [
            RowAction::make(__buildora('View'), 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('user.view'),

            RowAction::make(__buildora('Edit'), 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('user.edit'),

            RowAction::make(__buildora('Delete'), 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['id' => 'id'])
                ->permission('user.delete')
                ->confirm($this->confirmDeleteMessage()),
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

    public static function modelClass(): string
    {
        return config('auth.providers.users.model', '\App\Models\User');
    }
}
