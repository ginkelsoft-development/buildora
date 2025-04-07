<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class MakeBuildoraResource
 *
 * This command automatically generates a BuildoraResource class
 * based on the specified model's fillable attributes and relationships.
 */
class MakeBuildoraResource extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'buildora:resource {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Buildora Resource for a model';

    /**
     * Handle the execution of the command.
     *
     * @return void
     */
    public function handle(): void
    {
        $name = $this->argument('name') ?? $this->ask('Welke modelnaam wil je gebruiken?');
        $modelName = ucfirst(Str::studly($name));
        $resourceName = "{$modelName}Buildora";
        $modelClass = "App\\Models\\{$modelName}";
        $resourceClass = "App\\Buildora\\Resources\\{$resourceName}";

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist. Please create it first.");
            return;
        }

        if (class_exists($resourceClass)) {
            $this->error("Resource {$resourceClass} already exists!");
            return;
        }

        $resourceDirectory = app_path("Buildora/Resources");
        $resourcePath = "{$resourceDirectory}/{$resourceName}.php";

        if (!File::exists($resourceDirectory)) {
            File::makeDirectory($resourceDirectory, 0755, true);
        }

        $stub = <<<PHP
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\BelongsToField;
use Ginkelsoft\Buildora\Fields\Types\HasManyField;
use Ginkelsoft\Buildora\Fields\Types\BelongsToManyField;
use Ginkelsoft\Buildora\Fields\Types\HasOneField;
use Ginkelsoft\\Buildora\\Actions\\RowAction;

class {$resourceName} extends BuildoraResource
{
    protected static string \$model = $modelClass::class;

    public function defineFields(): array
    {
        return {$this->generateFieldsArray($modelClass)};
    }

    public function validationRules(): array
    {
        return [];
    }

    public function defineFilters(): array
    {
        return [];
    }

    public function defineRowActions(): array
    {
        return [
            RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->params(['id' => 'id']),

            RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['id' => 'id'])
                ->confirm('Are you sure you want to delete this item?'),
        ];
    }

    public function defineBulkActions(): array
    {
        return [];
    }

    public function defineWidgets(): array
    {
        return [];
    }
}
PHP;

        File::put($resourcePath, trim($stub));
        exec('composer dump-autoload');

        $this->info("Buildora Resource {$resourceName} created successfully.");
    }

    /**
     * Generate field declarations based on fillable and relationships.
     *
     * @param string \$modelClass
     * @return string
     */
    private function generateFieldsArray(string $modelClass): string
    {
        $modelInstance = new $modelClass;
        $fillable = $modelInstance->getFillable();
        $primaryKey = $modelInstance->getKeyName();
        $fields = [];

        if ($primaryKey === 'id') {
            $fields[] = "Field::make('id', 'ID', 'number')->readonly()->hideFromTable()->hideFromExport()";
        }

        foreach ($fillable as $field) {
            $type = $this->resolveFieldType($field, $modelInstance);
            $fields[] = "Field::make('{$field}', '" . ucfirst(str_replace('_', ' ', $field)) . "', '{$type}')";
        }

        foreach ($this->detectModelRelations($modelInstance) as $relationField) {
            $fields[] = $relationField;
        }

        return '[' . PHP_EOL . '            ' . implode(',' . PHP_EOL . '            ', $fields) . PHP_EOL . '        ]';
    }

    /**
     * Determine field type based on model cast type.
     *
     * @param string $field
     * @param object $model
     * @return string
     */
    private function resolveFieldType(string $field, object $model): string
    {
        $castType = method_exists($model, 'getAttributeCastType')
            ? $model->getAttributeCastType($field)
            : ($model->getCasts()[$field] ?? null);

        return match (true) {
            str_contains($castType, 'date'), str_contains($castType, 'timestamp') => 'date',
            $castType === 'boolean' => 'boolean',
            in_array($castType, ['int', 'integer', 'float', 'decimal', 'double'], true) => 'number',
            in_array($castType, ['array', 'json', 'collection', 'object'], true) => 'json',
            str_starts_with($castType, 'encrypted') => 'text',
            enum_exists($castType) => 'select',
            class_exists($castType) => 'custom',
            default => 'text',
        };
    }

    /**
     * Detect Eloquent relationships and map them to Buildora Fields.
     *
     * @param object $model
     * @return array<int, string>
     */
    private function detectModelRelations(object $model): array
    {
        $fields = [];
        $reflection = new ReflectionClass($model);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (
                $method->getNumberOfParameters() === 0 &&
                $method->class === get_class($model)
            ) {
                try {
                    $relation = $method->invoke($model);
                    $relationName = $method->getName();
                    $label = ucfirst(str_replace('_', ' ', $relationName));

                    if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        $fields[] = "BelongsToField::make('{$relationName}', '{$label}')";
                    } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasOne) {
                        $fields[] = "HasOneField::make('{$relationName}', '{$label}')->hideFromTable()";
                    } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                        $fields[] = "HasManyField::make('{$relationName}', '{$label}')->hideFromTable()";
                    } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
                        $fields[] = "BelongsToManyField::make('{$relationName}', '{$label}')->hideFromTable()";
                    }
                } catch (\Throwable) {
                    // Not a valid relationship method
                }
            }
        }

        return $fields;
    }
}
