<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Ginkelsoft\Buildora\Support\ResourceScanner;

class GlobalSearchController extends Controller
{
    /**
     * Maximaal aantal resultaten dat per resource wordt opgehaald.
     */
    private const RESULT_LIMIT_PER_RESOURCE = 10;

    /**
     * Handle the global search query and return matching results across all Buildora resources.
     *
     * Elke resource-query is begrensd tot maximaal RESULT_LIMIT_PER_RESOURCE records via
     * ->limit(), zodat een breed zoekterm op een grote tabel geen onbegrensde LIKE-scan
     * en resultaatlijst oplevert.
     *
     * Let op: dit is een LIKE '%term%' query zonder index-ondersteuning. Voor grote tabellen
     * (honderdduizenden+ rijen) wordt aanbevolen om een FULLTEXT-index op de doorzoekbare
     * kolommen te overwegen (of een dedicated zoekoplossing zoals Laravel Scout), omdat een
     * LIKE-query met leidend wildcard geen gebruik kan maken van een standaard B-tree index.
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

            $items = $query->limit(self::RESULT_LIMIT_PER_RESOURCE)->get();

            $items->each(function ($item) use (&$results, $resourceMeta, $resource, $labelConfig) {
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
