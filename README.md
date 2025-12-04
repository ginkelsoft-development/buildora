# Buildora

Buildora is a Laravel package for building admin panels, resources, forms, datatables, widgets and actions — fully based on Eloquent models and a minimal amount of configuration.

---

## 1. Requirements

- Laravel 10, 11 or 12
- PHP 8.1+
- Tailwind CSS (via CDN or Vite)
- Laravel Jetstream (optional)
- `spatie/laravel-permission` (recommended)

---

## 2. Installation via Composer

```bash
composer require ginkelsoft/buildora
```

If you are using a local path-based package:

```json
"repositories": [
  {
    "type": "path",
    "url": "packages/ginkelsoft/buildora",
    "options": {
      "symlink": true
    }
  }
]
```

Then:

```bash
composer require ginkelsoft/buildora:*
```

---

## 3. Publish the config (optional)

If Buildora provides configuration, you can publish it with:

```bash
php artisan vendor:publish --tag=buildora-config
```

---

## 4. Run the interactive installer

```bash
php artisan buildora:install
```

This command will:

- Detect Laravel version
- Run migrations
- Add necessary traits to your User model
- Generate Buildora resources for all your models
- Generate permissions (if Spatie is installed)
- Create a default admin user

---

## 5. Command: `buildora:resource`

Generate a Buildora resource class based on an Eloquent model:

```bash
php artisan buildora:resource User
```

This will create a file like:
`app/Buildora/Resources/UserBuildora.php`

You can customize fields, filters, actions, and views inside this class.

---

## 6. Command: `buildora:widget`

Create a dashboard widget:

```bash
php artisan buildora:widget StatsWidget
```

This will generate:

- `app/Buildora/Widgets/StatsWidget.php`

Each widget implements a `render()` method and can return a Blade view or raw HTML.

---

## 7. Field types

Buildora supports multiple field types. Each field can be configured using a fluent API:

Examples:

```php
TextField::make('name')->sortable()
EmailField::make('email')->readonly()
PasswordField::make('password')->hideFromIndex()
NumberField::make('price')->step(0.01)
CurrencyField::make('amount', '€')
DateTimeField::make('created_at')->readonly()
BelongsToField::make('company_id')->relation('company')
```

You can add new field types by extending the `Field` base class and implementing the `render()` method.

---

## 8. Widgets

Widgets can be used on dashboards or as panels on detail pages.

```php
class TotalUsersWidget extends Widget
{
    public function render(): string
    {
        $count = User::count();

        return view('widgets.total-users', compact('count'))->render();
    }
}
```

Widgets are registered in your resource via:

```php
public function defineWidgets(): array
{
    return [
        TotalUsersWidget::make()->columnSpan(6),
    ];
}
```

---

## 9. Panels

Panels are relation-based data sections shown on the detail page of a resource.

```php
public function definePanels(): array
{
    return [
        Panel::relation('orders', OrderBuildora::class)->label('Recent Orders'),
        Panel::relation('invoices', InvoiceBuildora::class),
    ];
}
```

This will show a datatable of related data on the detail page. Buildora automatically eager-loads these relations to prevent N+1 query issues.

---

## 10. Actions

Actions allow you to perform operations on individual records (RowAction) or multiple selected records (BulkAction).

### Row Actions

Row actions appear on individual rows in datatables and detail pages:

```php
public function defineRowActions(): array
{
    return [
        RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
            ->params(['resource' => 'user', 'id' => '{id}'])
            ->method('GET'),

        RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
            ->params(['resource' => 'user', 'id' => '{id}'])
            ->method('DELETE')
            ->confirm('Are you sure you want to delete this record?'),
    ];
}
```

### Bulk Actions

Bulk actions allow operations on multiple selected records:

```php
public function defineBulkActions(): array
{
    return [
        BulkAction::make('Delete Selected', 'buildora.bulk.delete')
            ->method('DELETE')
            ->confirm('Are you sure you want to delete the selected records?'),

        BulkAction::make('Export Selected', 'buildora.bulk.export')
            ->method('POST'),
    ];
}
```

---

## 11. Permissions

Buildora integrates with Spatie Laravel Permission for authorization. Permissions are automatically generated per resource.

### Available Commands

```bash
# Generate permissions for all resources
php artisan buildora:generate-permissions

# Sync permissions (registers new permissions without deleting existing ones)
php artisan buildora:sync-permissions

# Grant all permissions to a specific user
php artisan buildora:grant-permissions {user_id}

# Create Permission resource for managing permissions in the UI
php artisan buildora:make-permission-resource
```

### Permission Format

