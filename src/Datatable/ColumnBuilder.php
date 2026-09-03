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
     * In-memory cache van reeds opgebouwde kolommen, per resource-class.
     *
     * De structuur van de kolommen (naam, sortable, label, zichtbaarheid) hangt
     * alleen af van de fieldsdefinitie van de resource-class en niet van de
     * specifieke instantie of het model-record. Daarom mag dit resultaat
     * process-breed hergebruikt worden en hoeft build() niet bij elke
     * datatable-request opnieuw de fields van de resource te doorlopen.
     *
     * @var array<class-string, array<int, array{name: string, sortable: bool, label: string}>>
     */
    private static array $columnCache = [];

    /**
     * Builds an array of visible and optionally sortable column definitions.
     *
     * @param object $resource The resource instance (must implement getFields()).
     * @return array<int, array{name: string, sortable: bool, label: string}>
     */
    public static function build(object $resource): array
    {
        $cacheKey = get_class($resource);

        if (array_key_exists($cacheKey, self::$columnCache)) {
            return self::$columnCache[$cacheKey];
        }

        $columns = array_values(array_filter(
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

        return self::$columnCache[$cacheKey] = $columns;
    }

    /**
     * Leegt de kolommencache. Vooral bedoeld voor gebruik in tests.
     */
    public static function clearCache(): void
    {
        self::$columnCache = [];
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
