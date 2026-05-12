<?php

namespace Ginkelsoft\Buildora\Policies;

use App\Models\User;
use Ginkelsoft\Buildora\Authorization\BuildoraAbility;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BuildoraPolicy
{
    use HandlesAuthorization;

    abstract protected function resourceName(): string;

    protected function hasPermission(User $user, BuildoraAbility $ability): bool
    {
        return $user->hasPermissionTo($ability->permissionString($this->resourceName()));
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, BuildoraAbility::View);
    }

    public function view(User $user, $model): bool
    {
        return $this->hasPermission($user, BuildoraAbility::View);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, BuildoraAbility::Create);
    }

    public function update(User $user, $model): bool
    {
        return $this->hasPermission($user, BuildoraAbility::Edit);
    }

    public function delete(User $user, $model): bool
    {
        return $this->hasPermission($user, BuildoraAbility::Delete);
    }
}
