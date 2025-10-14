<?php

namespace Ginkelsoft\Buildora\Fields\Traits;

use Closure;

/**
 * Trait HasValidation
 *
 * Provides validation capabilities for form fields.
 */
trait HasValidation
{
    /**
     * Validation rules for the field.
     *
     * @var array|Closure|null
     */
    protected array|Closure|null $validationRules = null;

    /**
     * Define the validation rules for this field.
     * Can be a static array or a closure for dynamic rules.
     *
     * @param array|Closure $rules
     * @return self
     */
    public function validation(array|Closure $rules): self
    {
        $this->validationRules = $rules;
        return $this;
    }

    /**
     * Retrieve the validation rules.
     * If a closure is defined, it will be executed with the given model.
     *
     * @param mixed $model Optional model to use in dynamic validation rules.
     * @return array
     */
    public function getValidationRules(mixed $model = null): array
    {
        if (is_callable($this->validationRules)) {
            return call_user_func($this->validationRules, $model);
        }

        return $this->validationRules ?? [];
    }
}
