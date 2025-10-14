# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Buildora is a Laravel package for building admin panels, resources, forms, datatables, widgets and actions — fully based on Eloquent models with minimal configuration. It's a comprehensive CRUD engine that automatically generates resource interfaces from Laravel models.

**Key Technologies:**
- Laravel 10/11/12 + PHP 8.0-8.4
- Livewire 3.0
- Alpine.js 3.14
- Tailwind CSS 4.x with Vite
- Spatie Laravel Permission

## Development Commands

### Frontend Build
```bash
# Build for production
npm run build

# Development mode with hot reload
npm run dev
```

### Testing Buildora in a Laravel Application
Since this is a package, it's typically developed using a local path repository setup. To install in a consuming Laravel app:

```json
// In the Laravel app's composer.json:
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

Then run:
```bash
composer require ginkelsoft/buildora:*
php artisan buildora:install
```

### Artisan Commands
```bash
# Generate a Buildora resource for a model
php artisan buildora:resource ModelName

# Generate a widget
php artisan buildora:widget WidgetName

# Generate permissions for all resources
php artisan buildora:generate-permissions

# Sync permissions
php artisan buildora:sync-permissions

# Grant all permissions to a user
php artisan buildora:grant-permissions {user_id}

# Create admin user
php artisan buildora:create-user

