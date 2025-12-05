# Permissions

---

- [Introduction](#introduction)
- [Permission Format](#format)
- [Generating Permissions](#generating)
- [Using Permissions](#using)
- [Super Admin](#super-admin)

<a name="introduction"></a>
## Introduction

Buildora integrates with [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) to provide role-based access control.

<a name="format"></a>
## Permission Format

Permissions follow the format: `{resource}.{action}`

| Permission | Description |
|------------|-------------|
| `product.view` | View product list and details |
| `product.create` | Create new products |
| `product.edit` | Edit existing products |
| `product.delete` | Delete products |

<a name="generating"></a>
## Generating Permissions

### Generate All Permissions

```bash
php artisan buildora:generate-permissions
```

This scans all Buildora resources and creates permissions for each.

### Sync Permissions

```bash
php artisan buildora:sync-permissions
```

Synchronizes permissions, adding new ones and optionally removing unused ones.

### Grant All to a User

```bash
php artisan buildora:grant-permissions {user_id}
```

### Grant Resource Permissions

```bash
php artisan buildora:permission:grant-resource {user_id} {resource}
```

Example:
```bash
php artisan buildora:permission:grant-resource 1 product
```

This grants `product.view`, `product.create`, `product.edit`, `product.delete` to user 1.

<a name="using"></a>
## Using Permissions

### In Row Actions

```php
RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
    ->params(['resource' => 'products', 'id' => 'id'])
    ->permission('product.edit');
```

### In Bulk Actions

```php
BulkAction::make('Delete Selected', 'products.bulk-delete')
    ->method('DELETE')
    ->permission('product.delete');
```

### In Navigation

```php
'navigation' => [
    [
        'label' => 'Products',
        'icon' => 'fas fa-box',
        'route' => 'buildora.index',
        'route_params' => ['resource' => 'products'],
        'permission' => 'product.view',
    ],
],
```

### Custom Permission Prefix

Override the default permission prefix in your resource:

```php
protected function permissionPrefix(): string
{
    return 'inventory';  // Uses inventory.view, inventory.create, etc.
}
```

### In Blade Views

```html
@can('product.create')
    <a href="{{ route('buildora.create', 'products') }}">
        Create Product
    </a>
@endcan
```

<a name="super-admin"></a>
## Super Admin

For users who should bypass all permission checks, add this to your User model:

```php
public function hasPermissionTo($permission, $guardName = null): bool
{
    if ($this->hasRole('super-admin')) {
        return true;
    }

    return parent::hasPermissionTo($permission, $guardName);
}
```

## Setting Up Roles

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create roles
$admin = Role::create(['name' => 'admin']);
$editor = Role::create(['name' => 'editor']);

// Assign permissions to roles
$admin->givePermissionTo('product.view', 'product.create', 'product.edit', 'product.delete');
$editor->givePermissionTo('product.view', 'product.edit');

// Assign role to user
$user->assignRole('editor');
```
