<?php

if (!function_exists('__buildora')) {
    function __buildora(string $key, array $replace = [], ?string $locale = null): string
    {
        $translated = trans("buildora::buildora.$key", $replace, $locale);

        if ($translated === "buildora::buildora.$key") {
            return "[MISSING: $key]";
        }

        return $translated;
    }
}






