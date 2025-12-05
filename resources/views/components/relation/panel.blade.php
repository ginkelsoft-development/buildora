@php
    $relationName = $relation->relationName;
    $label = $relation->label ?? ucfirst($relationName);
    $parentResource = $resource->uriKey();
    $parentId = $model->getKey();
    $endpoint = "/buildora/resource/{$parentResource}/{$parentId}/relation/{$relationName}";
    $componentKey = 'datatable-' . $relationName;
    $hasInlineEditing = $relation->hasInlineEditing();
    $canCreate = $relation->canInlineCreate();
    $canDelete = $relation->canInlineDelete();
@endphp

<div class="mt-8 rounded-2xl overflow-visible"
     style="background: var(--bg-dropdown); border: 1px solid var(--border-color);"
     x-data="inlineRelationPanel({
        endpoint: '{{ $endpoint }}',
        parentResource: '{{ $parentResource }}',
        parentId: '{{ $parentId }}',
        relationName: '{{ $relationName }}',
        hasInlineEditing: {{ $hasInlineEditing ? 'true' : 'false' }},
        canCreate: {{ $canCreate ? 'true' : 'false' }},
        canDelete: {{ $canDelete ? 'true' : 'false' }}
     })">

    {{-- Panel Header --}}
    <div class="flex items-center justify-between p-4 lg:p-6" style="border-bottom: 1px solid var(--border-color);">
        <h3 class="text-lg font-semibold" style="color: var(--text-primary);">
            {{ $label }}
        </h3>

        @if($hasInlineEditing && $canCreate)
            <button type="button"
                    @click="openCreateModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 btn-primary text-white text-sm font-medium rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                <i class="fa-solid fa-plus"></i>
                {{ __buildora('Add') }}
            </button>
        @endif
    </div>

    {{-- Datatable --}}
    <div class="p-4 lg:p-6">
        <x-buildora::datatable
            :endpoint="$endpoint"
            :component-key="$componentKey"
            :inline-editing="$hasInlineEditing"
            :inline-delete="$canDelete"
        />
    </div>

    {{-- Inline Edit Modal --}}
    @if($hasInlineEditing)
        <div x-show="showModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="closeModal()">

            {{-- Backdrop --}}
            <div class="fixed inset-0 transition-opacity"
                 style="background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);"
                 @click="closeModal()"></div>

            {{-- Modal Content --}}
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative w-full max-w-2xl rounded-2xl shadow-2xl"
                     style="background: var(--bg-dropdown); border: 1px solid var(--border-color);"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop>

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between p-6" style="border-bottom: 1px solid var(--border-color);">
                        <h3 class="text-xl font-semibold" style="color: var(--text-primary);">
                            <span x-text="editingId ? '{{ __buildora('Edit') }}' : '{{ __buildora('Add') }}'"></span>
                            {{ $label }}
                        </h3>
                        <button @click="closeModal()"
                                class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-black/5 dark:hover:bg-white/5"
                                style="color: var(--text-muted);">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6">
                        {{-- Loading State --}}
                        <div x-show="isLoadingFields" class="flex items-center justify-center py-8">
                            <i class="fa-solid fa-spinner fa-spin text-2xl" style="color: var(--text-muted);"></i>
                        </div>

                        {{-- Error State --}}
                        <div x-show="formError && !isLoadingFields" class="mb-4 p-4 rounded-xl"
                             style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-exclamation-circle text-red-500"></i>
                                <span class="text-red-500 text-sm" x-text="formError"></span>
                            </div>
                        </div>

                        {{-- Dynamic Form Fields --}}
                        <form x-show="!isLoadingFields" @submit.prevent="submitForm()" class="space-y-4">
                            <template x-for="field in formFields" :key="field.name">
                                <div>
                                    <label :for="field.name"
                                           class="block text-sm font-medium mb-2"
                                           style="color: var(--text-secondary);"
                                           x-text="field.label">
                                    </label>

                                    {{-- Text Input --}}
                                    <template x-if="['text', 'email', 'number', 'date', 'datetime'].includes(field.type)">
                                        <input :type="field.type === 'datetime' ? 'datetime-local' : field.type"
                                               :name="field.name"
                                               :id="field.name"
                                               :placeholder="field.placeholder"
                                               :readonly="field.readonly"
                                               x-model="formData[field.name]"
                                               class="w-full h-12 px-4 rounded-xl text-sm transition-all focus:outline-none"
                                               style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
                                               onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
                                               onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'" />
                                    </template>

                                    {{-- Password Input --}}
                                    <template x-if="field.type === 'password'">
                                        <input type="password"
                                               :name="field.name"
                                               :id="field.name"
                                               :placeholder="field.placeholder || '••••••••'"
                                               x-model="formData[field.name]"
                                               class="w-full h-12 px-4 rounded-xl text-sm transition-all focus:outline-none"
                                               style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
                                               onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
                                               onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'" />
                                    </template>

                                    {{-- Select Input --}}
                                    <template x-if="field.type === 'select'">
                                        <select :name="field.name"
                                                :id="field.name"
                                                x-model="formData[field.name]"
                                                class="w-full h-12 px-4 rounded-xl text-sm transition-all focus:outline-none"
                                                style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                                            <option value="">{{ __buildora('Select a value...') }}</option>
                                            <template x-for="[key, label] in Object.entries(field.options || {})" :key="key">
                                                <option :value="key" x-text="label"></option>
                                            </template>
                                        </select>
                                    </template>

                                    {{-- Boolean/Checkbox Input --}}
                                    <template x-if="field.type === 'boolean'">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                   :name="field.name"
                                                   x-model="formData[field.name]"
                                                   class="sr-only peer">
                                            <div class="w-11 h-6 rounded-full peer transition-colors duration-200"
                                                 style="background: var(--border-color);"
                                                 :style="formData[field.name] ? 'background: #667eea' : ''">
                                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                                                     :class="formData[field.name] ? 'translate-x-5' : ''"></div>
                                            </div>
                                        </label>
                                    </template>

                                    {{-- Textarea --}}
                                    <template x-if="field.type === 'textarea'">
                                        <textarea :name="field.name"
                                                  :id="field.name"
                                                  :placeholder="field.placeholder"
                                                  x-model="formData[field.name]"
                                                  rows="4"
                                                  class="w-full px-4 py-3 rounded-xl text-sm transition-all focus:outline-none resize-none"
                                                  style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
                                                  onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
                                                  onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'">
                                        </textarea>
                                    </template>

                                    {{-- Help Text --}}
                                    <template x-if="field.help">
                                        <p class="mt-1 text-xs" style="color: var(--text-muted);" x-text="field.help"></p>
                                    </template>

                                    {{-- Field Error --}}
                                    <template x-if="fieldErrors[field.name]">
                                        <p class="mt-1 text-xs text-red-500" x-text="fieldErrors[field.name]"></p>
                                    </template>
                                </div>
                            </template>
                        </form>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-3 p-6" style="border-top: 1px solid var(--border-color);">
                        <button type="button"
                                @click="closeModal()"
                                class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
                                style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                            {{ __buildora('cancel') }}
                        </button>
                        <button type="button"
                                @click="submitForm()"
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 btn-primary text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i x-show="isSubmitting" class="fa-solid fa-spinner fa-spin"></i>
                            <i x-show="!isSubmitting" class="fa-solid fa-check"></i>
                            {{ __buildora('Save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@if($hasInlineEditing)
@once
<script>
function inlineRelationPanel(config) {
    return {
        endpoint: config.endpoint,
        parentResource: config.parentResource,
        parentId: config.parentId,
        relationName: config.relationName,
        hasInlineEditing: config.hasInlineEditing,
        canCreate: config.canCreate,
        canDelete: config.canDelete,

        showModal: false,
        editingId: null,
        formFields: [],
        formData: {},
        fieldErrors: {},
        formError: null,
        isLoadingFields: false,
        isSubmitting: false,

        openCreateModal() {
            this.editingId = null;
            this.formData = {};
            this.fieldErrors = {};
            this.formError = null;
            this.showModal = true;
            this.loadFields();
        },

        openEditModal(itemId) {
            this.editingId = itemId;
            this.formData = {};
            this.fieldErrors = {};
            this.formError = null;
            this.showModal = true;
            this.loadFields(itemId);
        },

        closeModal() {
            this.showModal = false;
            this.editingId = null;
            this.formFields = [];
            this.formData = {};
            this.fieldErrors = {};
            this.formError = null;
        },

        async loadFields(itemId = null) {
            this.isLoadingFields = true;
            this.formError = null;

            const url = itemId
                ? `${this.endpoint}/fields/${itemId}`
                : `${this.endpoint}/fields`;

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || data.message || 'Failed to load fields');
                }

                if (data.error) {
                    throw new Error(data.error);
                }

                this.formFields = data.fields || [];

                // Initialize form data with values
                this.formData = {};
                (data.fields || []).forEach(field => {
                    this.formData[field.name] = field.value ?? '';
                });

            } catch (error) {
                console.error('Load fields error:', error);
                this.formError = error.message;
            } finally {
                this.isLoadingFields = false;
            }
        },

        async submitForm() {
            this.isSubmitting = true;
            this.fieldErrors = {};
            this.formError = null;

            const url = this.editingId
                ? `${this.endpoint}/${this.editingId}`
                : this.endpoint;

            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        // Validation errors
                        Object.keys(data.errors).forEach(key => {
                            this.fieldErrors[key] = data.errors[key][0];
                        });
                    } else {
                        this.formError = data.message || 'An error occurred';
                    }
                    return;
                }

                // Success - close modal and refresh datatable
                this.closeModal();

                // Dispatch event to refresh the datatable
                window.dispatchEvent(new CustomEvent('refresh-datatable-{{ $componentKey }}'));

            } catch (error) {
                this.formError = error.message;
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteItem(itemId) {
            if (!confirm('{{ __buildora("Are you sure you want to delete this record?") }}')) {
                return;
            }

            try {
                const response = await fetch(`${this.endpoint}/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (!response.ok) throw new Error('Failed to delete');

                // Refresh datatable
                window.dispatchEvent(new CustomEvent('refresh-datatable-{{ $componentKey }}'));

            } catch (error) {
                alert(error.message);
            }
        }
    };
}
</script>
@endonce
@endif
