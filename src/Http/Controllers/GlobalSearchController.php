<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ginkelsoft\Buildora\Support\ResourceScanner;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;

class GlobalSearchController extends Controller
{
    /**
     * Handle the global search query and return matching results across all Buildora resources.
     *
     * @param Request $request The incoming HTTP request containing the search term (`q`).
     * @return JsonResponse A JSON response containing the matched results.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $term = $request->get('q');
        $results = [];

        foreach (ResourceScanner::getResources() as $resourceMeta) {
            if (!isset($resourceMeta['name'])) {
                continue; // ⛔️ Skip if no name is defined
            }

            $resourceClass = 'App\\Buildora\\Resources\\' . ucfirst($resourceMeta['name']) . 'Buildora';

            if (!class_exists($resourceClass)) {
                continue;
            }

            $resource = new $resourceClass();
            $model = $resource->getModelInstance();
            $columns = $model->getFillable();

            if (empty($columns)) {
                continue;
            }

            // Optional: filter only searchable text-based columns
            $textColumns = array_filter($columns, function ($column) use ($model) {
                $cast = $model->getCasts()[$column] ?? 'string';
                return in_array($cast, ['string', 'text']);
            });

            if (empty($textColumns)) {
                continue;
            }

            $query = $model::query();
            $query->where(function ($q) use ($textColumns, $term) {
                foreach ($textColumns as $col) {
                    $q->orWhere($col, 'like', "%{$term}%");
                }
            });

            $query->limit(5)->get()->each(function ($item) use (&$results, $resourceMeta, $textColumns) {
                $firstLabelColumn = $textColumns[0] ?? 'id';
                $label = $item->{$firstLabelColumn} ?? $item->id;

                $results[] = [
                    'label' => $label . ' (' . $resourceMeta['name'] . ')',
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
