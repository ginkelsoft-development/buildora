<?php

namespace Ginkelsoft\Buildora\Support;

use Illuminate\Database\Eloquent\Model;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Ginkelsoft\Buildora\Exceptions\BuildoraException;

/**
 * Class BuildoraValidator
 *
 * Validates that a model is compatible with Buildora.
 */
class BuildoraValidator
{
    /**
     * Ensure the given model class is valid and Buildora-compatible.
     *
     * @param string $modelClass The fully qualified model class name.
     * @return void
     *
     * @throws BuildoraException If the model is invalid or incompatible.
     */
    public static function assertValidModel(string $modelClass): void
    {
        if (!class_exists($modelClass)) {
            throw new BuildoraException("Model [$modelClass] does not exist.");
        }

        if (!is_subclass_of($modelClass, Model::class)) {
            throw new BuildoraException("Class [$modelClass] is not a valid Eloquent model.");
        }

        if (!in_array(HasBuildora::class, class_uses_recursive($modelClass))) {
            throw new BuildoraException("Model [$modelClass] must use the HasBuildora trait.");
        }
    }
}
