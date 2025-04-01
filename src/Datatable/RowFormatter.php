<?php

namespace Ginkelsoft\Buildora\Datatable;

use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Fields\Types\ViewField;

/**
 * Class RowFormatter
 *
 * Responsible for formatting a resource row for datatable rendering.
 */
class RowFormatter
{
    /**
     * Format a resource into a datatable row array.
     *
     * This method loops through all the fields of a resource and prepares them for JSON output,
     * including rendering Blade views for ViewField instances and appending row actions.
     *
     * @param object $resource The hydrated resource instance (with resolved field values).
     * @param object $resourceInstance The plain resource instance used for accessing row actions.
     * @return array<string, mixed> The formatted row with values and available actions.
     */
    public static function format(object $resource, object $resourceInstance): array
    {
        $row = [];

        foreach ($resource->getFields() as $field) {
            if ($field instanceof ViewField) {
                $view = view($field->getView(), [
                    $field->getVarKey() => $field->value,
                ])->render();

                $field->value = $view;
            }

            $row[$field->name] = is_array($field->value)
                ? implode(', ', $field->value)
                : $field->value;
        }

        $row['actions'] = array_map(
            fn($action) => $action instanceof RowAction
                ? $action->toArray($resource)
                : $action,
            $resourceInstance->getRowActions($resource)
        );

        return $row;
    }
}
