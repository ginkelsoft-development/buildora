<?php

namespace Ginkelsoft\Buildora\Http\Controllers;

use Ginkelsoft\Buildora\Datatable\BuildoraDatatable;
use Ginkelsoft\Buildora\Fields\Types\BelongsToField;
use Ginkelsoft\Buildora\Fields\Types\PasswordField;
use Ginkelsoft\Buildora\Fields\Types\RepeatableField;
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
    protected function getResource(string $model): object
    {
        return ResourceResolver::resolve($model);
    }

    public function create(string $model): View
    {
        $resource = $this->getResource($model);
        $modelInstance = $resource->getModelInstance();
        $resourceName = ResourceResolver::resolve($model);
        $fields = $resource->resolveFields($modelInstance);

        foreach ($fields as $field) {
            if ($field instanceof RepeatableField && empty($field->value)) {
                $field->value = null;
            }
        }

        return view("buildora::form", [
            'model' => $model,
            'fields' => $fields,
            'resource' => $resourceName
        ]);
    }

    public function store(Request $request, string $model): RedirectResponse
    {
        $resource = $this->getResource($model);
        $modelInstance = $resource->getModelInstance();

        $resolvedFields = collect($resource->resolveFields($modelInstance));

        $storeColumns = $resolvedFields
            ->reject(fn($field) => $field->readonly)
            ->map(fn($field) => $field->getStoreColumn())
            ->toArray();

        $validationRules = $resolvedFields
            ->mapWithKeys(fn($field) => [$field->name => $field->getValidationRules($modelInstance)])
            ->filter()
            ->toArray();

        $validatedData = $request->validate($validationRules);

        $allowedRequestData = $request->only($storeColumns);
        $finalData = array_merge($allowedRequestData, $validatedData);

        foreach ($resolvedFields as $field) {
            $storeKey = $field->getStoreColumn();

            if ($field instanceof FileField && $request->hasFile($field->name)) {
                $uploadedFile = $request->file($field->name);
                $disk = $field->getDisk() ?? 'public';
                $path = $uploadedFile->store($field->getPath() ?? 'uploads', $disk);
                $finalData[$storeKey] = $path;
            }

            if ($field instanceof PasswordField) {
                $value = $finalData[$storeKey] ?? null;
                $finalData[$storeKey] = blank($value) ? null : bcrypt($value);
            }

            if ($field instanceof BelongsToField) {
                $finalData[$storeKey] = $request->input($field->name);
            }

            if ($field instanceof RepeatableField) {
                $finalData[$storeKey] = $request->input($field->name, []);
            }
        }

        $filteredData = array_intersect_key($finalData, array_flip($storeColumns));

        if (empty($filteredData)) {
            return redirect()->back()
                ->with('error', __buildora('No valid fields to save. Check fields in resource.', [':model' => $model]));
        }

        $createdItem = $modelInstance::create($filteredData);

        $this->handleRelationships($createdItem, $request->all());

        return redirect()->route('buildora.index', ['resource' => $model])
            ->with('success', ucfirst($model) . ' ' . __buildora('created successfully.'));
    }

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

    public function update(Request $request, string $model, int $id): RedirectResponse
    {
        $resource = $this->getResource($model);
        $item = $resource->getModelInstance()->findOrFail($id);

        $resolvedFields = collect($resource->resolveFields($item));

        $storeColumns = $resolvedFields
            ->reject(fn($field) => $field->readonly)
            ->map(fn($field) => $field->getStoreColumn())
            ->toArray();

        $validationRules = $resolvedFields
            ->mapWithKeys(fn($field) => [$field->name => $field->getValidationRules($item)])
            ->filter()
            ->toArray();

        $validatedData = $request->validate($validationRules);
        $allowedRequestData = $request->only($storeColumns);
        $finalData = array_merge($allowedRequestData, $validatedData);

        foreach ($resolvedFields as $field) {
            $storeKey = $field->getStoreColumn();

            if ($field instanceof FileField && $request->hasFile($field->name)) {
                $uploadedFile = $request->file($field->name);
                $disk = $field->getDisk() ?? 'public';
                $path = $uploadedFile->store($field->getPath() ?? 'uploads', $disk);
                $finalData[$storeKey] = $path;
            }

            if ($field instanceof PasswordField) {
                $value = $finalData[$storeKey] ?? null;
                $finalData[$storeKey] = blank($value) ? $item->{$storeKey} : bcrypt($value);
            }

            if ($field instanceof BelongsToField) {
                $finalData[$storeKey] = $request->input($field->name);
            }

            if ($field instanceof RepeatableField) {
                $finalData[$storeKey] = $request->input($field->name, []);
            }
        }

        $filteredData = array_intersect_key($finalData, array_flip($storeColumns));
        $item->update($filteredData);

        $this->handleRelationships($item, $request->all());

        return redirect()
            ->route('buildora.index', ['resource' => $model])
            ->with('success', ucfirst($model) . ' ' . __buildora('updated successfully.'));
    }

    public function show(string $model, int|string $id)
    {
        $resource = ResourceResolver::resolve($model);
        $item = $resource::query()->findOrFail($id);
        $resource->fill($item);

        $customView = $resource->getDetailView();

        return view('buildora::wrapped-detail', [
            'resource' => $resource,
            'item' => $item,
            'fields' => $resource->getFields(),
            'model' => $item,
            'view' => $customView ?? 'buildora::show',
        ]);
    }

    public function destroy(string $model, int $id): RedirectResponse
    {
        $resource = $this->getResource($model);
        $item = $resource->getModelInstance()->find($id);

        if (!$item) {
            return redirect()->route('buildora.index', ['model' => $model])
                ->with('error', ucfirst($model) . ' ' . __buildora('not found or already deleted.'));
        }

        $item->delete();

        return redirect()->route('buildora.index', ['model' => $model])
            ->with('success', ucfirst($model) . ' ' . __buildora('deleted successfully.'));
    }

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

    protected function buildValidationRules($resolvedFields, Request $request): array
    {
        $rules = [];

        foreach ($resolvedFields as $field) {
            if ($field instanceof RepeatableField) {
                $rows = $request->input($field->name, []);
                foreach ($rows as $index => $row) {
                    foreach ($field->getSubfields() as $subfield) {
                        $rules["{$field->name}.{$index}.{$subfield->name}"] = $subfield->getValidationRules();
                    }
                }
            } else {
                $rules[$field->name] = $field->getValidationRules();
            }
        }

        return array_filter($rules);
    }
}
