<?php

namespace Ginkelsoft\Buildora\Resources\Defaults;

use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Fields\Types\EmailField;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\PasswordField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;

class UserBuildora extends BuildoraResource
{
    public function title(): string
    {
        return 'Gebruikers';
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
        return 'Weet je zeker dat je deze gebruiker wilt verwijderen?';
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

            TextField::make('name', 'Naam')
                ->help('Volledige naam van de gebruiker.')
                ->validation(['required', 'string', 'max:255']),

            EmailField::make('email', 'E-mailadres')
                ->help('Uniek e-mailadres voor login en notificaties.')
                ->validation(['required', 'email', 'max:255']),

            PasswordField::make('password', 'Wachtwoord')
                ->hideFromTable()
                ->hideFromDetail()
                ->help('Laat leeg om het wachtwoord ongewijzigd te laten.')
                ->validation(fn ($model) => $model && $model->exists
                    ? ['nullable', 'string', 'min:8']
                    : ['required', 'string', 'min:8']
                ),
        ];
    }

    public function defineRowActions(): array
    {
        return [
            RowAction::make('View', 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('user.view'),

            RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->params(['id' => 'id'])
                ->permission('user.edit'),

            RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
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
