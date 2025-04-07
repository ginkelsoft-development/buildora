@php
    $relationName = $relation->relationName;
    $label = $relation->label ?? ucfirst($relationName);
    $parentResource = $resource->uriKey();
    $parentId = $model->getKey();
    $endpoint = "/buildora/resource/{$parentResource}/{$parentId}/relation/{$relationName}";
@endphp

<div class="mt-8" wire:loading.remove>
    <h3 class="font-semibold text-lg mb-2">{{ $label }}</h3>

    <x-buildora::datatable :endpoint="$endpoint" />
</div>
