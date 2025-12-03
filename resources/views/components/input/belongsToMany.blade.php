@props(['field'])

@php
    $relation = $field->name;
    $displayColumn = $field->displayColumn ?? 'name';
    $returnValue = $field->returnColumn ?? 'id';

    $selectedValues = $field->value ?? [];

    $relatedModelClass = $field->getRelatedModel();
    $options = $relatedModelClass::query()
        ->pluck($displayColumn, $returnValue)
        ->toArray();
@endphp

<div class="relative">
    <x-buildora::multi-select-dropdown
        :name="$relation"
        :options="$options"
        :selected="$selectedValues"
    />

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
