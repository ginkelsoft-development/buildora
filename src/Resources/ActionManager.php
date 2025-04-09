<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\Actions\BulkAction;

class ActionManager
{
    /**
     * Convert row actions into array representations for frontend use.
     *
     * @param array $actions Array of RowAction objects.
     * @param object $resource The resource instance used for resolving action parameters.
     * @return array Array of row actions with URL, method, label, etc.
     */
    public static function resolveRowActions(array $actions, object $resource): array
    {
        return collect($actions)
            ->filter(fn($action) => self::canAccessAction($action))
            ->map(fn($action) => $action->toArray($resource))
            ->values()
            ->all();
    }

    /**
     * Ensure that all bulk actions are instances of BulkAction.
     *
     * @param array $actions Array of BulkAction objects or action arrays.
     * @return array Array of BulkAction instances.
     */
    public static function resolveBulkActions(array $actions): array
    {
        return collect($actions)
            ->filter(fn($action) => self::canAccessAction($action))
            ->map(function ($action): BulkAction {
                if (! $action instanceof BulkAction) {
                    return BulkAction::make(
                        $action['label'] ?? 'Unnamed Action',
                        $action['route'] ?? '#',
                        $action['parameters'] ?? []
                    )->method($action['method'] ?? 'GET');
                }

                return $action;
            })
            ->toArray();
    }

    /**
     * Check if the user can access a given action.
     *
     * @param mixed $action
     * @return bool
     */
    protected static function canAccessAction(mixed $action): bool
    {
        if (! auth()->check()) {
            return false;
        }

        if (method_exists($action, 'getPermission')) {
            $permission = $action->getPermission();
            if ($permission) {
                return auth()->user()->can($permission);
            }
        }

        return true;
    }
}
