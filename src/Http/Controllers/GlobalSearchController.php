<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Ginkelsoft\Buildora\Support\ResourceScanner;

class GlobalSearchController extends Controller
{
    /**
     * Handle the global search query and return matching results across all Buildora resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $term = $request->get('q');
        $results = [];

        foreach (ResourceScanner::getResources() as $resourceMeta) {
            if (!isset($resourceMeta['name'])) {
                continue;
            }

            $resourceClass = 'App\\Buildora\\Resources\\' . ucfirst($resourceMeta['name']) . 'Buildora';

            if (!class_exists($resourceClass)) {
                continue;
            }

            /** @var \Ginkelsoft\Buildora\Resources\BuildoraResource $resource */
            $resource = new $resourceClass();
            $model = $resource->getModelInstance();

            // Gebruik gedefinieerde zoekconfiguratie
            $config = method_exists($resource, 'searchResultConfig')
                ? $resource->searchResultConfig()
                : ['label' => 'id', 'columns' => $model->getFillable()];

            $columns = $config['columns'] ?? [];
            $labelConfig = $config['label'] ?? 'id';

            if (empty($columns)) {
                continue;
            }

            // Zoekquery op de opgegeven kolommen
            $query = $model::query();
            $query->where(function ($q) use ($columns, $term) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', '%' . $term . '%');
                }
            });

            $query->limit(5)->get()->each(function ($item) use (&$results, $resourceMeta, $resource, $labelConfig) {
                // Genereer label
                if (is_callable($labelConfig)) {
                    $label = $labelConfig($item);
                } elseif (is_array($labelConfig)) {
                    $label = collect($labelConfig)
                        ->map(fn($col) => $item->{$col} ?? '')
                        ->filter()
                        ->implode(' ');
                } else {
                    $label = $item->{$labelConfig} ?? 'ID ' . $item->id;
                }

                $results[] = [
                    'label' => $label . ' (' . $resource->title() . ')',
                    'url' => route('buildora.edit', [
                        'resource' => $resourceMeta['name'],
                        'id' => $item->id,
                    ]),
                ];
            });
        }

        return response()->json(['results' => $results]);
    }
}
