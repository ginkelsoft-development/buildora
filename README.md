# 📘 Buildora Resource Package

Buildora is a Laravel package that allows you to dynamically define and manage CRUD resources, datatables, actions, filters and widgets using a clean and fluent API.

---

## ✅ Requirements

- PHP 8.1 or higher
- Laravel 10 or higher
- TailwindCSS (already integrated)

---

## 📦 Installation

Install the package via Composer (if private/local, adjust path):

```bash
composer require ginkelsoft/buildora
```

Publish views and configuration:

```bash
php artisan vendor:publish --provider="Ginkelsoft\Buildora\BuildoraServiceProvider"
```

---

## 🧱 Defining a Resource

Place your resource in `app/Buildora/Resources`. Example:

```php
use Ginkelsoft\Buildora\Resources\BuildoraResource;
use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Fields\Types\BelongsToField;

class PostBuildora extends BuildoraResource
{
    protected static string $model = \App\Models\Post::class;

    public function defineFields(): array
    {
        return [
            Field::make('id', 'ID', 'number')->readonly()->hideFromTable(),
            Field::make('title', 'Title'),
            Field::make('content', 'Content', 'textarea'),
            BelongsToField::make('user')->relatedTo(\App\Models\User::class),
        ];
    }
}
```

---

## 🥉 Available Field Types

- `Field::make()` for:
    - `text`
    - `textarea`
    - `number`
    - `email`
    - `password`
    - `readonly`
    - `json`
    - `date`
    - `datetime`
    - `boolean`
- Relationship fields:
    - `BelongsToField`
    - `HasManyField`
    - `BelongsToManyField`
    - `HasOneField`

Each field supports chaining options like:

```php
->label('Custom Label')
->sortable()
->readonly()
->hideFromCreate()
->hideFromEdit()
```

---

## ⚡ Row Actions

Define row actions in your resource:

```php
use Ginkelsoft\Buildora\Actions\RowAction;

public function defineRowActions(): array
{
    return [
        RowAction::make('Edit', 'fas fa-edit', 'route', 'buildora.edit')
            ->method('GET')
            ->params(['id' => 'id']),

        RowAction::make('Delete', 'fas fa-trash', 'route', 'buildora.destroy')
            ->method('DELETE')
            ->params(['id' => 'id'])
            ->confirm('Are you sure?'),
    ];
}
```

---

## 📄 Bulk Actions

Define bulk actions with the `BulkAction` class:

```php
use Ginkelsoft\Buildora\Actions\BulkAction;

public function defineBulkActions(): array
{
    return [
        BulkAction::make('Export as Excel', 'buildora.export', ['format' => 'xlsx'])
            ->method('GET'),

        BulkAction::make('Export as CSV', 'buildora.export', ['format' => 'csv'])
            ->method('GET'),
    ];
}
```

---

## 📊 Widgets

Widgets are custom view components that can be rendered per page type (`index`, `create`, `edit`, `detail`).

Create a widget class in `app/Buildora/Widgets`:

```php
namespace App\Buildora\Widgets;

use Ginkelsoft\Buildora\Widgets\BuildoraWidget;
use Illuminate\View\View;

class StatsWidget extends BuildoraWidget
{
    public function render(): View
    {
        return view('widgets.stats');
    }

    public function pageVisibility(): array
    {
        return ['index'];
    }
}
```

---

## 🛠️ Artisan Commands

### 🔹 Create a Resource:
```bash
php artisan buildora:resource
```
You'll be asked for the model name. This will auto-generate a resource based on its fillable and relationships.

### 🔹 Create a Widget:
```bash
php artisan buildora:widget
```
You'll be asked:
- Widget class name
- View path (e.g. `widgets.stats`)

This will create the class in `app/Buildora/Widgets` and a Blade view in `resources/views/widgets`.

---

## 📌 Routing

Buildora registers a route group under `/buildora`, e.g.

- `/buildora/user`
- `/buildora/post`

You can link to these from the sidebar.

---

## 🔒 Permissions & Middleware

You can apply your own auth/role logic via middleware or extend Buildora controllers.

