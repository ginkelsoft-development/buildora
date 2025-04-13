<input type="number"
       name="{{ $field->name }}"
       id="{{ $field->name }}"
       class="w-full border border-border rounded-lg p-3 text-foreground bg-muted focus:ring-2 focus:ring-ring focus:outline-none transition shadow-sm"
       value="{{ old($field->name, $value ?? '') }}" />

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
