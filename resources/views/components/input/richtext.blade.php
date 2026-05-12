<div class="relative">
    <div class="p-4 rounded-xl prose prose-sm max-w-none dark:prose-invert"
         style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
        {!! \Ginkelsoft\Buildora\Support\HtmlSanitizer::clean($value ?? '') !!}
    </div>

    @include('buildora::components.field.help')
</div>
