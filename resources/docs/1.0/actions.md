# Actions

---

- [Introduction](#introduction)
- [Row Actions](#row-actions)
- [Bulk Actions](#bulk-actions)
- [Page Actions](#page-actions)
- [Full Example](#example)

<a name="introduction"></a>
## Introduction

Actions allow users to perform operations on records. Buildora supports three types: Row Actions, Bulk Actions, and Page Actions.

<a name="row-actions"></a>
## Row Actions

Row actions appear in the actions column of the datatable and operate on individual records.

### Creating Row Actions

```php
use Ginkelsoft\Buildora\Actions\RowAction;

public function defineRowActions(): array
{
    return [
        RowAction::make('View Details', 'fas fa-eye', 'route', 'products.show')
            ->params(['id' => 'id']),

        RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
            ->params(['resource' => 'products', 'id' => 'id']),

        RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
            ->method('DELETE')
            ->confirm('Are you sure you want to delete this product?'),
    ];
}
```

### RowAction Methods

#### make()

Factory method to create a new action:

```php
RowAction::make(
    string $label,      // Button label
    string $icon,       // FontAwesome icon class
    string $type,       // 'route' or 'url'
    string $action      // Route name or URL
): RowAction
```

#### method()

Set the HTTP method:

```php
->method('GET')     // Default
->method('POST')
->method('PUT')
->method('DELETE')
```

#### params()

Define route parameters. Use the field name as the value to substitute:

```php
->params(['id' => 'id'])  // Substitutes record's 'id' field
->params([
    'resource' => 'products',  // Static value
    'id' => 'id',              // Dynamic from record
])
```

#### confirm()

Show a confirmation dialog before executing:

```php
->confirm('Are you sure you want to delete this item?')
```

#### permission()

Require a permission to see/use this action:

```php
->permission('product.delete')
```

<a name="bulk-actions"></a>
## Bulk Actions

Bulk actions operate on multiple selected records from the datatable.

### Creating Bulk Actions

```php
use Ginkelsoft\Buildora\Actions\BulkAction;

public function defineBulkActions(): array
{
    return [
        BulkAction::make('Delete Selected', 'products.bulk-delete')
            ->method('DELETE')
            ->permission('product.delete'),

        BulkAction::make('Export Selected', 'products.bulk-export')
            ->method('POST')
            ->permission('product.export'),
    ];
}
```

### BulkAction Methods

#### make()

```php
BulkAction::make(
    string $label,           // Button label
    string $route,           // Route name
    array $parameters = []   // Additional route parameters
): BulkAction
```

#### method()

```php
->method('POST')    // Default
->method('DELETE')
```

#### permission()

```php
->permission('product.bulk-delete')
```

### Handling Bulk Actions

In your controller, receive the selected IDs:

```php
// routes/web.php
Route::post('products/bulk-publish', [ProductController::class, 'bulkPublish'])
    ->name('products.bulk-publish');

// ProductController.php
public function bulkPublish(Request $request)
{
    $ids = $request->input('ids', []);
    Product::whereIn('id', $ids)->update(['status' => 'published']);
    return back()->with('success', count($ids) . ' products published.');
}
```

> {info} Buildora automatically includes export actions (XLSX, CSV) for bulk operations.

<a name="page-actions"></a>
## Page Actions

Page actions appear in the header of the index page.

```php
use Ginkelsoft\Buildora\Actions\PageAction;

public function definePageActions(): array
{
    return [
        PageAction::make('Import Products', 'products.import')
            ->icon('fas fa-upload')
            ->permission('product.import'),
    ];
}
```

<a name="example"></a>
## Full Example

```php
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Actions\RowAction;
use Ginkelsoft\Buildora\Actions\BulkAction;
use Ginkelsoft\Buildora\Actions\PageAction;

class ProductBuildora extends BuildoraResource
{
    protected string $modelClass = \App\Models\Product::class;

    public function defineFields(): array
    {
        // ... fields
    }

    public function defineRowActions(): array
    {
        return [
            RowAction::make('View', 'fas fa-eye', 'route', 'buildora.show')
                ->params(['resource' => 'products', 'id' => 'id'])
                ->permission('product.view'),

            RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
                ->params(['resource' => 'products', 'id' => 'id'])
                ->permission('product.edit'),

            RowAction::make('Duplicate', 'fas fa-copy', 'route', 'products.duplicate')
                ->method('POST')
                ->params(['id' => 'id'])
                ->confirm('Create a copy of this product?')
                ->permission('product.create'),

            RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
                ->method('DELETE')
                ->params(['resource' => 'products', 'id' => 'id'])
                ->confirm('Delete this product?')
                ->permission('product.delete'),
        ];
    }

    public function defineBulkActions(): array
    {
        return [
            BulkAction::make('Publish Selected', 'products.bulk-publish')
                ->method('POST')
                ->permission('product.publish'),

            BulkAction::make('Delete Selected', 'products.bulk-delete')
                ->method('DELETE')
                ->permission('product.delete'),
        ];
    }

    public function definePageActions(): array
    {
        return [
            PageAction::make('Import', 'products.import')
                ->icon('fas fa-upload')
                ->permission('product.import'),

            PageAction::make('Export All', 'products.export-all')
                ->icon('fas fa-download')
                ->permission('product.export'),
        ];
    }
}
```

## Action Icons

Common FontAwesome icons:

| Action | Icon |
|--------|------|
| View | `fas fa-eye` |
| Edit | `fas fa-edit` |
| Delete | `fas fa-trash` |
| Duplicate | `fas fa-copy` |
| Download | `fas fa-download` |
| Upload | `fas fa-upload` |
| Print | `fas fa-print` |
| Email | `fas fa-envelope` |
