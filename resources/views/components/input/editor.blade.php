<div class="relative">
    <textarea name="{{ $field->name }}" id="{{ $field->name }}" rows="10"
              class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition shadow-sm">
        {{ old($field->name, $value ?? '') }}
    </textarea>

    @include('buildora::components.field.help')
</div>
@include('buildora::components.field.error', ['field' => $field])

<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ClassicEditor
            .create(document.querySelector('#{{ $field->name }}'))
            .catch(error => console.error(error));
    });
</script>
