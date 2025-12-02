<?php

namespace Ginkelsoft\Buildora\Support;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Traits\HasBuildora;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ResourceScanner
{
    /**
     * Retrieve all available BuildoraResource classes and generate a menu.
     *
     * @return array<int, array{
     *     name: string,
     *     resource: string,
     *     route: string,
     *     label: string
     * }>
     */
    public static function getResources(): array
    {
        $resourcePath = app_path('Buildora/Resources');
        $resources = [];
        $registered = [];

        $registerResource = function (string $resourceClass) use (&$resources, &$registered): void {
            if (! class_exists($resourceClass) || ! is_subclass_of($resourceClass, BuildoraResource::class)) {
                return;
            }

            try {
                /** @var BuildoraResource $instance */
                $instance = app($resourceClass);
                $modelInstance = $instance->getModelInstance();
                $modelClass = get_class($modelInstance);
            } catch (\Throwable) {
                return;
            }

            if (! in_array(HasBuildora::class, class_uses_recursive($modelClass))) {
                return;
            }

            $slug = $resourceClass::slug();

            if (isset($registered[$slug])) {
                return;
            }

            $label = method_exists($instance, 'label')
                ? $instance->label()
                : Str::plural(Str::headline(class_basename($modelClass)));

            $resources[] = [
                'name' => $slug,
                'resource' => class_basename($resourceClass),
                'route' => route('buildora.index', ['resource' => $slug]),
                'label' => $label,
            ];

            $registered[$slug] = true;
        };

        if (File::exists($resourcePath)) {
            foreach (File::files($resourcePath) as $file) {
                $resourceName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $resourceClass = "App\\Buildora\\Resources\\{$resourceName}";

                $registerResource($resourceClass);
            }
        }

        $defaultResources = config('buildora.resources.defaults', []);

        foreach ($defaultResources as $slug => $config) {
            if (($config['enabled'] ?? false) !== true) {
                continue;
            }

            $class = $config['class'] ?? null;

            if (! $class) {
                continue;
            }

            $registerResource($class);
        }

        return $resources;
    }
}
