<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Ginkelsoft\Buildora\Datatable\BuildoraDatatable;
use Ginkelsoft\Buildora\Support\ResourceResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionMethod;
use Ginkelsoft\Buildora\Fields\Types\FileField;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BuildoraController extends Controller
{
    /**
     * Resolve a Buildora resource by its model name.
     *
     * @param string $model
     * @return object
     */
    protected function getResource(string $model): object
    {
        return ResourceResolver::resolve($model);
    }

    /**
     * Show the create form for a resource.
     *
     * @param string $model
     * @return View
     */
    public function create(string $model): View
    {
        $resource = $this->getResource($model);
        $modelInstance = $resource->getModelInstance();
        $resourceName = ResourceResolver::resolve($model);
        $fields = $resource->resolveFields($modelInstance);

        return view("buildora::form", [
            'model' => $model,
            'fields' => $fields,
            'resource' => $resourceName
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param string $model
     * @return RedirectResponse
     */
    public function store(Request $request, string $model): RedirectResponse
    {
        $resource = $this->getResource($model);
        $modelInstance = $resource->getModelInstance();

        $fields = collect($resource->resolveFields($modelInstance))
            ->reject(fn($field) => $field->readonly)
            ->map(fn($field) => $field->name)
            ->toArray();

        $validationRules = method_exists($resource, 'validationRules')
            ? $resource->validationRules()
            : [];

        $validatedData = $request->validate($validationRules);
        $allowedRequestData = $request->only($fields);
        $finalData = array_merge($allowedRequestData, $validatedData);

        foreach ($resource->resolveFields($modelInstance) as $field) {
            if ($field instanceof FileField && $request->hasFile($field->name)) {
                $uploadedFile = $request->file($field->name);
                $disk = $field->getDisk() ?? 'public';
                $path = $uploadedFile->store($field->getPath() ?? 'uploads', $disk);
                $finalData[$field->name] = $path;

                if (!in_array($field->name, $fields)) {
                    $fields[] = $field->name;
                }
            }
        }

        $filteredData = array_intersect_key($finalData, array_flip($fields));

        if (empty($filteredData)) {
            return redirect()->back()->with('error', "No valid fields to save. Check 'fields()' in $model resource.");
        }

        $createdItem = $modelInstance::create($filteredData);
        $this->handleRelationships($createdItem, $request->all());

        return redirect()->route('buildora.index', ['resource' => $model])
            ->with('success', ucfirst($model) . ' created successfully.');
    }

    /**
     * Show the edit form for the given resource.
     *
     * @param string $model
     * @param int $id
     * @return View
     */
    public function edit(string $model, int $id): View
    {
        $resource = $this->getResource($model);
        $item = $resource->getModelInstance()->findOrFail($id);
        $resourceName = ResourceResolver::resolve($model);
        $fields = $resource->resolveFields($item);

        return view("buildora::form", [
            'model' => $model,
            'fields' => $fields,
            'resource' => $resourceName,
            'item' => $item,
        ]);
    }

    /**
     * Update the given resource instance.
     *
     * @param Request $request
     * @param string $model
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, string $model, int $id): RedirectResponse
    {
        $resource = $this->getResource($model);
        $modelInstance = $resource->getModelInstance();
        $item = $modelInstance->findOrFail($id);

        $fields = collect($resource->resolveFields($modelInstance))
            ->reject(fn($field) => $field->readonly)
            ->map(fn($field) => $field->name)
            ->toArray();

        $validationRules = method_exists($resource, 'validationRules')
            ? $resource->validationRules()
            : [];

        $validatedData = $request->validate($validationRules);
        $allowedRequestData = $request->only($fields);
        $finalData = array_merge($allowedRequestData, $validatedData);

        foreach ($resource->resolveFields($modelInstance) as $field) {
            if ($field instanceof FileField && $request->hasFile($field->name)) {
                $uploadedFile = $request->file($field->name);
                $disk = $field->getDisk() ?? 'public';
                $path = $uploadedFile->store($field->getPath() ?? 'uploads', $disk);
                $finalData[$field->name] = $path;

                if (!in_array($field->name, $fields)) {
                    $fields[] = $field->name;
                }
            }
        }

        $filteredData = array_intersect_key($finalData, array_flip($fields));

        if (empty($filteredData)) {
            return redirect()->back()->with('error', "No valid fields to save. Check 'fields()' in $model resource.");
        }

        $item->update($filteredData);
        $this->handleRelationships($item, $request->all());

        return redirect()->route('buildora.index', ['resource' => $model])
            ->with('success', ucfirst($model) . ' updated successfully.');
    }

    /**
     * Delete a resource instance.
     *
     * @param string $model
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(string $model, int $id): RedirectResponse
    {
        $resource = $this->getResource($model);
        $item = $resource->getModelInstance()->find($id);

        if (!$item) {
            return redirect()->route('buildora.index', ['model' => $model])
                ->with('error', ucfirst($model) . ' not found or already deleted.');
        }

        $item->delete();

        return redirect()->route('buildora.index', ['model' => $model])
            ->with('success', ucfirst($model) . ' deleted successfully.');
    }

    /**
     * Check if a model attribute is a relation.
     *
     * @param object $model
     * @param string $attribute
     * @return bool
     */
    protected function isRelation($model, string $attribute): bool
    {
        if (!method_exists($model, $attribute)) {
            return false;
        }

        $reflection = new ReflectionMethod($model, $attribute);

        if (!$reflection->isPublic() || $reflection->getNumberOfParameters() > 0) {
            return false;
        }

        return $model->$attribute() instanceof Relation;
    }

    /**
     * Handle relationships for a given model item.
     *
     * @param object $item
     * @param array $data
     * @return void
     */
    protected function handleRelationships($item, array $data): void
    {
        foreach ($data as $key => $value) {
            if (!$this->isRelation($item, $key)) {
                continue;
            }

            $relation = $item->$key();

            if (method_exists($relation, 'sync')) {
                $relation->sync($value);
            } elseif (method_exists($relation, 'associate')) {
                $relation->associate($value);
                $item->save();
            } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                $relatedModelClass = get_class($relation->getRelated());
                $modelsToSave = $relatedModelClass::findMany($value);
                $relation->saveMany($modelsToSave);
            }
        }
    }
}
