<?php

namespace Ginkelsoft\Buildora\Resources;

use BackedEnum;
use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\BelongsToManyField;
use Ginkelsoft\Buildora\Fields\Types\BooleanField;
use Ginkelsoft\Buildora\Fields\Types\DateField;
use Ginkelsoft\Buildora\Fields\Types\DateTimeField;
use Ginkelsoft\Buildora\Fields\Types\EmailField;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\JsonField;
use Ginkelsoft\Buildora\Fields\Types\NumberField;
use Ginkelsoft\Buildora\Fields\Types\PasswordField;
use Ginkelsoft\Buildora\Fields\Types\SelectField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

abstract class ModelResource extends BuildoraResource
{
    /**
     * Fields that should be excluded from the auto-generated form.
     *
     * @var array<int, string>
     */
    protected array $excludeFields = [];

    /**
     * Defines whether BelongsToMany relations should be exposed as fields automatically.
     */
    protected bool $includeRelationFields = true;

    /**
     * Allow subclasses to disable the default row actions.
     */
    protected bool $useDefaultRowActions = true;

    public function defineFields(): array
    {
        $model = $this->getModelInstance();

        $fields = [];

        if ($this->shouldIncludePrimaryKey($model)) {
            $fields[] = IDField::make($model->getKeyName(), Str::upper($model->getKeyName()));
        }

        foreach ($model->getFillable() as $attribute) {
            if (! $this->shouldIncludeField($attribute)) {
                continue;
            }

            $field = $this->makeFieldForAttribute($attribute, $model);

            if ($field) {
                $fields[] = $field;
            }
        }

        if ($this->includeRelationFields) {
            $fields = array_merge($fields, $this->buildRelationFields($model));
        }

        $fields = array_merge($fields, $this->additionalFields($model));

        return $this->finalizeFields($fields, $model);
    }

    public function defineRowActions(): array
    {
        if (! $this->useDefaultRowActions) {
            return $this->additionalRowActions();
        }

        return array_merge($this->defaultRowActions(), $this->additionalRowActions());
    }

    /**
     * Override to inject additional fields.
     *
     * @param Model $model
     * @return array<int, Field>
     */
    protected function additionalFields(Model $model): array
    {
        return [];
    }

    /**
     * Override to tweak the generated field list.
     *
     * @param array<int, Field> $fields
     * @param Model $model
     * @return array<int, Field>
     */
    protected function finalizeFields(array $fields, Model $model): array
    {
        return $fields;
    }

    /**
     * Override to append extra row actions.
     *
     * @return array<int, RowAction>
     */
    protected function additionalRowActions(): array
    {
        return [];
    }

    /**
     * Determine if the model's primary key should be exposed as an ID field.
     */
    protected function shouldIncludePrimaryKey(Model $model): bool
    {
        return $model->getKeyName() === 'id';
    }

    /**
     * Determine if an attribute should be included.
     */
    protected function shouldIncludeField(string $attribute): bool
    {
        return ! in_array($attribute, $this->excludedFields(), true);
    }

    /**
     * Helpers for subclasses to modify excluded fields.
     *
     * @return array<int, string>
     */
    protected function excludedFields(): array
    {
        return $this->excludeFields;
    }

