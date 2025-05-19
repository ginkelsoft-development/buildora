<?php

namespace Ginkelsoft\Buildora\Support;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
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

        if (!File::exists($resourcePath)) {
            return [];
        }

        foreach (File::files($resourcePath) as $file) {
            $resourceName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $resourceClass = "App\\Buildora\\Resources\\{$resourceName}";

            if (!class_exists($resourceClass) || !is_subclass_of($resourceClass, BuildoraResource::class)) {
                continue;
            }

            try {
                /** @var BuildoraResource $instance */
                $instance = new $resourceClass();
                $modelInstance = $instance->getModelInstance();
                $modelClass = get_class($modelInstance);
            } catch (BuildoraException $e) {
                continue;
            }

            if (!in_array(HasBuildora::class, class_uses_recursive($modelClass))) {
                continue;
            }

            $label = method_exists($instance, 'label')
                ? $instance->label()
                : Str::plural(ucfirst(Str::snake(class_basename($modelClass), ' ')));

            $resources[] = [
                'name' => strtolower(str_replace('Buildora', '', $resourceName)),
                'resource' => class_basename($resourceClass),
                'route' => route('buildora.index', ['resource' => strtolower(class_basename($modelClass))]),
                'label' => $label,
            ];
        }

        return $resources;
    }
}
