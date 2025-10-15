<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Buildora Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the core settings for the Buildora CRUD package.
    | You can customize route behavior, middleware, file settings,
    | datatable defaults, and resource navigation here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This value will be prefixed to all Buildora routes. You can customize
    | it to change the base URL path (e.g., /admin or /dashboard).
    |
    */
    'route_prefix' => 'buildora',

    /*
    |--------------------------------------------------------------------------
    | Middleware Stack
    |--------------------------------------------------------------------------
    |
    | These middleware will be applied to all Buildora routes. You can add
    | your own authentication, authorization, or context-specific middleware.
    |
    */
    'middleware' => [
        'web',
        'buildora.auth',
        'buildora.ensure-user-resource',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace Buildora will use to locate your Eloquent models.
    | All model class names will be resolved relative to this namespace.
    |
    */
    'models_namespace' => 'App\\Models\\',

    /*
    |--------------------------------------------------------------------------
    | Datatable Defaults
    |--------------------------------------------------------------------------
    |
    | Configure the pagination options for Buildora-powered datatables.
    | These values determine the selectable page sizes and the default.
    |
    */
    'datatable' => [
        'pagination' => [10, 25, 50, 100, 250],     // Available rows-per-page options
        'default_per_page' => 25,                   // Default selected pagination size
        'pagination_strategy' => env('BUILDORA_PAGINATION_STRATEGY', 'length_aware'), // length_aware|simple
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the file upload behavior for Buildora fields. You can define
    | the default disk, upload path, max upload size (in KB), and which file
    | types can be previewed in the UI.
    |
    */
    'files' => [
        'default_disk' => 'public',                 // Filesystem disk to use
        'default_path' => 'uploads',                // Default upload folder
        'max_upload_size_kb' => 2048,               // Max file size in kilobytes (2 MB)
        'previewable' => ['jpg', 'jpeg', 'png', 'pdf'], // Extensions that can be shown inline
    ],

    'dashboards' => [
        'enabled' => true,
        'label' => 'Dashboards',
        'icon' => 'fa fa-gauge',
        'children' => [
            'main' => [
                'label' => 'Main',
                'route' => 'buildora.dashboard',
                'params' => ['name' => 'main'],
                'permission' => 'dashboard.view',
                'widgets' => [],
            ],
            'marketing' => [
                'label' => 'Marketing',
                'route' => 'buildora.dashboard',
                'params' => ['name' => 'marketing'],
                'permission' => 'dashboard.view',
                'widgets' => [],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Configuration
    |--------------------------------------------------------------------------
    |
    | Define the navigation items that will be shown in the Buildora UI.
    | You can provide custom links or let Buildora include all resources
    | automatically via the 'include_resources' flag.
    |
    */
    'navigation' => [
        [
            'label' => 'Settings',
            'icon' => 'fas fa-cog',
            'children' => [
                [
                    'label' => 'Gebruikers',
                    'icon' => 'fas fa-user',
                    'route' => 'buildora.index',
                    'params' => [
                        'resource' => 'user',
                    ]
                ],
                [
                    'label' => 'Permissies',
                    'icon' => 'fas fa-key',
                    'route' => 'buildora.index',
                    'params' => [
                        'resource' => 'permission',
                    ]
                ],
            ],
        ],

        'include_resources' => true, // Laat overige resources automatisch toevoegen
    ],
];
