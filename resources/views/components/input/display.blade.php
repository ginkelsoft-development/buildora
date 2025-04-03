@php
    $content = $field->getContent($model ?? null);
@endphp

<div class="form-group">
    @if ($field->getLabel())
        <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">
            {{ $field->getLabel() }}
        </label>
    @endif

    <div class="p-2 rounded bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-200">
        {!! $content !!}
    </div>
</div>
