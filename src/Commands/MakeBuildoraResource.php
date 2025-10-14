<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class MakeBuildoraResource extends Command
{
    protected $signature = 'buildora:resource {name?}';
    protected $description = 'Generate a new Buildora Resource for a model';

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

        $fields = $this->generateFieldsArray($modelClass);
        $relations = $this->detectModelRelations(new $modelClass());

        $stub = <<<PHP
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Fields\Types\BelongsToManyField;
use Ginkelsoft\Buildora\Fields\Types\EmailField;
use Ginkelsoft\Buildora\Fields\Types\IDField;
use Ginkelsoft\Buildora\Fields\Types\PasswordField;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Actions\RowAction;
use Spatie\Permission\Models\Permission;

class {$resourceName} extends BuildoraResource
{
    protected static string \$model = {$modelClass}::class;

    public function title(): string
    {
        return '{$modelName}';
    }

    public function searchResultConfig(): array
    {
        return [
            'label' => '{$modelName}',
            'columns' => [],
        ];
    }

    public function defineFields(): array
    {
        return {$fields};
    }

    public function defineRowActions(): array
    {
        return [
            RowAction::make('View', 'fas fa-eye', 'route', 'buildora.show')
                ->method('GET')
                ->params(['id' => 'id']),

            RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
                ->method('GET')
                ->permission('{$name}.edit')
                ->params(['id' => 'id']),

            RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['id' => 'id'])
                ->permission('{$name}.delete')
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

    public function definePanels(): array
    {
        return [];
    }
}
PHP;

        File::put($resourcePath, trim($stub));
        exec('composer dump-autoload');

        $this->info("Buildora Resource {$resourceName} created successfully.");
    }

    private function generateFieldsArray(string $modelClass): string
    {
        $modelInstance = new $modelClass();
        $fillable = $modelInstance->getFillable();
        $primaryKey = $modelInstance->getKeyName();
        $fields = [];

        if ($primaryKey === 'id') {
            $fields[] = "IDField::make('id', 'ID')->readonly()->hideFromTable()->hideFromExport()";
        }

        foreach ($fillable as $field) {
            $type = $this->resolveFieldType($field, $modelInstance);
            $fieldLabel = ucfirst(str_replace('_', ' ', $field));

            if ($field === 'email') {
                $fields[] = "EmailField::make('{$field}', '{$fieldLabel}')";
            } elseif ($field === 'password') {
                $fields[] = "PasswordField::make('{$field}', '{$fieldLabel}')->hideFromTable()";
            } else {
                $fields[] = "TextField::make('{$field}', '{$fieldLabel}')";
            }
        }

        return '[' . PHP_EOL . '            ' . implode(',' . PHP_EOL . '            ', $fields) . PHP_EOL . '        ]';
    }

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

                    if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
                        $fields[] = "BelongsToManyField::make('{$relationName}', '{$label}')->hideFromTable()";
                    }
                } catch (\Throwable) {
                    // Skip non-relations
                }
            }
        }

        return $fields;
    }
}
