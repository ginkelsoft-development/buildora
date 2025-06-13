@php
//dd($field->toArray());
        @endphp
<div
        x-data="function () {
        return {
            name: '{{ $field->name }}',
            rows: {{ json_encode($field->toArray()['rows']) }},
            subfields: {{ json_encode($field->toArray()['subfields']) }},
            addRow() {
                const newRow = { __uuid: crypto.randomUUID() };
                this.subfields.forEach(field => newRow[field.name] = '');
                this.rows.push(newRow);
                this.$nextTick(() => window.initCkeditors?.());
            },
            removeRow(index) {
                this.rows.splice(index, 1);
                this.$nextTick(() => window.initCkeditors?.());
            },
            getEditorDataSnapshot() {
                const snapshot = [];

                document.querySelectorAll('textarea.editorfield').forEach((textarea) => {
                    const editor = textarea._ckeditorInstance;
                    if (!editor) return;

                    const uuid = textarea.dataset.uuid;
                    const field = textarea.dataset.field;
                    const content = editor.getData();

                    snapshot.push({ uuid, field, content });
                });

                return snapshot;
            },
            applyEditorDataSnapshot(snapshot) {
                snapshot.forEach(({ uuid, field, content }) => {
                    const row = this.rows.find(r => r.__uuid === uuid);
                    if (row) row[field] = content;
                });
            },
            async destroyEditors() {
                const editors = document.querySelectorAll('textarea.editorfield');
                for (const textarea of editors) {
                    const editor = textarea._ckeditorInstance;
                    if (editor) {
                        await editor.destroy();
                        textarea._ckeditorInstance = null;
                        textarea.classList.remove('ck-initialized');
                    }
                }
            },
            moveUp(index) {
                if (index === 0) return;
                const snapshot = this.getEditorDataSnapshot();
                this.applyEditorDataSnapshot(snapshot);

                this.destroyEditors().then(() => {
                    const temp = this.rows[index];
                    this.rows.splice(index, 1);
                    this.rows.splice(index - 1, 0, temp);
                    this.$nextTick(() => {
                        window.initCkeditors?.();
                    });
                });
            },
            moveDown(index) {
                if (index >= this.rows.length - 1) return;
                const snapshot = this.getEditorDataSnapshot();
                this.applyEditorDataSnapshot(snapshot);

                this.destroyEditors().then(() => {
                    const temp = this.rows[index];
                    this.rows.splice(index, 1);
                    this.rows.splice(index + 1, 0, temp);
                    this.$nextTick(() => {
                        window.initCkeditors?.();
                    });
                });
            },
        }
    }"
        x-init="$nextTick(() => window.initCkeditors?.())"
        class="space-y-6"
>
    <template x-for="(row, index) in rows" :key="row.__uuid">
        <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4">
            <div class="absolute top-3 left-3 flex space-x-2">
                <template x-if="index > 0">
                    <button type="button"
                            @click="moveUp(index)"
                            class="text-gray-400 hover:text-blue-600"
                            title="Verplaats omhoog">
                        <x-buildora-icon class="fas fa-arrow-up"></x-buildora-icon>
                    </button>
                </template>
                <template x-if="index < rows.length - 1">
                    <button type="button"
                            @click="moveDown(index)"
                            class="text-gray-400 hover:text-blue-600"
                            title="Verplaats omlaag">
                        <x-buildora-icon class="fas fa-arrow-down"></x-buildora-icon>
                    </button>
                </template>
            </div>
            <div class="absolute top-3 right-3 flex space-x-2">
                <button type="button"
                        @click="removeRow(index)"
                        class="text-gray-400 hover:text-red-600"
                        title="Verwijder rij">
                    <x-buildora-icon class="fas fa-trash"></x-buildora-icon>
                </button>
            </div>

            <template x-for="field in subfields" :key="field.name">
                <div class="mt-4 mb-4">
                    <label class="block text-sm font-medium text-foreground mb-1" x-text="field.label"></label>

                    <template x-if="field.type === 'text'">
                        <input
                                type="text"
                                class="w-full border border-border rounded-lg p-3 text-foreground bg-muted focus:ring-2 focus:ring-ring focus:outline-none transition shadow-sm"
                                :name="`${name}[${index}][${field.name}]`"
                                x-model="row[field.name]"
                        />
                    </template>

                    <template x-if="field.type === 'textarea' || field.type === 'editor'">
                        <textarea
                                class="ckeditor editorfield"
                                :id="`${name}-${index}-${field.name}`"
                                :data-index="index"
                                :data-uuid="row.__uuid"
                                :data-field="field.name"
                                x-model="row[field.name]"
                                :name="`${name}[${index}][${field.name}]`"
                        ></textarea>
                    </template>
                </div>
            </template>
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
            Voeg rij toe
        </button>
    </div>
</div>

<script>
    window.initCkeditors = function () {
        document.querySelectorAll('textarea.editorfield:not(.ck-initialized)').forEach((el) => {
            ClassicEditor
                .create(el)
                .then((editor) => {
                    el.classList.add('ck-initialized');
                    el._ckeditorInstance = editor;
                })
                .catch(console.error);
        });
    };
</script>