    /**
     * Build default row actions for CRUD operations.
     *
     * @return array<int, RowAction>
     */
    protected function defaultRowActions(): array
    {
        $prefix = $this->permissionPrefix();

        return [
            RowAction::make('View', 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id']),

            RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->permission("{$prefix}.edit")
                ->params(['id' => 'id']),

            RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->permission("{$prefix}.delete")
                ->params(['id' => 'id'])
                ->confirm($this->confirmDeleteMessage()),
        ];
    }

    /**
     * Determine the permission prefix used for default row actions.
     */
    protected function permissionPrefix(): string
    {
        return static::slug();
    }

    /**
     * Confirmation message shown before deleting a record.
     */
    protected function confirmDeleteMessage(): string
    {
        return 'Are you sure you want to delete this item?';
    }

    /**
     * Build a Field instance for the given attribute.
     */
    protected function makeFieldForAttribute(string $attribute, Model $model): ?Field
    {
        $label = Str::headline($attribute);
        $castType = $this->resolveCastType($attribute, $model);

        return match (true) {
            $attribute === 'email' => EmailField::make($attribute, $label),
            $attribute === 'password' => PasswordField::make($attribute, $label)->hideFromTable(),
            $this->isBooleanCast($castType) => BooleanField::make($attribute, $label),
            $this->isDateCast($castType) => DateField::make($attribute, $label),
            $this->isDateTimeCast($castType) => DateTimeField::make($attribute, $label),
            $this->isNumericCast($castType) => NumberField::make($attribute, $label),
            $this->isJsonCast($castType) => JsonField::make($attribute, $label),
            $this->isEnumCast($castType) => SelectField::make($attribute, $label)->options(
                $this->resolveEnumOptions($castType)
            ),
            default => TextField::make($attribute, $label),
        };
    }

    protected function resolveCastType(string $attribute, Model $model): ?string
    {
        if (method_exists($model, 'getAttributeCastType')) {
            try {
                return $model->getAttributeCastType($attribute);
            } catch (Throwable) {
                return $model->getCasts()[$attribute] ?? null;
            }
        }

        return $model->getCasts()[$attribute] ?? null;
    }

    protected function isBooleanCast(?string $cast): bool
    {
        return $cast === 'bool' || $cast === 'boolean';
    }

    protected function isDateCast(?string $cast): bool
    {
        if (! $cast) {
            return false;
        }

        return str_contains($cast, 'date') && ! str_contains($cast, 'time');
    }

    protected function isDateTimeCast(?string $cast): bool
    {
        if (! $cast) {
            return false;
        }

        return str_contains($cast, 'datetime') || str_contains($cast, 'timestamp');
    }

    protected function isNumericCast(?string $cast): bool
    {
        return in_array($cast, ['int', 'integer', 'real', 'float', 'double', 'decimal'], true);
    }

    protected function isJsonCast(?string $cast): bool
    {
        return in_array($cast, ['array', 'json', 'collection', 'object'], true);
    }

    protected function isEnumCast(?string $cast): bool
    {
        return $cast && enum_exists($cast);
    }

    /**
     * @return array<int, string>
     */
    protected function resolveEnumOptions(string $enumClass): array
    {
        return collect($enumClass::cases())
            ->mapWithKeys(function ($case) {
                $key = $case instanceof BackedEnum ? $case->value : $case->name;
                $label = method_exists($case, 'label') ? $case->label() : $case->name;

                return [$key => $label];
            })
            ->toArray();
    }

    /**
     * Automatically expose BelongsToMany relations as selectable fields.
     *
     * @param Model $model
     * @return array<int, Field>
     */
    protected function buildRelationFields(Model $model): array
    {
        $fields = [];
        $reflection = new ReflectionClass($model);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() !== 0 || $method->class !== $reflection->getName()) {
                continue;
            }

            $name = $method->getName();

            if (! $this->shouldIncludeRelation($name)) {
                continue;
            }

            try {
                $relation = $method->invoke($model);
            } catch (Throwable) {
                continue;
            }

            if ($relation instanceof BelongsToMany) {
                $label = Str::headline($name);
                $fields[] = BelongsToManyField::make($name, $label)->hideFromTable();
            }
        }

        return $fields;
    }

    /**
     * Allow subclasses to skip specific relations.
     */
    protected function shouldIncludeRelation(string $relation): bool
    {
        return true;
    }

    public static function modelClass(): string
    {
        if (property_exists(static::class, 'model')) {
            $model = static::$model ?? null;

            if ($model) {
                return $model;
            }
        }

        $namespace = config('buildora.models_namespace', 'App\\Models\\');

        return $namespace . str_replace('Buildora', '', class_basename(static::class));
    }
}
