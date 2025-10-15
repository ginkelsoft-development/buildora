<?php

namespace Ginkelsoft\Buildora\Resources;

use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FieldManager
 *
 * Responsible for preparing fields with their values and model relationships.
 */
class FieldManager
{
    /**
     * Prepare fields by setting their parent model and value.
     *
     * @param Field[] $fields The array of field instances.
     * @param Model $model The Eloquent model to extract values from.
     * @return Field[] The array of prepared fields.
     */
    public static function prepare(array $fields, Model $model): array
    {
        return collect($fields)->map(function (Field $field) use ($model) {
            if (method_exists($field, 'setParentModel')) {
                $field->setParentModel($model);
            }

            $shouldHydrateValue = !($model instanceof Model) || $model->exists;

            if ($shouldHydrateValue && method_exists($field, 'setValue')) {
                $field->setValue($model);
            } else {
                $field->value = $model instanceof Model ? ($model->{$field->name} ?? null) : null;
            }

            return $field;
        })->toArray();
    }
}
