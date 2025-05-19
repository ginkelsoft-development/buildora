<input type="datetime-local" name="{{ $field->name }}" id="{{ $field->name }}"
       class="w-full border border-border rounded-lg p-3 text-foreground bg-input focus:ring-2 focus:ring-primary focus:outline-none transition shadow-sm"
       value="{{ old($field->name, $value ?? '') }}">

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
