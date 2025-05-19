@props(['field'])

@php
    $relation = $field->name;
    $displayColumn = $field->displayColumn ?? 'name';
    $returnValue = $field->returnColumn ?? 'id';

    $selectedValue = $field->value ?? null;

    $relatedModelClass = $field->getRelatedModel();
    $options = $relatedModelClass::query()
        ->pluck($displayColumn, $returnValue)
        ->toArray();

@endphp

<div class="relative">
    <x-buildora::single-select-dropdown
        :name="$relation"
        :options="$options"
        :selected="$selectedValue"
    />

    @include('buildora::components.field.help')
</div>
@include('buildora::components.field.error', ['field' => $field])

