<?php

namespace Ginkelsoft\Buildora\Resources\Concerns;

use Ginkelsoft\Buildora\Exports\ExportManager;
use Ginkelsoft\Buildora\Resources\ActionManager;

/**
 * Resource-action surface, extracted from BuildoraResource as the first
 * step of #135 (god-object decomposition).
 *
 * Why a trait and not a value object: the action hooks are defined by
 * subclasses (a consumer's CouponBuildora overrides defineRowActions()),
 * so they must remain callable via `\$this` from inside the resource class
 * itself. Pulling them into a trait keeps that contract intact while
 * physically separating the 60-odd lines from BuildoraResource — and
 * gives a single home for the per-concern unit tests.
 *
 * Behaviour is byte-identical to the inline implementation; this is a
 * structural move only.
 */
trait HasResourceActions
{
    /**
     * Row-level actions (edit, delete, custom buttons in each table row).
     * Subclasses override.
     *
     * @return array
     */
    public function defineRowActions(): array
    {
        return [];
    }

    /**
     * Bulk actions applied to multiple selected rows. Subclasses override
     * with their own; default export actions are appended in getBulkActions().
     *
     * @return array
     */
    public function defineBulkActions(): array
    {
        return [];
    }

    /**
     * Page-level actions shown above the datatable (e.g. "Import"). Subclasses
     * override.
     *
     * @return array
     */
    public function definePageActions(): array
    {
        return [];
    }

    /**
     * Page actions filtered by the current user's permissions. Actions
     * without a permission requirement are always returned; actions with a
     * permission are returned only when the authenticated user holds it.
     *
     * @return array
     */
    public function getPageActions(): array
    {
        return collect($this->definePageActions())
            ->filter(function ($action) {
                $permission = $action->getPermission();
                if ($permission && auth()->check()) {
                    return auth()->user()->can($permission);
                }
                return true;
            })
            ->values()
            ->toArray();
    }

    /**
     * Row actions resolved for a specific resource instance.
     *
     * @param object $resource
     * @return array
     */
    public function getRowActions(object $resource): array
    {
        return ActionManager::resolveRowActions($this->defineRowActions(), $resource);
    }

    /**
     * Bulk actions, merged with Buildora's default export actions. The
     * resource's own actions win on label conflict — callers can replace a
     * default export by declaring an action with the same label.
     *
     * @return array
     */
    public function getBulkActions(): array
    {
        $custom = collect(static::defineBulkActions());
        $default = collect(ExportManager::defaultBulkActions(static::slug()));

        return $custom
            ->keyBy(fn ($a) => $a->label)
            ->union($default->keyBy(fn ($a) => $a->getLabel()))
            ->values()
            ->toArray();
    }
}
