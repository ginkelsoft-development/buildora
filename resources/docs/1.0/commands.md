# Artisan Commands

---

- [Resource Commands](#resource)
- [Widget Commands](#widget)
- [Permission Commands](#permission)
- [User Commands](#user)
- [Installation Commands](#installation)

<a name="resource"></a>
## Resource Commands

### buildora:resource

Generate a new Buildora resource for an Eloquent model.

```bash
php artisan buildora:resource {ModelName}
```

**Example:**
```bash
php artisan buildora:resource Product
```

Creates `app/Buildora/Resources/ProductBuildora.php` with auto-detected fields from database schema.

### buildora:make-permission-resource

Generate the Permission resource for managing permissions.

```bash
php artisan buildora:make-permission-resource
```

<a name="widget"></a>
## Widget Commands

### buildora:widget

Interactive command to generate a widget.

```bash
php artisan buildora:widget
```

Creates:
- Widget class: `app/Buildora/Widgets/{Name}Widget.php`
- Blade view: `resources/views/buildora/widgets/{name}.blade.php`

<a name="permission"></a>
## Permission Commands

### buildora:generate-permissions

Generate permissions for all Buildora resources.

```bash
php artisan buildora:generate-permissions
```

Creates permissions: `{resource}.view`, `{resource}.create`, `{resource}.edit`, `{resource}.delete`

### buildora:sync-permissions

Synchronize permissions with the database.

```bash
php artisan buildora:sync-permissions
```

### buildora:grant-permissions

Grant all Buildora permissions to a user.

```bash
php artisan buildora:grant-permissions {user_id}
```

### buildora:permission:grant-resource

Grant all permissions for a specific resource to a user.

```bash
php artisan buildora:permission:grant-resource {user_id} {resource}
```

**Example:**
```bash
php artisan buildora:permission:grant-resource 1 product
```

<a name="user"></a>
## User Commands

### buildora:user:create

Interactive command to create a new admin user.

```bash
php artisan buildora:user:create
```

<a name="installation"></a>
## Installation Commands

### buildora:install

Run the Buildora installation wizard.

```bash
php artisan buildora:install
```

### buildora:upgrade

Upgrade an existing Buildora installation.

```bash
php artisan buildora:upgrade
```

## Publishing Assets

```bash
# Publish configuration
php artisan vendor:publish --tag=buildora-config

# Publish views
php artisan vendor:publish --tag=buildora-views

# Publish theme
php artisan vendor:publish --tag=buildora-theme
```

## Quick Reference

| Command | Description |
|---------|-------------|
| `buildora:install` | Initial installation |
| `buildora:upgrade` | Upgrade installation |
| `buildora:resource {Model}` | Generate resource |
| `buildora:widget` | Generate widget |
| `buildora:generate-permissions` | Generate all permissions |
| `buildora:sync-permissions` | Sync permissions |
| `buildora:grant-permissions {id}` | Grant all permissions |
| `buildora:user:create` | Create admin user |
