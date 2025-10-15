<?php

namespace Ginkelsoft\Buildora;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

/**
 * Company: GinkelSoft
 * Created by: W.J. van Ginkel
 * Date: 11/04/2025
 */


class Buildora
{
    /**
     * Get the CSS for the Horizon dashboard.
     *
     * @return Illuminate\Contracts\Support\Htmlable
     */
    public static function css()
    {
        if (($light = @file_get_contents(__DIR__ . '/../dist/assets/style.css')) === false) {
            throw new BuildoraException('Unable to load the Buildora dashboard CSS.');
        }

        $theme = @file_get_contents(resource_path('buildora/buildora-theme.css'));

        if ($theme == true) {
            $theme = '<style>' . $theme . '</style>';
        } else {
            $theme = '';
        }

        return new HtmlString(<<<HTML
            <style>{$light}</style>
            {$theme}
            HTML);
    }

    /**
     * Get the JS for the Horizon dashboard.
     *
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public static function js()
    {
        if (($js = @file_get_contents(__DIR__ . '/../dist/assets/app.js')) === false) {
            throw new BuildoraException('Unable to load the Buildora dashboard JavaScript.');
        }

        return new HtmlString(<<<HTML
            <script type="module">
                {$js}
            </script>
            HTML);
    }
}
