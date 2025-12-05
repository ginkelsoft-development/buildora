# Widgets

---

- [Introduction](#introduction)
- [Creating a Widget](#creating)
- [Widget Configuration](#configuration)
- [Using Widgets](#using)
- [Examples](#examples)

<a name="introduction"></a>
## Introduction

Widgets are reusable components that display information on dashboards and resource pages. They're perfect for statistics, charts, quick actions, and custom content.

<a name="creating"></a>
## Creating a Widget

Use the Artisan command to generate a widget:

```bash
php artisan buildora:widget
```

This interactive command will:
1. Ask for the widget name
2. Generate the widget class
3. Create the Blade view

### Manual Creation

Create a widget class in `app/Buildora/Widgets/`:

```php
<?php

namespace App\Buildora\Widgets;

use Ginkelsoft\Buildora\Widgets\BuildoraWidget;
use Illuminate\View\View;

class SalesOverviewWidget extends BuildoraWidget
{
    public function render(): View
    {
        $totalSales = Order::sum('total');
        $orderCount = Order::count();

        return view('buildora.widgets.sales-overview', [
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
        ]);
    }
}
```

Create the Blade view at `resources/views/buildora/widgets/sales-overview.blade.php`:

```html
<div class="p-6 rounded-xl" style="background: var(--bg-card); border: 1px solid var(--border-color);">
    <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
        Sales Overview
    </h3>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm" style="color: var(--text-muted);">Total Sales</p>
            <p class="text-2xl font-bold" style="color: var(--text-primary);">
                € {{ number_format($totalSales, 2, ',', '.') }}
            </p>
        </div>
        <div>
            <p class="text-sm" style="color: var(--text-muted);">Orders</p>
            <p class="text-2xl font-bold" style="color: var(--text-primary);">
                {{ $orderCount }}
            </p>
        </div>
    </div>
</div>
```

<a name="configuration"></a>
## Widget Configuration

### Column Span

Control the widget width using a 12-column grid:

```php
SalesOverviewWidget::make()
    ->colSpan(6)  // 50% width
```

Responsive column spans:

```php
->colSpan([
    'default' => 12,  // Full width on mobile
    'md' => 6,        // 50% on medium screens
    'lg' => 4,        // 33% on large screens
])
```

### Accessing Resource & Model

Widgets can access the current resource and model:

```php
public function render(): View
{
    $resource = $this->getResource();
    $model = $this->getModel();

    return view('buildora.widgets.custom', [
        'resourceTitle' => $resource?->title(),
        'modelId' => $model?->id,
    ]);
}
```

### Page Visibility

Control which pages the widget appears on:

```php
public function pageVisibility(): array
{
    return ['index'];  // Default: only on index page
}

// Show on multiple pages
public function pageVisibility(): array
{
    return ['index', 'detail', 'create', 'edit'];
}
```

<a name="using"></a>
## Using Widgets

### In Resources

Add widgets to a resource's index page:

```php
public function defineWidgets(): array
{
    return [
        SalesOverviewWidget::make()
            ->colSpan(12),

        NewOrdersWidget::make()
            ->colSpan(6),

        TopProductsWidget::make()
            ->colSpan(6),
    ];
}
```

### On Dashboard

Configure dashboard widgets in `config/buildora.php`:

```php
'dashboards' => [
    'enabled' => true,
    'widgets' => [
        \App\Buildora\Widgets\TotalUsersWidget::class,
        \App\Buildora\Widgets\RevenueWidget::class,
    ],
],
```

<a name="examples"></a>
## Examples

### Statistics Card

```php
class TotalUsersWidget extends BuildoraWidget
{
    public function render(): View
    {
        return view('buildora.widgets.stat-card', [
            'title' => 'Total Users',
            'value' => User::count(),
            'icon' => 'fas fa-users',
            'color' => '#667eea',
        ]);
    }
}
```

```html
{{-- stat-card.blade.php --}}
<div class="p-6 rounded-xl" style="background: var(--bg-card); border: 1px solid var(--border-color);">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium" style="color: var(--text-muted);">{{ $title }}</p>
            <p class="text-3xl font-bold mt-1" style="color: var(--text-primary);">
                {{ number_format($value) }}
            </p>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center"
             style="background: {{ $color }}20;">
            <i class="{{ $icon }} text-xl" style="color: {{ $color }};"></i>
        </div>
    </div>
</div>
```

### Recent Records Widget

```php
class RecentOrdersWidget extends BuildoraWidget
{
    public function render(): View
    {
        $orders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('buildora.widgets.recent-orders', [
            'orders' => $orders,
        ]);
    }
}
```

## Styling Guidelines

Use CSS variables for consistent theming:

```css
var(--bg-card)           /* Card background */
var(--border-color)      /* Border color */
var(--text-primary)      /* Primary text */
var(--text-muted)        /* Muted/helper text */
var(--accent-color)      /* Accent/link color */
```

> {primary} Using CSS variables ensures your widgets look correct in both light and dark modes.
