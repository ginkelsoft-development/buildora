<textarea name="{{ $field->name }}"
          id="{{ $field->name }}"
          class="w-full border border-border rounded-lg p-3 bg-muted text-foreground focus:ring-2 focus:ring-ring focus:outline-none transition shadow-sm"
          rows="5">{{ old($field->name, $value ?? '') }}</textarea>

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
