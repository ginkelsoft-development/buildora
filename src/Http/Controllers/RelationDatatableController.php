<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\Relations\Relation;
use Ginkelsoft\Buildora\Support\ResourceResolver;
use Ginkelsoft\Buildora\Datatable\BuildoraDatatable;
use Ginkelsoft\Buildora\Fields\Types\BelongsToField;

/**
 * Class RelationDatatableController
 *
 * Handles AJAX requests for relation-based datatables inside Buildora resources.
 *
 * When a resource (for example `CouponBuildora`) defines a panel such as:
 *
 *     Panel::relation('orders', OrderBuildora::class)
 *
 * the Buildora frontend will make a request like:
 *
 *     GET /buildora/relation-datatable/coupons/12/orders
 *
 * This controller resolves that request by:
 *  1. Resolving the parent Buildora resource (e.g., CouponBuildora).
 *  2. Retrieving the parent Eloquent model instance (e.g., Coupon::findOrFail(12)).
 *  3. Validating that the requested relation exists on the model.
 *  4. Resolving the related Buildora resource (e.g., OrderBuildora).
 *  5. Building an Eloquent query for the relation (e.g., $coupon->orders()).
 *  6. Applying eager loading for any nested relations defined in the related resource.
 *  7. Returning a JSON response containing the paginated datatable data.
 *
 * This ensures relation panels load efficiently and consistently,
 * without generating redundant or duplicate SQL queries.
 */
class RelationDatatableController extends Controller
{
    /**
     * Handle a relation datatable request for a specific Buildora resource.
     *
     * @param  string     $resource  The base resource slug or class name (for example: 'coupons').
     * @param  int|string $id        The primary key of the parent model instance.
     * @param  string     $relation  The relation method name defined on the model (for example: 'orders').
     * @return JsonResponse          The JSON datatable response for the relation.
     */
    public function __invoke(string $resource, int|string $id, string $relation): JsonResponse
    {
        // Step 1: Resolve the Buildora resource (e.g., CouponBuildora)
        $resource = ResourceResolver::resolve($resource);

        // ✅ PERFORMANCE: Only select 'id' for parent - we just need it to exist
        $model = $resource->getModelInstance()->select('id')->findOrFail($id);

        // Step 3: Ensure the relation exists on the model
        if (!method_exists($model, $relation)) {
            abort(404, "Relation '{$relation}' not found on model " . get_class($model));
        }

        // Step 4: Find the relation configuration (as defined in definePanels)
        $relationConfig = collect($resource->getRelationResources())
            ->first(fn($layout) => $layout->relationName === $relation);

        if (!$relationConfig) {
            abort(404, "Relation '{$relation}' is not defined in resource " . get_class($resource));
        }

        // Step 5: Instantiate the related resource (e.g., OrderBuildora)
        $relatedResourceClass = $relationConfig->resourceClass;
        $relatedResource = app($relatedResourceClass);
        $relatedResource->setParentModel($model);

        // Step 6: Build the Eloquent relation query (e.g., $coupon->orders())
        $relationQuery = $model->{$relation}();

        if (!$relationQuery instanceof Relation) {
            abort(400, "Relation '{$relation}' on " . get_class($model) . " is not a valid Eloquent relation.");
        }

        // ✅ PERFORMANCE: Only eager load BelongsTo relations that are visible in table
        $relatedModel = $relationQuery->getRelated();
        $belongsToRelations = collect($relatedResource->getFields())
            ->filter(function ($field) use ($relatedModel) {
                // Only load if it's a BelongsTo, exists on model, AND is visible in table
                return $field instanceof BelongsToField
                    && method_exists($relatedModel, $field->name)
                    && ($field->visibility['table'] ?? false);
            })
            ->map(fn (BelongsToField $field) => $field->name)
            ->unique()
            ->values()
            ->toArray();

        if (!empty($belongsToRelations)) {
            $relationQuery->with($belongsToRelations);
        }

        // Step 8: Build the datatable using the relation query
        $datatable = new BuildoraDatatable($relatedResource);
        $datatable->fetchDataUsingRelation($relationQuery);

        // Step 9: Return the JSON datatable response
        return response()->json($datatable->getJsonResponse());
    }
}
