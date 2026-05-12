<?php

namespace Ginkelsoft\Buildora\Authorization;

/**
 * Buildora-managed permission verbs.
 *
 * The four verbs map directly to the policy methods (viewAny/view, create,
 * update, delete) and to the permission strings stored in Spatie. They were
 * sprinkled as string literals throughout the codebase (policies, commands,
 * middleware, controllers); centralising them here gives IDE autocomplete,
 * a single point of typo-protection, and a typesafe `permissionString()`
 * helper.
 *
 * Values are intentionally identical to the strings that were already in
 * use, so existing permission rows in the database remain valid without
 * a migration.
 */
enum BuildoraAbility: string
{
    case View = 'view';
    case Create = 'create';
    case Edit = 'edit';
    case Delete = 'delete';

    /**
     * Compose the dotted permission identifier for a resource, matching the
     * format Spatie expects (e.g. "user.view").
     */
    public function permissionString(string $resource): string
    {
        return "{$resource}.{$this->value}";
    }

    /**
     * The default verb set generated for every resource.
     *
     * @return array<int, self>
     */
    public static function defaults(): array
    {
        return [self::View, self::Create, self::Edit, self::Delete];
    }
}
