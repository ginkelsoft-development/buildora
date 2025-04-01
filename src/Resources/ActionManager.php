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
        return array_map(fn($action) => $action->toArray($resource), $actions);
    }

    /**
     * Ensure that all bulk actions are instances of BulkAction.
     *
     * @param array $actions Array of BulkAction objects or action arrays.
     * @return array Array of BulkAction instances.
     */
    public static function resolveBulkActions(array $actions): array
    {
        return collect($actions)->map(function ($action): BulkAction {
            if (! $action instanceof BulkAction) {
                return BulkAction::make(
                    $action['label'] ?? 'Unnamed Action',
                    $action['route'] ?? '#',
                    $action['parameters'] ?? []
                )->method($action['method'] ?? 'GET');
            }

            return $action;
        })->toArray();
    }
}
