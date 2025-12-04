# Panels & Relations

---

- [Introduction](#introduction)
- [Creating Panels](#creating)
- [Inline Editing](#inline-editing)
- [Supported Relationships](#relationships)
- [Full Example](#example)

<a name="introduction"></a>
## Introduction

Panels allow you to display and manage related data on the detail page of a resource. They integrate with Laravel's Eloquent relationships.

<a name="creating"></a>
## Creating Panels

Define panels in the `definePanels()` method of your resource:

```php
use Ginkelsoft\Buildora\Layouts\Panel;

public function definePanels(): array
{
    return [
        Panel::relation('orders', OrderBuildora::class)
            ->label('Customer Orders'),

        Panel::relation('addresses', AddressBuildora::class)
            ->label('Addresses'),
    ];
}
```

### Panel::relation()

The primary method to create a panel:

```php
Panel::relation(string $relationMethod, string $resourceClass): Panel
```

**Parameters:**
- `$relationMethod` - The name of the relationship method on your model
- `$resourceClass` - The Buildora resource class for the related model

### label()

Set a custom display label:

```php
Panel::relation('orders', OrderBuildora::class)
    ->label('Purchase History')
```

<a name="inline-editing"></a>
## Inline Editing

Enable inline CRUD operations without leaving the page:

```php
Panel::relation('items', OrderItemBuildora::class)
    ->label('Order Items')
    ->inlineEditing()  // Enable create, edit, and delete
```

Control which operations are allowed:

```php
// Only allow create and edit, no delete
->inlineEditing(create: true, delete: false)

// Only allow editing existing records
->inlineEditing(create: false, delete: false)

// Full control
->inlineEditing(create: true, delete: true)
```

When `inlineEditing()` is enabled, users can:

1. **Create** new related records via a modal
2. **Edit** existing records in a modal
3. **Delete** records with confirmation

<a name="relationships"></a>
## Supported Relationships

Panels work with these Eloquent relationship types:

### HasMany

```php
// Model: App\Models\Customer
public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}

// Resource: CustomerBuildora
public function definePanels(): array
{
    return [
        Panel::relation('orders', OrderBuildora::class)
            ->label('Orders'),
    ];
}
```

### BelongsToMany

```php
// Model: App\Models\Product
public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class);
}

// Resource: ProductBuildora
public function definePanels(): array
{
    return [
        Panel::relation('tags', TagBuildora::class)
            ->label('Tags')
            ->inlineEditing(),
    ];
}
```

> {info} Relations defined in `definePanels()` are automatically eager-loaded to prevent N+1 query problems.

<a name="example"></a>
## Full Example

```php
<?php

namespace App\Buildora\Resources;

use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Fields\Types\TextField;
use Ginkelsoft\Buildora\Fields\Types\EmailField;
use Ginkelsoft\Buildora\Layouts\Panel;

class CustomerBuildora extends BuildoraResource
{
    protected string $modelClass = \App\Models\Customer::class;

    public function defineFields(): array
    {
        return [
            TextField::make('name', 'Customer Name')
                ->searchable()
                ->sortable()
                ->columnSpan(6),

            EmailField::make('email', 'Email')
                ->searchable()
                ->columnSpan(6),
        ];
    }

    public function definePanels(): array
    {
        return [
            // Read-only panel for orders
            Panel::relation('orders', OrderBuildora::class)
                ->label('Order History'),

            // Inline editable addresses
            Panel::relation('addresses', AddressBuildora::class)
                ->label('Addresses')
                ->inlineEditing(create: true, delete: true),

            // Inline notes without delete option
            Panel::relation('notes', NoteBuildora::class)
                ->label('Customer Notes')
                ->inlineEditing(create: true, delete: false),
        ];
    }
}
```
