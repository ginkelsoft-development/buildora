<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ginkelsoft\Buildora\Support\ResourceResolver;

/**
 * Controller for inline editing of HasMany and BelongsToMany relations.
 * Handles CRUD operations via AJAX for relation panels with inlineEditing enabled.
 */
class InlineRelationController extends Controller
{
    /**
     * Get the form fields for creating/editing a relation item.
     */
    public function fields(string $resource, int|string $id, string $relation, ?int $itemId = null): JsonResponse
    {
        try {
            $parentResource = ResourceResolver::resolve($resource);
            $parentModel = $parentResource->getModelInstance()->findOrFail($id);

            $relationConfig = $this->getRelationConfig($parentResource, $relation);
            $relatedResource = app($relationConfig->resourceClass);

            // Get the related model instance (for edit) or a new instance (for create)
            $relationQuery = $parentModel->{$relation}();
            $relatedModel = $itemId
                ? $relationQuery->findOrFail($itemId)
                : $relationQuery->getRelated()->newInstance();

            // Fill the resource with the model
            $relatedResource->fill($relatedModel);

            // Get fields for the form
            $fields = $relatedResource->resolveFields($relatedModel);
            $visibility = $itemId ? 'edit' : 'create';

            $formFields = collect($fields)
                ->filter(fn($field) => $field->isVisible($visibility))
                ->map(function($field) use ($relatedModel) {
                    $data = [
                        'name' => $field->name,
                        'label' => $field->label,
                        'type' => $field->type,
                        'value' => $relatedModel->{$field->name} ?? '',
                        'placeholder' => $field->placeholder ?? '',
                        'help' => $field->help ?? null,
                        'readonly' => $field->readonly ?? false,
                        'columnSpan' => $field->getColumnSpan(),
                    ];

                    // Safely get options
                    if (method_exists($field, 'getOptions')) {
                        try {
                            $data['options'] = $field->getOptions();
                        } catch (\Exception $e) {
                            $data['options'] = [];
                        }
                    } else {
                        $data['options'] = [];
                    }

                    // Safely get validation rules
                    try {
                        $data['rules'] = $field->getValidationRules() ?? [];
                    } catch (\Exception $e) {
                        $data['rules'] = [];
                    }

                    return $data;
                })
                ->values()
                ->toArray();

            return response()->json([
                'fields' => $formFields,
                'item' => $itemId ? $relatedModel->toArray() : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Store a new relation item.
     */
    public function store(Request $request, string $resource, int|string $id, string $relation): JsonResponse
    {
        $parentResource = ResourceResolver::resolve($resource);
        $parentModel = $parentResource->getModelInstance()->findOrFail($id);

        $relationConfig = $this->getRelationConfig($parentResource, $relation);
        $relatedResource = app($relationConfig->resourceClass);

        $relationQuery = $parentModel->{$relation}();

        // Get validation rules from the resource fields
        $fields = $relatedResource->resolveFields($relatedResource->getModelInstance());
        $rules = $this->getValidationRules($fields, 'create');

        $validated = $request->validate($rules);

        // Filter out empty password fields
        $data = collect($validated)->filter(function ($value, $key) use ($fields) {
            $field = collect($fields)->firstWhere('name', $key);
            if ($field && $field->type === 'password' && empty($value)) {
                return false;
            }
            return true;
        })->toArray();

        // Handle password hashing
        foreach ($fields as $field) {
            if ($field->type === 'password' && !empty($data[$field->name])) {
                $data[$field->name] = bcrypt($data[$field->name]);
            }
        }

        // Create the related model
        if ($relationQuery instanceof HasMany) {
            $item = $relationQuery->create($data);
        } elseif ($relationQuery instanceof BelongsToMany) {
            $relatedModel = $relationQuery->getRelated();
            $item = $relatedModel->create($data);
            $relationQuery->attach($item->id);
        } else {
            return response()->json(['error' => 'Unsupported relation type'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => __buildora('created successfully.'),
            'item' => $item->toArray(),
        ]);
    }

    /**
     * Update an existing relation item.
     */
    public function update(Request $request, string $resource, int|string $id, string $relation, int $itemId): JsonResponse
    {
        $parentResource = ResourceResolver::resolve($resource);
        $parentModel = $parentResource->getModelInstance()->findOrFail($id);

        $relationConfig = $this->getRelationConfig($parentResource, $relation);
        $relatedResource = app($relationConfig->resourceClass);

        $relationQuery = $parentModel->{$relation}();
        $item = $relationQuery->findOrFail($itemId);

        // Get validation rules from the resource fields
        $fields = $relatedResource->resolveFields($item);
        $rules = $this->getValidationRules($fields, 'edit');

        $validated = $request->validate($rules);

        // Filter out empty password fields
        $data = collect($validated)->filter(function ($value, $key) use ($fields) {
            $field = collect($fields)->firstWhere('name', $key);
            if ($field && $field->type === 'password' && empty($value)) {
                return false;
            }
            return true;
        })->toArray();

        // Handle password hashing
        foreach ($fields as $field) {
            if ($field->type === 'password' && !empty($data[$field->name])) {
                $data[$field->name] = bcrypt($data[$field->name]);
            }
        }

        $item->update($data);

        return response()->json([
            'success' => true,
            'message' => __buildora('updated successfully.'),
            'item' => $item->fresh()->toArray(),
        ]);
    }

    /**
     * Delete a relation item.
     */
    public function destroy(string $resource, int|string $id, string $relation, int $itemId): JsonResponse
    {
        $parentResource = ResourceResolver::resolve($resource);
        $parentModel = $parentResource->getModelInstance()->findOrFail($id);

        $relationConfig = $this->getRelationConfig($parentResource, $relation);

        $relationQuery = $parentModel->{$relation}();

        if ($relationQuery instanceof BelongsToMany) {
            // For BelongsToMany, detach instead of delete
            $relationQuery->detach($itemId);
        } else {
            // For HasMany, delete the record
            $item = $relationQuery->findOrFail($itemId);
            $item->delete();
        }

        return response()->json([
            'success' => true,
            'message' => __buildora('deleted successfully.'),
        ]);
    }

    /**
     * Get the relation configuration from the parent resource.
     */
    protected function getRelationConfig($parentResource, string $relation)
    {
        $relationConfig = collect($parentResource->getRelationResources())
            ->first(fn($layout) => $layout->relationName === $relation);

        if (!$relationConfig) {
            abort(404, "Relation '{$relation}' not found on resource");
        }

        if (!$relationConfig->hasInlineEditing()) {
            abort(403, "Inline editing is not enabled for relation '{$relation}'");
        }

        return $relationConfig;
    }

    /**
     * Get validation rules from fields.
     */
    protected function getValidationRules(array $fields, string $visibility): array
    {
        $rules = [];

        foreach ($fields as $field) {
            if (!$field->isVisible($visibility)) {
                continue;
            }

            $fieldRules = $field->getValidationRules();
            if (!empty($fieldRules)) {
                $rules[$field->name] = $fieldRules;
            }
        }

        return $rules;
    }
}
