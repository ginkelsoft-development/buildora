@if (config('buildora.load_fontawesome', true))
    <i class="{{ $class }}"></i>
@else
    <span class="text-gray-500">{{ $fallback }}</span>
@endif
