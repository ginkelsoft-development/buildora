<?php

namespace Ginkelsoft\Buildora\Datatable;

/**
 * Class ColumnBuilder
 *
 * Responsible for building column definitions for a Buildora datatable.
 */
class ColumnBuilder
{
    /**
     * Builds an array of visible and optionally sortable column definitions.
     *
     * @param object $resource The resource instance (must implement getFields()).
     * @return array<int, array{name: string, sortable: bool, label: string}>
     */
    public static function build(object $resource): array
    {
        return array_values(array_filter(
            array_map(
                fn($field): array => [
                    'name' => $field->name,
                    'sortable' => $field->sortable ?? false,
                    'label' => $field->label,
                ],
                $resource->getFields()
            ),
            fn(array $field): bool => self::isVisibleInTable($resource, $field['name'])
        ));
    }

    /**
     * Checks if a field is marked as visible in the datatable.
     *
     * @param object $resource The resource instance.
     * @param string $fieldName The name of the field to check.
     * @return bool True if the field should be shown in the table, false otherwise.
     */
    protected static function isVisibleInTable(object $resource, string $fieldName): bool
    {
        foreach ($resource->getFields() as $field) {
            if ($field->name === $fieldName && ($field->visibility['table'] ?? false)) {
                return true;
            }
        }

        return false;
    }
}
