<?php

/*
|--------------------------------------------------------------------------
| Buildora Translation Helper
|--------------------------------------------------------------------------
*/

if (!function_exists('__buildora')) {
    function __buildora(string $key, array $replace = [], ?string $locale = null): string
    {
        $effectiveLocale = $locale ?? buildora_session_get('locale', 'nl');

        $translated = trans("buildora::buildora.$key", $replace, $effectiveLocale);

        return $translated === "buildora::buildora.$key"
            ? "[LANGUAGE: $key]"
            : $translated;
    }
}

/*
|--------------------------------------------------------------------------
| Buildora Session Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('buildora_session_get')) {
    function buildora_session_get(string $key, mixed $default = null): mixed
    {
        $prefix = config('buildora.session_prefix', 'buildora');
        return session()->get("{$prefix}.{$key}", $default);
    }
}

if (!function_exists('buildora_session_put')) {
    function buildora_session_put(string $key, mixed $value): void
    {
        $prefix = config('buildora.session_prefix', 'buildora');
        session()->put("{$prefix}.{$key}", $value);
    }
}

if (!function_exists('buildora_session_forget')) {
    function buildora_session_forget(string $key): void
    {
        $prefix = config('buildora.session_prefix', 'buildora');
        session()->forget("{$prefix}.{$key}");
    }
}

if (!function_exists('buildora_session_has')) {
    function buildora_session_has(string $key): bool
    {
        $prefix = config('buildora.session_prefix', 'buildora');
        return session()->has("{$prefix}.{$key}");
    }
}
