@props(['field', 'value'])

@php
    $previewableExtensions = config('buildora.files.previewable', ['jpg', 'jpeg', 'png', 'pdf']);
    $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
    $acceptTypes = $field->getAcceptArray();
    $previewUrl = $value ? Storage::disk($field->getDisk())->url($value) : '';
@endphp

<div x-data='{
        fileName: "",
        previewUrl: @json($previewUrl),
        errorMessage: "",
        accept: @json($acceptTypes),
        dragOver: false
    }'
     x-init="
        fileName = previewUrl ? '{{ basename($value) }}' : '';
        handleFiles = function(event) {
            const files = event.target.files || event.dataTransfer.files;
            if (!files.length) return;

            const file = files[0];
            const type = file.type;
            const name = file.name.toLowerCase();

            const isAccepted = accept.some(allowed => {
                if (allowed.endsWith('/*')) {
                    return type.startsWith(allowed.replace('/*', ''));
                }
                if (allowed.startsWith('.')) {
                    return name.endsWith(allowed);
                }
                return type === allowed;
            });

            if (!isAccepted) {
                errorMessage = '{{ __buildora('Invalid file type.') }}';
                return;
            }

            errorMessage = '';
            fileName = file.name;
            previewUrl = URL.createObjectURL(file);
            $refs.input.files = files;
        }
    "
     @dragover.prevent="dragOver = true"
     @dragleave.prevent="dragOver = false"
     @dragenter.prevent="dragOver = true"
     @drop.prevent="dragOver = false; handleFiles($event)"
     class="flex flex-col gap-3">

    <label for="{{ $field->name }}"
           class="relative rounded-xl p-6 text-center cursor-pointer transition-all"
           style="background: var(--bg-input); border: 2px dashed var(--border-color);"
           :style="dragOver ? 'border-color: #667eea; background: rgba(102,126,234,0.05)' : ''"
           @mouseenter="$el.style.borderColor='#667eea'"
           @mouseleave="if(!dragOver) $el.style.borderColor='var(--border-color)'">

        <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);">
                <i class="fa fa-cloud-upload-alt text-xl" style="color: #667eea;"></i>
            </div>
            <div>
                <p class="font-medium text-sm" style="color: var(--text-primary);">
                    {{ __buildora('Drag a file here or click to choose') }}
                </p>
                <p x-show="fileName" class="mt-1 text-sm font-medium" style="color: #667eea;" x-text="fileName"></p>
            </div>
        </div>

        <input type="file"
               name="{{ $field->name }}"
               id="{{ $field->name }}"
               x-ref="input"
               @change="handleFiles($event)"
               @if($field->getAccept()) accept="{{ $field->getAccept() }}" @endif
               class="hidden" />

        @include('buildora::components.field.help')
    </label>

    <p x-show="errorMessage"
       class="text-sm px-3 py-2 rounded-lg"
       style="background: rgba(239,68,68,0.1); color: #ef4444;"
       x-text="errorMessage"></p>

    @if ($field->shouldShowPreview() && in_array($extension, $previewableExtensions))
        <template x-if="previewUrl">
            <div class="rounded-xl overflow-hidden" style="border: 1px solid var(--border-color);">
                @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <img :src="previewUrl" alt="Preview" class="mx-auto max-w-full max-h-64 object-contain" />
                @else
                    <iframe :src="previewUrl" class="w-full" style="height: 300px;" frameborder="0"></iframe>
                @endif
            </div>
        </template>
    @endif

    @if (!empty($value))
        <div class="flex items-center gap-2 text-sm" style="color: var(--text-muted);">
            <i class="fa fa-file"></i>
            <span>{{ __buildora('Current file') }}:</span>
            <a href="{{ $previewUrl }}" target="_blank" class="font-medium hover:underline" style="color: #667eea;">
                {{ basename($value) }}
            </a>
        </div>
    @endif

    @include('buildora::components.field.error')
</div>
