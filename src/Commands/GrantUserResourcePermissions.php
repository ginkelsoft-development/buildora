<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class GrantUserResourcePermissions extends Command
{
    protected $signature = 'buildora:permission:grant-resource
                            {user_id : The ID of the user}
                            {resource : The resource name (e.g. "user")}';

    protected $description = 'Grant full CRUD permissions on a given resource to a specific user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $resource = $this->argument('resource');

        /** @var \App\Models\User|null $user */
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return self::FAILURE;
        }

        $permissions = [
            "{$resource}.view",
            "{$resource}.create",
            "{$resource}.edit",
            "{$resource}.delete",
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::findOrCreate($permissionName);
            if (!$user->hasPermissionTo($permission)) {
                $user->givePermissionTo($permission);
                $this->info("Granted permission: {$permissionName}");
            } else {
                $this->line("User already has permission: {$permissionName}");
            }
        }

        $this->info("✅ All permissions granted successfully to user ID {$userId} for resource '{$resource}'.");

        return self::SUCCESS;
    }
}
