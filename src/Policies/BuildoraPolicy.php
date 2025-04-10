<?php

namespace Ginkelsoft\Buildora\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

abstract class BuildoraPolicy
{
    use HandlesAuthorization;

    abstract protected function resourceName(): string;

    protected function hasPermission(User $user, string $action): bool
    {
        $permission = "{$this->resourceName()}.{$action}";

        return $user->hasPermissionTo($permission);
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function view(User $user, $model): bool
    {
        return $this->hasPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    public function update(User $user, $model): bool
    {
        return $this->hasPermission($user, 'edit');
    }

    public function delete(User $user, $model): bool
    {
        return $this->hasPermission($user, 'delete');
    }
}
