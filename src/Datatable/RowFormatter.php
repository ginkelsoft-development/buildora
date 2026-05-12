<?php

namespace Ginkelsoft\Buildora\Datatable;

use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;

class RowFormatter
{
    /**
     * Format a resource into a datatable row array.
     *
     * @param object $resource The hydrated resource instance (with resolved field values).
     * @param object $resourceInstance The plain resource instance used for accessing row actions.
     * @return array<string, mixed>
     *
     * @throws BuildoraException
     */
    public static function format(object $resource, object $resourceInstance): array
    {
        $row = [];

        foreach ($resource->getFields() as $field) {
            if (! $field instanceof Field) {
                $type = is_object($field) ? get_class($field) : gettype($field);
                throw new BuildoraException(
                    "Ongeldig veld in " . get_class($resource) . ": verwacht Field, kreeg " . $type
                );
            }

            // Field-type-specific format-time work goes here. The default
            // implementation on Field is a no-op; only ViewField actually
            // overrides this (to materialise its Blade partial). The
            // formatter doesn't need to know which is which.
            $field->renderForDisplay();

            $rawValue = $field->displayValue ?? $field->value;

            $row[$field->name] = is_array($rawValue)
                ? implode(', ', $rawValue)
                : $rawValue;
        }

        // Voeg acties toe
        $row['actions'] = array_map(
            fn($action) => $action instanceof RowAction
                ? $action->toArray($resource)
                : $action,
            $resourceInstance->getRowActions($resource)
        );

        return $row;
    }
}
