# Configuration

---

- [Publishing Configuration](#publishing)
- [Route Settings](#routes)
- [Datatable Settings](#datatable)
- [File Upload Settings](#files)
- [Dashboard Configuration](#dashboard)
- [Navigation](#navigation)
- [Default Resources](#resources)

<a name="publishing"></a>
## Publishing Configuration

```bash
php artisan vendor:publish --tag=buildora-config
```

This creates `config/buildora.php` in your application.

<a name="routes"></a>
## Route Settings

### Route Prefix

The URL prefix for all Buildora routes:

```php
'route_prefix' => 'buildora',
```

Your admin panel will be accessible at `/buildora/...`

Change to `'admin'` for URLs like `/admin/resource/users`.

### Middleware

Middleware applied to all Buildora routes:

```php
'middleware' => [
    'web',
    'buildora.auth',
    'buildora.ensure-user-resource',
],
```

Add your own middleware:

```php
'middleware' => [
    'web',
    'buildora.auth',
    'buildora.ensure-user-resource',
    'verified',  // Require email verification
],
```

<a name="datatable"></a>
## Datatable Settings

Pagination options for datatables:

```php
'datatable' => [
    'pagination' => [10, 25, 50, 100, 250],
    'default_per_page' => 25,
],
```

<a name="files"></a>
## File Upload Settings

Configure file upload behavior:

```php
'files' => [
    'default_disk' => 'public',
    'default_path' => 'uploads',
    'max_upload_size_kb' => 2048,
    'previewable' => ['jpg', 'jpeg', 'png', 'pdf'],
],
```

| Option | Description |
|--------|-------------|
| `default_disk` | Laravel filesystem disk |
| `default_path` | Default upload path |
| `max_upload_size_kb` | Maximum file size in KB |
| `previewable` | File types that show inline preview |

<a name="dashboard"></a>
## Dashboard Configuration

```php
'dashboards' => [
    'enabled' => true,
    'label' => 'Dashboard',
    'icon' => 'fa fa-gauge',
    'route' => 'buildora.dashboard',
    'permission' => 'dashboard.view',
    'widgets' => [
        \App\Buildora\Widgets\StatsWidget::class,
        \App\Buildora\Widgets\RecentActivityWidget::class,
    ],
],
```

| Option | Description |
|--------|-------------|
| `enabled` | Show dashboard in navigation |
| `label` | Navigation label |
| `icon` | FontAwesome icon class |
| `route` | Dashboard route name |
| `permission` | Required permission |
| `widgets` | Array of widget classes |

<a name="navigation"></a>
## Navigation

Define custom navigation structure:

```php
'navigation' => [
    [
        'label' => 'Dashboard',
        'icon' => 'fas fa-gauge',
        'route' => 'buildora.dashboard',
    ],
    [
        'label' => 'Products',
        'icon' => 'fas fa-box',
        'route' => 'buildora.index',
        'route_params' => ['resource' => 'products'],
    ],
    [
        'label' => 'Settings',
        'icon' => 'fas fa-cog',
        'children' => [
            [
                'label' => 'Users',
                'route' => 'buildora.index',
                'route_params' => ['resource' => 'users'],
            ],
            [
                'label' => 'Permissions',
                'route' => 'buildora.index',
                'route_params' => ['resource' => 'permissions'],
            ],
        ],
    ],
    'include_resources' => true,  // Auto-include all resources
],
```

### Navigation Item Options

| Option | Description |
|--------|-------------|
| `label` | Display text |
| `icon` | FontAwesome icon |
| `route` | Route name |
| `route_params` | Route parameters |
| `url` | External URL (instead of route) |
| `children` | Nested navigation items |
| `permission` | Required permission |

<a name="resources"></a>
## Default Resources

Configure built-in resources:

```php
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
```

Disable a default resource:

```php
'user' => [
    'enabled' => false,
],
```

Use a custom class:

```php
'user' => [
    'enabled' => true,
    'class' => \App\Buildora\Resources\CustomUserBuildora::class,
],
```

## Full Example

```php
<?php

return [
    'route_prefix' => 'admin',

    'middleware' => [
        'web',
        'buildora.auth',
        'buildora.ensure-user-resource',
    ],

    'models_namespace' => 'App\\Models\\',

    'datatable' => [
        'pagination' => [10, 25, 50, 100],
        'default_per_page' => 25,
    ],

    'files' => [
        'default_disk' => 'public',
        'default_path' => 'uploads',
        'max_upload_size_kb' => 5120,
        'previewable' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'],
    ],

    'dashboards' => [
        'enabled' => true,
        'label' => 'Dashboard',
        'icon' => 'fa fa-gauge',
        'permission' => 'dashboard.view',
        'widgets' => [
            \App\Buildora\Widgets\StatsWidget::class,
        ],
    ],

    'resources' => [
        'defaults' => [
            'user' => ['enabled' => true],
            'permission' => ['enabled' => true],
        ],
    ],
];
```
