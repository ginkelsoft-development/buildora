<?php

namespace Ginkelsoft\Buildora\Datatable;

use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\ViewField;
use Ginkelsoft\Buildora\Resources\ActionManager;

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
    public static function format(object $resource, object $resourceInstance, ?array $rowActionDefinitions = null): array
    {
        $row = [];
        $fields = $resource->getFields();

        // ✅ PERFORMANCE: Process only visible table fields
        foreach ($fields as $field) {
            if (! $field instanceof Field) {
                throw new BuildoraException(
                    "Ongeldig veld in " . get_class($resource) . ": verwacht Field, kreeg " .
                    (is_object($field) ? get_class($field) : gettype($field))
                );
            }

            // Skip fields not visible in table
            if (!($field->visibility['table'] ?? false)) {
                continue;
            }

            // ✅ PERFORMANCE: Defer view rendering - return raw value for now
            // ViewFields will be rendered on-demand by frontend if needed
            if ($field instanceof ViewField) {
                // Store view path and value for lazy rendering
                $row[$field->name] = $field->displayValue ?? $field->value;
            } else {
                $rawValue = $field->displayValue ?? $field->value;

                $row[$field->name] = is_array($rawValue)
                    ? implode(', ', $rawValue)
                    : $rawValue;
            }
        }

        // Voeg acties toe
        if ($rowActionDefinitions !== null) {
            $row['actions'] = ActionManager::resolveRowActions($rowActionDefinitions, $resource);
            return $row;
        }

        $row['actions'] = array_map(
            fn($action) => $action instanceof RowAction ? $action->toArray($resource) : $action,
            $resourceInstance->getRowActions($resource)
        );

        return $row;
    }
}
