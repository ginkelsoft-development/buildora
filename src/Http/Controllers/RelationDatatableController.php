<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\Relations\Relation;
use Ginkelsoft\Buildora\Support\ResourceResolver;
use Ginkelsoft\Buildora\Datatable\BuildoraDatatable;

class RelationDatatableController extends Controller
{
    public function __invoke(string $resource, int|string $id, string $relation): JsonResponse
    {
        // 🔎 Resolve hoofdresource en model
        $resource = ResourceResolver::resolve($resource);
        $model = $resource->getModelInstance()->findOrFail($id);

        // ❌ Controleer of de relatie bestaat op het model
        if (!method_exists($model, $relation)) {
            abort(404, "Relation '{$relation}' not found on model " . get_class($model));
        }

        // 🔗 Vind relationele layoutconfig (zoals Panel::relation)
        $relationConfig = collect($resource->getRelationResources())
            ->first(fn ($layout) => $layout->relationName === $relation);

        if (!$relationConfig) {
            abort(404, "Relation '{$relation}' is not defined in the resource " . get_class($resource));
        }

        // 📦 Instantieer de relationele resource
        $relatedResourceClass = $relationConfig->resourceClass;
        $relatedResource = app($relatedResourceClass);

        // 🔗 Stel het parentmodel in zodat relatie weet voor wie
        $relatedResource->setParentModel($model);

        // 🧠 Haal relationele query op (bv. $model->orders())
        $relationQuery = $model->{$relation}();

        if (!$relationQuery instanceof Relation) {
            abort(400, "Relation '{$relation}' on model " . get_class($model) . " is not a valid Eloquent relation.");
        }

        // ✨ Vul de resource met het eerste record (optioneel, voor weergave)
        if ($first = $relationQuery->first()) {
            $relatedResource->fill($first);
        }

        // 📊 Bouw de datatable
        $datatable = new BuildoraDatatable($relatedResource);
        $datatable->fetchDataUsingRelation($relationQuery);

        return response()->json($datatable->getJsonResponse());
    }
}
