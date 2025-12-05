# Resources

---

- [Introduction](#introduction)
- [Creating a Resource](#creating)
- [Resource Properties](#properties)
- [Defining Fields](#fields)
- [Optional Methods](#optional-methods)
- [Query Customization](#query)
- [Full Example](#example)

<a name="introduction"></a>
## Introduction

Resources are the core building blocks of Buildora. Each resource represents a single Eloquent model and defines how it should be displayed and managed in the admin panel.

<a name="creating"></a>
## Creating a Resource

Use the Artisan command to generate a new resource:

```bash
php artisan buildora:resource Product
```

This creates `app/Buildora/Resources/ProductBuildora.php`:

```php
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use App\Models\Product;

class ProductBuildora extends BuildoraResource
{
    protected string $modelClass = Product::class;

    public function defineFields(): array
    {
        return [
            // Define your fields here
        ];
    }
}
```

<a name="properties"></a>
## Resource Properties

### Model Class

Every resource must define which Eloquent model it represents:

```php
protected string $modelClass = \App\Models\Product::class;
```

### Title

Override the display title (shown in navigation and headers):

```php
public function title(): string
{
    return 'Products';  // Default: class basename
}
```

### URI Key

The URL segment used for this resource:

```php
public function uriKey(): string
{
    return 'products';  // Default: lowercase model name
}
```

### Navigation

Control whether this resource appears in the sidebar:

```php
public function showInNavigation(): bool
{
    return true;  // Default: true
}
```

<a name="fields"></a>
## Defining Fields

The `defineFields()` method is required. Returns an array of Field objects:

```php
public function defineFields(): array
{
    return [
        TextField::make('name', 'Product Name')
            ->searchable()
            ->sortable()
            ->validation(['required', 'max:255']),

        NumberField::make('price', 'Price')
            ->sortable(),

        SelectField::make('status', 'Status')
            ->options([
                'draft' => 'Draft',
                'published' => 'Published',
            ]),

        TextField::make('description', 'Description')
            ->hideFromTable()
            ->columnSpan(12),
    ];
}
```

<a name="optional-methods"></a>
## Optional Methods

### definePanels()

Define relation panels for the detail page:

```php
public function definePanels(): array
{
    return [
        Panel::relation('orders', OrderBuildora::class)
            ->label('Orders'),

        Panel::relation('reviews', ReviewBuildora::class)
            ->label('Customer Reviews')
            ->inlineEditing(),
    ];
}
```

### defineRowActions()

Add custom actions for individual records:

```php
public function defineRowActions(): array
{
    return [
        RowAction::make('Duplicate', 'fas fa-copy', 'route', 'products.duplicate')
            ->method('POST')
            ->confirm('Are you sure you want to duplicate this product?'),
    ];
}
```

### defineBulkActions()

Add actions for multiple selected records:

```php
public function defineBulkActions(): array
{
    return [
        BulkAction::make('Publish Selected', 'products.bulk-publish')
            ->method('POST')
            ->permission('product.publish'),
    ];
}
```

### defineWidgets()

Add widgets to the resource index page:

```php
public function defineWidgets(): array
{
    return [
        ProductStatsWidget::make()
            ->colSpan(6),
    ];
}
```

### searchResultConfig()

Configure how this resource appears in global search:

```php
public function searchResultConfig(): array
{
    return [
        'label' => 'name',  // Field(s) for the result label
        'columns' => ['name', 'sku', 'price'],  // Columns to display
    ];
}
```

<a name="query"></a>
## Query Customization

Override the base query for all resource operations:

```php
public static function query(): BuildoraQueryBuilder
{
    return parent::query()
        ->where('active', true)
        ->orderBy('created_at', 'desc');
}
```

<a name="example"></a>
## Full Example

```php
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Fields\Types\NumberField;
use Ginkelsoft\Buildora\Fields\Types\SelectField;
use Ginkelsoft\Buildora\Fields\Types\BooleanField;
use Ginkelsoft\Buildora\Fields\Types\FileField;
use Ginkelsoft\Buildora\Fields\Types\BelongsToField;
use Ginkelsoft\Buildora\Layouts\Panel;
use App\Models\Product;

class ProductBuildora extends BuildoraResource
{
    protected string $modelClass = Product::class;

    public function title(): string
    {
        return 'Products';
    }

    public function defineFields(): array
    {
        return [
            TextField::make('name', 'Product Name')
                ->searchable()
                ->sortable()
                ->validation(['required', 'max:255'])
                ->columnSpan(6),

            TextField::make('sku', 'SKU')
                ->searchable()
                ->columnSpan(6),

            BelongsToField::make('category')
                ->relatedTo(\App\Models\Category::class)
                ->pluck('id', 'name')
                ->columnSpan(6),

            NumberField::make('price', 'Price')
                ->sortable()
                ->validation(['required', 'numeric', 'min:0'])
                ->columnSpan(6),

            SelectField::make('status', 'Status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ])
                ->columnSpan(6),

            BooleanField::make('featured', 'Featured Product')
                ->columnSpan(6),

            FileField::make('image', 'Product Image')
                ->disk('public')
                ->path('products')
                ->columnSpan(12),
        ];
    }

    public function definePanels(): array
    {
        return [
            Panel::relation('variants', ProductVariantBuildora::class)
                ->label('Variants')
                ->inlineEditing(),
        ];
    }

    public function searchResultConfig(): array
    {
        return [
            'label' => 'name',
            'columns' => ['sku', 'price'],
        ];
    }
}
```