Permissions follow the format `{resource}.{action}`:
- `user.view` - View user listings
- `user.create` - Create new users
- `user.edit` - Edit existing users
- `user.delete` - Delete users

### Checking Permissions in Resources

You can control access to actions using permissions:

```php
RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
    ->permission('user.delete');
```

---

## 12. Global Search

Configure global search behavior per resource:

```php
public function searchResultConfig(): array
{
    return [
        'label' => fn($record) => $record->name,
        'columns' => ['name', 'email', 'created_at'],
    ];
}
```

The `label` can be:
- A string (column name)
- An array of column names
- A callable that receives the record and returns a string

---

## 13. Configuration

The main configuration file `config/buildora.php` contains:

### Route Settings
```php
'route_prefix' => 'buildora',  // Base URL path
'middleware' => ['web', 'buildora.auth', 'buildora.ensure-user-resource'],
```

### Models Namespace
```php
'models_namespace' => 'App\\Models\\',
```

### Datatable Defaults
```php
'datatable' => [
    'pagination' => [10, 25, 50, 100, 250],
    'default_per_page' => 25,
],
```

### File Upload Settings
```php
'files' => [
    'default_disk' => 'public',
    'default_path' => 'uploads',
    'max_upload_size_kb' => 2048,
    'previewable' => ['jpg', 'jpeg', 'png', 'pdf'],
],
```

### Dashboard Configuration
```php
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
    ],
],
```

### Navigation Structure
```php
'navigation' => [
    // Link to a Buildora resource
    [
        'label' => 'Users',
        'icon' => 'fas fa-user',
        'route' => 'buildora.index',
        'params' => ['resource' => 'user'],
    ],

    // Link to an external URL
    [
        'label' => 'Documentation',
        'icon' => 'fas fa-book',
        'url' => 'https://docs.example.com',
    ],

    // Link to a custom Laravel route
    [
        'label' => 'Back to site',
        'icon' => 'fas fa-home',
        'url' => '/',
    ],

    // Nested navigation with children
    [
        'label' => 'Settings',
        'icon' => 'fas fa-cog',
        'children' => [
            [
                'label' => 'Users',
                'icon' => 'fas fa-user',
                'route' => 'buildora.index',
                'params' => ['resource' => 'user'],
            ],
            [
                'label' => 'Permissions',
                'icon' => 'fas fa-lock',
                'route' => 'buildora.index',
                'params' => ['resource' => 'permission'],
            ],
        ],
    ],

    'include_resources' => true, // Auto-include all resources not manually defined
],
```

Navigation items support:
- `route` + `params`: Link to a named Laravel route
- `url`: Direct URL (internal or external)
- `children`: Nested dropdown menu

---

## 14. All Available Commands

```bash
# Installation and setup
php artisan buildora:install              # Interactive installer

# Resource generation
php artisan buildora:resource {Model}     # Generate resource from model
php artisan buildora:widget {Name}        # Generate widget

# Permission management
php artisan buildora:generate-permissions # Generate all resource permissions
php artisan buildora:sync-permissions     # Sync permissions
php artisan buildora:grant-permissions {user_id}  # Grant all permissions to user
php artisan buildora:make-permission-resource     # Create Permission resource

# User management
php artisan buildora:create-user          # Create admin user
```

---

## 15. Theme Customization

Buildora uses CSS variables for theming with support for light and dark mode.

### Publishing the Theme

```bash
php artisan vendor:publish --tag=buildora-theme
```

This will create `resources/buildora/buildora-theme.css` in your Laravel application.

### Customizing Colors

Edit the published theme file to override CSS variables:

```css
:root {
    /* Primary colors */
    --primary-rgb: 59, 130, 246;
    --primary-hover-rgb: 37, 99, 235;

    /* Background colors */
    --background-rgb: 255, 255, 255;
    --surface-rgb: 249, 250, 251;

    /* Text colors */
    --text-primary-rgb: 17, 24, 39;
    --text-secondary-rgb: 107, 114, 128;

    /* Border colors */
    --border-rgb: 229, 231, 235;
}

/* Dark mode */
.dark {
    --background-rgb: 17, 24, 39;
    --surface-rgb: 31, 41, 55;
    --text-primary-rgb: 243, 244, 246;
    --text-secondary-rgb: 156, 163, 175;
    --border-rgb: 55, 65, 81;
}
```

The theme system uses RGB values to allow alpha transparency (e.g., `rgba(var(--primary-rgb), 0.5)`).

### Frontend Build

If you're developing the package itself, you can rebuild the assets:

```bash
# Development with hot reload
npm run dev

# Production build
npm run build
```

---

## 16. License

Buildora is open-source software licensed under the MIT license.
