<div class="relative">
    <textarea name="{{ $field->name }}" id="{{ $field->name }}" rows="10"
              class="w-full border border-border rounded-lg p-3 bg-input text-foreground focus:ring-2 focus:ring-ring transition shadow-sm">
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
