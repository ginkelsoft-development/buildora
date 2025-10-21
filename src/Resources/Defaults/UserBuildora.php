<?php

namespace Ginkelsoft\Buildora\Resources\Defaults;

use Ginkelsoft\Buildora\Resources\ModelResource;
use Illuminate\Database\Eloquent\Model;

class UserBuildora extends ModelResource
{
    protected array $excludeFields = ['remember_token'];
    protected bool $includeRelationFields = false;

    public function title(): string
    {
        return 'User';
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
        return 'Are you sure you want to delete this user?';
    }

    protected function additionalFields(Model $model): array
    {
        return [];
    }

    public static function modelClass(): string
    {
        $configured = config('auth.providers.users.model');

        if ($configured) {
            return $configured;
        }

        return parent::modelClass();
    }
}
