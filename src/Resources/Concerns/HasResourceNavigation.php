<?php

namespace Ginkelsoft\Buildora\Resources\Concerns;

/**
 * Resource navigation/identification surface, extracted from BuildoraResource
 * as the second decomposition step for #135.
 *
 * Houses the four overridable hooks that decide:
 *   - how the resource identifies itself in the URL (slug)
 *   - how it labels itself in the admin nav and breadcrumbs (title)
 *   - whether it shows up in the nav at all (showInNavigation)
 *   - what the global search uses to label and search across rows
 *     (searchResultConfig)
 *
 * Like HasResourceActions, this is a trait rather than a value object so
 * that the subclass-overridable hook semantics stay intact — a consumer's
 * UserBuildora overrides title()/searchResultConfig() and expects \$this
 * dispatch to find them.
 */
trait HasResourceNavigation
{
    /**
     * Human-readable label for the resource. Defaults to the model class
     * basename.
     */
    public function title(): string
    {
        return class_basename($this->modelClass);
    }

    /**
     * Configuration consumed by GlobalSearchController. The 'label' may be
     * a column name, an array of column names (concatenated), or a callable
     * receiving the model instance. 'columns' lists the columns scanned for
     * `LIKE %term%` matches.
     *
     * The defaults below are tuned to the package's built-in UserBuildora;
     * every other resource should override.
     *
     * @return array{label: string|array|callable, columns: string[]}
     */
    public function searchResultConfig(): array
    {
        return [
            'label'   => ['voornaam', 'achternaam'],
            'columns' => ['voornaam', 'achternaam', 'emailadres'],
        ];
    }

    /**
     * Whether to include this resource in the admin navigation. Subclasses
     * return false to keep a resource available via direct URLs (e.g. for
     * relation panels) without surfacing it in the menu.
     */
    public function showInNavigation(): bool
    {
        return true;
    }

    /**
     * URL slug derived from the class basename:
     *   `App\\Buildora\\Resources\\CouponBuildora` → 'coupon'
     *
     * Buildora routes match resources on this value, and Spatie permissions
     * follow the {slug}.{verb} convention (see BuildoraAbility), so changing
     * this mid-flight requires migrating both routes and the permissions
     * table — override with care.
     */
    public static function slug(): string
    {
        return str_replace('buildora', '', strtolower(class_basename(static::class)));
    }
}
