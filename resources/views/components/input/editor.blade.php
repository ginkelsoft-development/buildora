<div class="relative">
    <textarea name="{{ $field->name }}" id="{{ $field->name }}" rows="20"
              class="w-full border border-border rounded-lg p-3 bg-input text-foreground focus:ring-2 focus:ring-ring transition shadow-sm editorfield">
        {{ old($field->name, $value ?? '') }}
    </textarea>

    @include('buildora::components.field.help')
</div>
@include('buildora::components.field.error', ['field' => $field])
