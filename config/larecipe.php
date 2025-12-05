<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Documentation Routes
    |--------------------------------------------------------------------------
    |
    | These options configure the documentation routes.
    |
    */

    'docs' => [
        'route' => '/buildora/docs',
        'path' => '/resources/docs',
        'landing' => 'index',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Versions
    |--------------------------------------------------------------------------
    |
    | Here you can define the available documentation versions.
    |
    */

    'versions' => [
        'default' => '1.0',
        'published' => [
            '1.0',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Settings
    |--------------------------------------------------------------------------
    |
    | Configure documentation behavior.
    |
    */

    'settings' => [
        'auth' => false,
        'ga_id' => '',
        'middleware' => [],
        'edit_on_github' => [
            'enabled' => true,
            'repo' => 'https://github.com/ginkelsoft/buildora',
            'branch' => 'main',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Appearance
    |--------------------------------------------------------------------------
    |
    | Configure the appearance of the documentation.
    |
    */

    'ui' => [
        'code_theme' => 'dark',
        'fav' => '',
        'fa_v4_shims' => true,
        'show_side_bar' => true,
        'colors' => [
            'primary' => '#667eea',
            'secondary' => '#e8f4f8',
        ],
        'theme_order' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO
    |--------------------------------------------------------------------------
    |
    | Configure SEO settings.
    |
    */

    'seo' => [
        'author' => 'Ginkelsoft',
        'description' => 'Buildora - Laravel Admin Panel Package Documentation',
        'keywords' => 'laravel, admin panel, crud, buildora, datatables',
        'og' => [
            'title' => 'Buildora Documentation',
            'type' => 'website',
            'url' => '',
            'image' => '',
            'description' => 'Buildora - Laravel Admin Panel Package Documentation',
        ],
    ],
];
