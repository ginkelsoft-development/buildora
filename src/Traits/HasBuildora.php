<?php

namespace Ginkelsoft\Buildora\Traits;

use ReflectionMethod;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Trait HasBuildora
 *
 * Provides automatic Buildora functionality for models.
 */
trait HasBuildora
{
    /**
     * Get the fields required for Buildora operations.
     *
     * @return array<string, mixed>
     */
    public static function getFields(): array
    {
        $instance = new static();

        return [
            'fillable' => $instance->getFillable(),
            'casts' => $instance->getCasts(),
            'relations' => self::getBuildoraRelations(),
        ];
    }

    /**
     * Get all relations defined in the model.
     *
     * @return array<string, string>
     */
    private static function getBuildoraRelations(): array
    {
        $instance = new static();
        $relations = [];

        foreach (get_class_methods($instance) as $method) {
            $reflection = new ReflectionMethod($instance, $method);

            if ($reflection->getNumberOfParameters() === 0) {
                $returnType = $reflection->getReturnType();
                if ($returnType && is_subclass_of($returnType->getName(), Relation::class)) {
                    $relations[$method] = $returnType->getName();
                }
            }
        }

        return $relations;
    }
}
