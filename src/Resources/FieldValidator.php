<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Ginkelsoft\Buildora\Fields\Types\ViewField;
use Exception;

/**
 * Class FieldValidator
 *
 * Validates fields in a Buildora resource against the given model.
 */
class FieldValidator
{
    /**
     * Validate all given fields to ensure they are proper Field instances.
     *
     * @param Field[] $fields The array of field instances.
     * @param Model $model The model instance associated with the fields.
     * @return void
     * @throws Exception If a field is not a valid Field instance.
     */
    public static function validate(array $fields, Model $model): void
    {
        foreach ($fields as $field) {
            // Skip validation for view-only fields
            if ($field instanceof ViewField) {
                continue;
            }

            if (! $field instanceof Field) {
                throw new Exception("Invalid field instance for [{$field->name}].");
            }
        }
    }
}