# Generate Permission resource
php artisan buildora:make-permission-resource
```

## Architecture

### Core Concepts

**BuildoraResource (Abstract Base Class)**
- Location: `src/Resources/BuildoraResource.php`
- Every resource extends this class
- Generated files live in `app/Buildora/Resources/` in the consuming Laravel app
- Defines fields, actions, widgets, and panels via abstract/overridable methods

**Key Methods on BuildoraResource:**
- `defineFields()`: Returns array of Field objects (required)
- `defineRowActions()`: Returns array of RowAction objects
- `defineBulkActions()`: Returns array of BulkAction objects
- `defineWidgets()`: Returns array of Widget objects
- `definePanels()`: Returns array of Panel objects for relations
- `searchResultConfig()`: Configures global search behavior
- `showInNavigation()`: Whether to show in nav menu
- `query()`: Static method returning BuildoraQueryBuilder with auto-eager-loading

**Field System**
- Base class: `src/Fields/Field.php`
- All field types in `src/Fields/Types/`
- Uses traits: HasSearch, HasVisibility, HasLayout, HasValidation
- Fields use fluent API: `TextField::make('name')->sortable()->readonly()`

**Available Field Types:**
- TextField, EmailField, PasswordField, NumberField, CurrencyField
- DateField, DateTimeField
- BooleanField, SelectField
- RichTextField, EditorField, JsonField
- FileField, ViewField, DisplayField, IDField
- BelongsToField, AsyncBelongsToField, HasManyField, HasOneField, BelongsToManyField

**Datatable System**
- `BuildoraDatatable`: Main datatable class (`src/Datatable/BuildoraDatatable.php`)
- `DataFetcher`: Handles querying and pagination
- `ColumnBuilder`: Builds columns from resource fields
- `RowFormatter`: Formats rows for display
- Datatables can work in standalone mode or relation mode (via `fromRelation()`)

**Query System**
- `QueryFactory`: Factory for creating BuildoraQueryBuilder instances
- **Automatic eager loading**: `QueryFactory` automatically eager-loads relations defined in `definePanels()` to avoid N+1 queries
- `BuildoraQueryBuilder`: Wraps Eloquent query builder, tracks resource class

**Panel System (Relation Layouts)**
- Panels display related data on detail pages
- Defined in `definePanels()` on the resource
- Uses `Panel::relation()` or `Panel::resource()` to bind relations
- Example: `Panel::relation('orders', OrderBuildora::class)->label('Orders')`
- Panels automatically eager-load their relations via QueryFactory

**Actions**
- `RowAction`: Actions on individual records (`src/Actions/RowAction.php`)
- `BulkAction`: Actions on multiple selected records (`src/Actions/BulkAction.php`)

**Controllers**
- `BuildoraController`: Main CRUD operations (create, store, edit, update, destroy)
- `BuildoraDataTableController`: Handles datatable AJAX requests
- `RelationDatatableController`: Optimized controller for relation datatables (avoid N+1)
- `BuildoraDashboardController`: Dashboard rendering
- `GlobalSearchController`: Global search across resources
- `BuildoraExportController`: Export functionality

**Widgets**
- Base class: `src/Widgets/BuildoraWidget.php`
- Custom widgets extend this and implement `render(): string`
- Used on dashboards or as detail page sections

**Service Providers**
- `BuildoraServiceProvider`: Main provider (registers commands, middleware, publishes assets)
- `BuildoraDatatableServiceProvider`: Registers datatable routes and services

### Middleware
- `BuildoraAuthenticate`: Handles authentication for Buildora routes
- `CheckBuildoraPermission`: Checks Spatie permissions
- `EnsureUserResourceExists`: Ensures User resource exists

### Routing
Routes are defined in `routes/buildora.php` and use the prefix defined in config (`buildora` by default).

### Configuration
Main config: `config/buildora.php`
- Route prefix and middleware
- Models namespace
- Datatable pagination defaults
- File upload settings
- Dashboard configuration
- Navigation structure

### Frontend Build System
**Vite Configuration** (`vite.config.js`):
- Entry points: `resources/js/app.js` and `resources/css/entry.css`
- Theme override support: checks for `../../resources/buildora/buildora-theme.css`, falls back to package theme
- Builds to `dist/assets/`

**Tailwind Configuration** (`tailwind.config.js`):
- Uses CSS variables for theming (supports dark mode via `class` strategy)
- Custom color system based on RGB variables
- Safelist includes dynamic col-span classes

**CSS Architecture:**
- Theme defined via CSS variables in `resources/css/buildora-theme.css`
- Override by publishing: `php artisan vendor:publish --tag=buildora-theme`
- Variables include: colors, spacing, shadows, borders, z-indexes

### Key Design Patterns

1. **Resource-First Architecture**: Everything revolves around BuildoraResource instances
2. **Fluent Field API**: Fields are configured via chained methods
3. **Automatic Model Resolution**: ModelResolver maps resource classes to Eloquent models
4. **Query Factory Pattern**: Centralized query creation with automatic eager loading
5. **Trait-Based Features**: Fields compose behavior via traits (HasSearch, HasValidation, etc.)
6. **Component-Based Views**: Uses Blade components extensively (`<x-buildora-*>`)

### Important Implementation Details

**Field Validation**
- Fields have validation via `->rules()` method
- Validation rules passed to `FieldValidator::validate()`
- Rules extracted via `getValidationRules()` on field

**File Handling**
- FileField handles uploads via Laravel's storage system
- Configured disk and path via field methods: `->disk('public')->path('uploads')`
- Max size and previewable types in config

**Password Handling**
- PasswordField automatically bcrypts values
- Empty passwords are ignored (not updated)

**Global Search**
- Configured per resource via `searchResultConfig()`
- Returns label (string/array/callable) and columns to display

**Permissions**
- Uses Spatie Laravel Permission package
- Automatic permission generation per resource
- Format: `{resource}.{action}` (e.g., `user.view`, `user.create`)

**Navigation**
- Can be manually defined in config or auto-generated from resources
- Resources can opt-out via `showInNavigation(): bool`

## Common Workflows

### Creating a New Field Type
1. Create class in `src/Fields/Types/` extending `Field`
2. Override `render()` method to return field HTML
3. Add any special methods (e.g., `->disk()` for FileField)
4. Optionally override `setValue()`, `getDisplayValue()`, validation logic

### Creating a New Resource
In the consuming Laravel app:
```bash
php artisan buildora:resource Product
```

This creates `app/Buildora/Resources/ProductBuildora.php` with:
- Auto-detected fields from model schema
- Basic CRUD setup
- Ready to customize with additional fields, actions, panels

### Adding Relation Panels
In your resource's `definePanels()`:
```php
public function definePanels(): array
{
    return [
        Panel::relation('orders', OrderBuildora::class)->label('Orders'),
        Panel::relation('invoices', InvoiceBuildora::class),
    ];
}
```

These relations are automatically eager-loaded by QueryFactory to prevent N+1 queries.

### Creating Custom Actions
Extend `RowAction` or `BulkAction` and implement:
- `handle()`: Business logic
- `confirm()`: Confirmation message (optional)

### Theme Customization
1. Publish theme: `php artisan vendor:publish --tag=buildora-theme`
2. Edit `resources/buildora/buildora-theme.css` in the Laravel app
3. Override CSS variables for colors, spacing, etc.
4. Vite automatically picks up the override

## Code Style Notes
- Uses Dutch language in some UI labels and comments (e.g., "Gebruikers" = Users)
- Exceptions thrown as `BuildoraException`
- Heavy use of Laravel collections and helpers (`collect()`, `blank()`, etc.)
- Follows PSR-4 autoloading: `Ginkelsoft\Buildora` namespace maps to `src/`
