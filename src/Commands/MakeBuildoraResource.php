<?php

namespace Ginkelsoft\Buildora\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeBuildoraResource extends Command
{
    protected $signature = 'buildora:resource {name?}';
    protected $description = 'Generate a new Buildora Resource for a model';

    public function handle(): void
    {
        $name = $this->argument('name') ?? $this->ask('Welke modelnaam wil je gebruiken?');
        $modelName = ucfirst(Str::studly($name));
        $resourceName = "{$modelName}Buildora";
        $modelsNamespace = rtrim(config('buildora.models_namespace', 'App\\Models\\'), '\\') . '\\';
        $modelClass = "{$modelsNamespace}{$modelName}";
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

use Ginkelsoft\Buildora\Resources\ModelResource;

class {$resourceName} extends ModelResource
{
    protected static string \$model = {$modelClass}::class;
}
PHP;

        File::put($resourcePath, trim($stub));
        exec('composer dump-autoload');

        $this->info("Buildora Resource {$resourceName} created successfully.");
    }
}
