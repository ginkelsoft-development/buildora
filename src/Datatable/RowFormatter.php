<?php

namespace Ginkelsoft\Buildora\Datatable;

use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\ViewField;

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
            if (! $field instanceof \Ginkelsoft\Buildora\Fields\Field) {
                throw new \Ginkelsoft\Buildora\Exceptions\BuildoraException(
                    "Ongeldig veld in " . get_class($resource) . ": verwacht Field, kreeg " . (is_object($field) ? get_class($field) : gettype($field))
                );
            }

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
