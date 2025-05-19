@props(['field', 'value'])

@php
    $previewableExtensions = config('buildora.files.previewable', ['jpg', 'jpeg', 'png', 'pdf']);
    $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
    $acceptTypes = $field->getAcceptArray();
    $previewUrl = $value ? Storage::disk($field->getDisk())->url($value) : '';
@endphp

<div
        x-data='{
        fileName: "",
        previewUrl: @json($previewUrl),
        errorMessage: "",
        accept: @json($acceptTypes)
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
                errorMessage = 'Ongeldig bestandstype.';
                return;
            }

            errorMessage = '';
            fileName = file.name;
            previewUrl = URL.createObjectURL(file);
            $refs.input.files = files;
        }
    "
        @dragover.prevent
        @dragenter.prevent
        @drop.prevent="handleFiles($event)"
        class="flex flex-col gap-2"
>
    <!-- Drop zone -->
    <label for="{{ $field->name }}"
           class="border-2 border-dashed border-border rounded-lg p-6 text-center cursor-pointer transition hover:border-primary bg-muted text-muted-foreground">
        <div>
            <i class="fas fa-cloud-upload-alt text-2xl mb-2"></i>
            <p class="font-semibold">{{ __buildora('Drag a file here or click to choose') }}</p>
            <p x-show="fileName" class="mt-2 text-primary" x-text="fileName"></p>
        </div>

        <div class="relative">
            <input
                    type="file"
                    name="{{ $field->name }}"
                    id="{{ $field->name }}"
                    x-ref="input"
                    @change="handleFiles($event)"
                    @if($field->getAccept()) accept="{{ $field->getAccept() }}" @endif
                    class="hidden"
            />
            @include('buildora::components.field.help')
        </div>
    </label>

    <!-- Foutmelding -->
    <p x-show="errorMessage" class="text-destructive text-sm mt-1" x-text="errorMessage"></p>

    <!-- Preview -->
    @if ($field->shouldShowPreview() && in_array($extension, $previewableExtensions))
        <template x-if="previewUrl">
            <div class="mt-4 rounded-lg overflow-hidden border border-border shadow-sm">
                @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <img :src="previewUrl" alt="Preview" class="mx-auto max-w-full max-h-96 object-contain rounded" />
                @else
                    <iframe :src="previewUrl"
                            class="w-full max-h-96 rounded"
                            style="height: 400px;" frameborder="0"></iframe>
                @endif
            </div>
        </template>
    @endif

    <!-- Bestandslink -->
    @if (!empty($value))
        <div class="text-sm text-muted-foreground">
            {{ __buildora('Current file') }}:
            <a href="{{ $previewUrl }}" target="_blank"
               class="underline text-primary">
                {{ basename($value) }}
            </a>
        </div>
    @endif

    @include('buildora::components.field.error')
</div>
