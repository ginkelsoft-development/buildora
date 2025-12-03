@props(['field'])

@php
    $relation = $field->name;
    $displayColumn = $field->displayColumn ?? 'name';
    $returnColumn = $field->returnColumn ?? 'id';
    $selectedValues = is_array($field->value) ? array_map('strval', $field->value) : [];
    $isGrouped = $field->isGrouped();
    $options = $isGrouped ? $field->getGroupedOptions() : ['' => $field->getOptions()];
    $allOptionKeys = [];
    foreach ($options as $group => $items) {
        foreach ($items as $key => $label) {
            $allOptionKeys[] = (string) $key;
        }
    }
@endphp

<div x-data="{
    selected: {{ json_encode($selectedValues) }},
    allOptions: {{ json_encode($allOptionKeys) }},
    searchTerm: '',

    isSelected(key) {
        return this.selected.includes(String(key));
    },

    toggle(key) {
        key = String(key);
        if (this.isSelected(key)) {
            this.selected = this.selected.filter(k => k !== key);
        } else {
            this.selected.push(key);
        }
    },

    selectAll() {
        this.selected = [...this.allOptions];
    },

    deselectAll() {
        this.selected = [];
    },

    selectGroup(keys) {
        keys.forEach(key => {
            key = String(key);
            if (!this.selected.includes(key)) {
                this.selected.push(key);
            }
        });
    },

    deselectGroup(keys) {
        keys = keys.map(String);
        this.selected = this.selected.filter(k => !keys.includes(k));
    },

    isGroupFullySelected(keys) {
        return keys.every(key => this.selected.includes(String(key)));
    },

    matchesSearch(label) {
        if (!this.searchTerm) return true;
        return label.toLowerCase().includes(this.searchTerm.toLowerCase());
    }
}">

    {{-- Modern Toolbar --}}
    <div class="rounded-xl p-4 mb-4" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search --}}
            <div class="relative flex-1 group">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 transition-colors" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       x-model="searchTerm"
                       placeholder="{{ __buildora('Search permissions...') }}"
                       class="w-full h-12 pl-12 pr-4 rounded-xl text-sm focus:outline-none transition-all"
                       style="background: var(--bg-body); border: 1px solid var(--border-color); color: var(--text-primary);"
                       onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 4px rgba(102,126,234,0.1)'"
                       onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'">
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                {{-- Counter --}}
                <div class="h-12 px-4 rounded-xl inline-flex items-center gap-2" style="background: var(--bg-body); border: 1px solid var(--border-color);">
                    <span class="text-lg font-bold" style="color: #667eea;" x-text="selected.length"></span>
                    <span class="text-sm" style="color: var(--text-muted);">/ <span x-text="allOptions.length"></span></span>
                </div>

                {{-- Select All --}}
                <button type="button"
                        @click="selectAll()"
                        class="h-12 px-5 rounded-xl text-sm font-medium text-white inline-flex items-center gap-2 transition-all hover:shadow-lg active:scale-95"
                        style="background: #667eea;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __buildora('Select All') }}
                </button>

                {{-- Deselect All --}}
                <button type="button"
                        @click="deselectAll()"
                        class="h-12 px-5 rounded-xl text-sm font-medium inline-flex items-center gap-2 transition-all hover:bg-opacity-80 active:scale-95"
                        style="background: var(--bg-body); border: 1px solid var(--border-color); color: var(--text-primary);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __buildora('Deselect All') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Checkbox Grid --}}
    <div class="rounded-xl overflow-hidden" style="background: var(--bg-dropdown); border: 1px solid var(--border-color); max-height: 400px; overflow-y: auto;">
        @foreach($options as $group => $items)
            @if($isGrouped && $group !== '')
                @php $groupKeys = array_map('strval', array_keys($items)); @endphp
                <div class="sticky top-0 z-10 px-4 py-3 flex items-center justify-between" style="background: var(--bg-dropdown); border-bottom: 1px solid var(--border-color);">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox"
                               :checked="isGroupFullySelected({{ json_encode($groupKeys) }})"
                               @change="$event.target.checked ? selectGroup({{ json_encode($groupKeys) }}) : deselectGroup({{ json_encode($groupKeys) }})"
                               class="w-5 h-5 rounded-md border-2 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                               style="border-color: var(--border-color);">
                        <span class="text-sm font-semibold" style="color: var(--text-primary);">{{ $group }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full" style="background: rgba(102,126,234,0.1); color: #667eea;">{{ count($items) }}</span>
                    </label>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3">
                @foreach($items as $key => $label)
                    <label x-show="matchesSearch('{{ addslashes($label) }}')"
                           class="flex items-center gap-3 px-4 py-3 cursor-pointer transition-all border-b border-r last:border-r-0"
                           style="border-color: var(--border-color);"
                           :style="isSelected('{{ $key }}') ? 'background: rgba(102, 126, 234, 0.08)' : 'background: transparent'"
                           @mouseenter="$el.style.background = isSelected('{{ $key }}') ? 'rgba(102, 126, 234, 0.12)' : 'var(--bg-hover)'"
                           @mouseleave="$el.style.background = isSelected('{{ $key }}') ? 'rgba(102, 126, 234, 0.08)' : 'transparent'">
                        <input type="checkbox"
                               :checked="isSelected('{{ $key }}')"
                               @change="toggle('{{ $key }}')"
                               class="w-5 h-5 rounded-md border-2 text-indigo-600 focus:ring-indigo-500 cursor-pointer flex-shrink-0"
                               style="border-color: var(--border-color);">
                        <span class="text-sm truncate" :style="isSelected('{{ $key }}') ? 'color: var(--text-primary); font-weight: 500' : 'color: var(--text-secondary)'">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        @endforeach

        <div x-show="allOptions.length === 0" class="py-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm" style="color: var(--text-muted);">{{ __buildora('No records found') }}</p>
        </div>
    </div>

    {{-- Hidden inputs --}}
    <template x-for="key in selected" :key="'hidden-' + key">
        <input type="hidden" name="{{ $relation }}[]" :value="key">
    </template>

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
