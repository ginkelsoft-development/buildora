<?php

namespace Ginkelsoft\Buildora\Datatable;

/**
 * Class ColumnBuilder
 *
 * Responsible for building column definitions for a Buildora datatable.
 *
 * Column definitions are derived purely from a resource's defined fields and
 * their static visibility metadata — they never change between requests for
 * the same resource. The build output is therefore memoised per resource
 * class so subsequent datatable AJAX requests don't re-instantiate every
 * Field object on each call.
 *
 * The previous implementation also called $resource->getFields() N+1 times
 * per build (once in the outer map, then once per item in isVisibleInTable);
 * the refactored version walks the field list exactly once.
 */
class ColumnBuilder
{
    /**
     * @var array<class-string, array<int, array{name: string, sortable: bool, label: string}>>
     */
    private static array $cache = [];

    /**
     * Builds an array of visible column definitions for the given resource.
     *
     * @param object $resource The resource instance (must expose getFields()).
     * @return array<int, array{name: string, sortable: bool, label: string}>
     */
    public static function build(object $resource): array
    {
        $key = get_class($resource);

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $columns = [];

        foreach ($resource->getFields() as $field) {
            if (! ($field->visibility['table'] ?? false)) {
                continue;
            }

            $columns[] = [
                'name'     => $field->name,
                'sortable' => $field->sortable ?? false,
                'label'    => $field->label,
            ];
        }

        return self::$cache[$key] = $columns;
    }

    /**
     * Drop the memoised columns. Primarily for tests; in normal operation
     * the cache is naturally bounded by the number of registered resources.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
