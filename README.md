# Buildora

Buildora is a Laravel package for building admin panels, resources, forms, datatables, widgets and actions — fully based on Eloquent models and a minimal amount of configuration.

---

## 1. Requirements

- Laravel 10, 11 or 12
- PHP 8.2+
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
        Panel::make('orders')
            ->label('Recent Orders')
            ->resource(OrderBuildora::class)
            ->relation('orders'),
    ];
}
```

This will show a datatable of related `orders` on the detail page.

---

```php
```



```php
```

## 10. Theme customization

Buildora uses CSS variables for theming. By default, it includes a clean base theme with support for light and dark mode.  
If you want to customize the theme colors, **you can override the default theme**.

### Overriding the default theme

To override the default colors, first publish the theme file:

```bash
php artisan vendor:publish --tag=buildora-theme
