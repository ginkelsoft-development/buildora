<div class="relative">
    <div class="rounded-xl overflow-hidden" style="border: 1px solid var(--border-color);">
        <textarea name="{{ $field->name }}"
                  id="{{ $field->name }}"
                  rows="10"
                  class="w-full p-4 text-sm focus:outline-none"
                  style="background: var(--bg-input); color: var(--text-primary); min-height: 250px;">{{ old($field->name, $value ?? '') }}</textarea>
    </div>

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])

<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ClassicEditor
            .create(document.querySelector('#{{ $field->name }}'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo']
            })
            .then(editor => {
                editor.ui.view.editable.element.style.minHeight = '200px';
                editor.ui.view.editable.element.style.background = 'var(--bg-input)';
                editor.ui.view.editable.element.style.color = 'var(--text-primary)';
            })
            .catch(error => console.error(error));
    });
</script>

<style>
    .ck.ck-editor__main>.ck-editor__editable {
        background: var(--bg-input) !important;
        color: var(--text-primary) !important;
        border-radius: 0 0 0.75rem 0.75rem !important;
    }
    .ck.ck-toolbar {
        background: var(--bg-muted) !important;
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border-color: var(--border-color) !important;
    }
    .ck.ck-editor__editable:not(.ck-editor__nested-editable).ck-focused {
        border-color: #667eea !important;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.1) !important;
    }
</style>
