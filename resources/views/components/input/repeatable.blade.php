
<div
        x-data="{
        rows: @js($field->rows()),
        addRow() {
            this.rows.push({});
            this.$nextTick(() => window.initCkeditors?.());
        },
        removeRow(index) {
            this.rows.splice(index, 1);
            this.$nextTick(() => window.initCkeditors?.());
        }
    }"
        x-init="$nextTick(() => window.initCkeditors?.())"
        class="space-y-6"
>
    <template x-for="(row, index) in rows" :key="index">
        <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4"
             x-bind:data-repeatable-index="index"
             x-effect="$el.querySelectorAll('[name]').forEach(el => {
                 if (el.name && el.name.includes('\${index}')) {
                     el.name = el.name.replaceAll('\${index}', index);
                 }
             })">
            <button type="button"
                    @click="removeRow(index)"
                    class="absolute top-3 right-3 text-gray-400 hover:text-red-600"
                    title="Verwijder rij">
                <x-buildora-icon class="fas fa-trash"></x-buildora-icon>
            </button>

            <div class="grid grid-cols-1 gap-y-4">
                @foreach ($field->getSubfields() as $subfield)
                    @php
                        $instance = clone $subfield;
                        $instance->originalName = $subfield->name;
                        $instance->name = $field->name . '[${index}][' . $subfield->name . ']';
                        $instance->xModel = "rows[index].{$subfield->name}";
                    @endphp

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $instance->label }}
                        </label>

                        @include('buildora::components.input.' . $subfield->type, ['field' => $instance, 'xModel' => $instance->xModel])
                    </div>
                @endforeach
            </div>
        </div>
    </template>

    <div class="pt-4 text-center">
        <button type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl border border-border text-primary hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring transition"
                @click="addRow">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="currentColor"
                 viewBox="0 0 20 20">
                <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
            </svg>
            Add {{ $field->label }}
        </button>
    </div>
</div>
