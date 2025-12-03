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
        'label' => 'Dashboard',
        'icon' => 'fa fa-gauge',
        'route' => 'buildora.dashboard',
        'permission' => 'dashboard.view',
        'widgets' => [],
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
                    'label' => 'Users',
                    'icon' => 'fas fa-user',
                    'route' => 'buildora.index',
                    'params' => [
                        'resource' => 'user',
                    ]
                ],
                [
                    'label' => 'Permissions',
                    'icon' => 'fas fa-key',
                    'route' => 'buildora.index',
                    'params' => [
                        'resource' => 'permission',
                    ]
                ],
            ],
        ],

        'include_resources' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Resources
    |--------------------------------------------------------------------------
    |
    | Enable or disable the packaged Buildora resources. You can also replace
    | the class with your own implementation per resource.
    |
    */
    'resources' => [
        'defaults' => [
            'user' => [
                'enabled' => true,
                'class' => \Ginkelsoft\Buildora\Resources\Defaults\UserBuildora::class,
            ],
            'permission' => [
                'enabled' => true,
                'class' => \Ginkelsoft\Buildora\Resources\Defaults\PermissionBuildora::class,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Models Allowed Without HasBuildora
    |--------------------------------------------------------------------------
    |
    | Some third-party models (like Spatie Permissions) do not ship with the
    | HasBuildora trait. List them here to bypass the trait requirement while
    | still allowing Buildora resources to function.
    |
    */
    'models_allow_without_buildora_trait' => [
        \Spatie\Permission\Models\Permission::class,
    ],
];
