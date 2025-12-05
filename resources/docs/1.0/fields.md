# Fields

---

- [Introduction](#introduction)
- [Common Methods](#common-methods)
- [Text Input Fields](#text-fields)
- [Date & Time Fields](#date-fields)
- [Selection Fields](#selection-fields)
- [Relationship Fields](#relationship-fields)
- [Content Fields](#content-fields)
- [File Upload Field](#file-field)

<a name="introduction"></a>
## Introduction

Fields are the building blocks that define how model attributes are displayed and edited. Buildora provides 20+ field types out of the box.

All fields follow a fluent API pattern:

```php
TextField::make('name', 'Display Label')
    ->searchable()
    ->sortable()
    ->validation(['required', 'max:255'])
    ->columnSpan(6);
```

<a name="common-methods"></a>
## Common Methods

These methods are available on all field types:

### Display

```php
->label('Custom Label')     // Override the display label
->help('Help text here')    // Show help text below the field
->readonly()                // Make the field read-only
->readonly(fn($model) => $model->is_locked)  // Conditional readonly
```

### Table & Search

```php
->sortable()                // Enable sorting in datatable
->searchable()              // Enable searching on this field
->searchable('custom_column')  // Search on a different column
->notSearchable()           // Explicitly disable search
```

### Visibility

Control where the field appears:

```php
->hideFromTable()           // Hide from index/list view
->hideFromCreate()          // Hide from create form
->hideFromEdit()            // Hide from edit form
->hideFromDetail()          // Hide from detail/show page
->hideFromExport()          // Hide from exports
->hideOnForms()             // Hide from both create and edit
```

### Layout

Control form layout using a 12-column grid:

```php
->columnSpan(6)             // 50% width (6 of 12 columns)
->columnSpan(12)            // Full width
->columnSpan(4)             // 33% width

// Responsive column spans
->columnSpan([
    'default' => 12,        // Full width on mobile
    'md' => 6,              // 50% on medium screens
    'lg' => 4,              // 33% on large screens
])

->startNewRow()             // Force this field to start a new row
```

---

<a name="text-fields"></a>
## Text Input Fields

### TextField

Basic text input for strings.

```php
use Ginkelsoft\Buildora\Fields\Types\TextField;

TextField::make('name', 'Full Name')
    ->searchable()
    ->sortable()
    ->validation(['required', 'max:255']);
```

### EmailField

Email input with automatic validation.

```php
use Ginkelsoft\Buildora\Fields\Types\EmailField;

EmailField::make('email', 'Email Address')
    ->searchable()
    ->sortable();
```

### PasswordField

Password input with automatic hashing.

```php
use Ginkelsoft\Buildora\Fields\Types\PasswordField;

PasswordField::make('password', 'Password')
    ->hideFromTable()
    ->help('Leave empty to keep current password');
```

> {info} Empty passwords are ignored on update, and values are automatically hashed with `bcrypt()`.

### NumberField

Numeric input.

```php
use Ginkelsoft\Buildora\Fields\Types\NumberField;

NumberField::make('quantity', 'Quantity')
    ->sortable()
    ->validation(['required', 'integer', 'min:0']);
```

### CurrencyField

Formatted currency display.

```php
use Ginkelsoft\Buildora\Fields\Types\CurrencyField;

CurrencyField::make('price', 'Price')
    ->sortable();
```

---

<a name="date-fields"></a>
## Date & Time Fields

### DateField

Date input with customizable format.

```php
use Ginkelsoft\Buildora\Fields\Types\DateField;

DateField::make('birth_date', 'Birth Date')
    ->format('d-m-Y')       // Display format
    ->sortable();
```

### DateTimeField

Date and time input.

```php
use Ginkelsoft\Buildora\Fields\Types\DateTimeField;

DateTimeField::make('published_at', 'Published At')
    ->format('d-m-Y H:i')
    ->sortable();
```

---

<a name="selection-fields"></a>
## Selection Fields

### BooleanField

Toggle/checkbox for boolean values.

```php
use Ginkelsoft\Buildora\Fields\Types\BooleanField;

BooleanField::make('is_active', 'Active')
    ->options([
        true => 'Enabled',
        false => 'Disabled',
    ]);
```

### SelectField

Dropdown select with static or dynamic options.

```php
use Ginkelsoft\Buildora\Fields\Types\SelectField;

// Static options
SelectField::make('status', 'Status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ]);

// Dynamic options via closure
SelectField::make('category_id', 'Category')
    ->options(fn() => Category::pluck('name', 'id')->toArray());

// From PHP Enum
SelectField::make('status', 'Status')
    ->options(OrderStatus::class);
```

### CheckboxListField

Multiple checkbox selection (extends BelongsToManyField).

```php
use Ginkelsoft\Buildora\Fields\Types\CheckboxListField;

CheckboxListField::make('permissions', 'Permissions')
    ->groupByPrefix(true, '.')  // Group by dot prefix
    ->hideFromTable();
```

---

<a name="relationship-fields"></a>
## Relationship Fields

### BelongsToField

Select for BelongsTo relationships.

```php
use Ginkelsoft\Buildora\Fields\Types\BelongsToField;

BelongsToField::make('category')
    ->relatedTo(\App\Models\Category::class)
    ->pluck('id', 'name')       // (valueColumn, displayColumn)
    ->searchable();
```

### AsyncBelongsToField

AJAX-powered select for large datasets.

```php
use Ginkelsoft\Buildora\Fields\Types\AsyncBelongsToField;

AsyncBelongsToField::make('customer')
    ->relatedTo(\App\Models\Customer::class)
    ->pluck('id', 'name')
    ->searchColumns(['name', 'email', 'phone'])  // Columns to search
    ->displayUsing('name');
```

### BelongsToManyField

Multi-select for many-to-many relationships.

```php
use Ginkelsoft\Buildora\Fields\Types\BelongsToManyField;

BelongsToManyField::make('tags')
    ->pluck('id', 'name')
    ->hideFromTable();
```

### RelationLinkField

Displays a clickable link to a related record.

```php
use Ginkelsoft\Buildora\Fields\Types\RelationLinkField;

// Basic usage - link to related record
RelationLinkField::make('category', 'Category')
    ->displayUsing('name');

// With explicit model and resource
RelationLinkField::make('author', 'Author')
    ->relatedTo(\App\Models\User::class)
    ->resource(\App\Buildora\Resources\UserBuildora::class)
    ->displayUsing('name');

// Open in new tab
RelationLinkField::make('company')
    ->displayUsing('company_name')
    ->openInNewTab();
```

**Methods:**
- `relatedTo(string $model)` - Set the related model class
- `resource(string $resourceClass)` - Set the Buildora resource for URL generation
- `displayUsing(string $column)` - Column to display as link text (default: 'name')
- `openInNewTab(bool $value)` - Open the link in a new browser tab

> {info} RelationLinkField automatically hides from create/edit forms. It's designed for table and detail views only.

---

<a name="content-fields"></a>
## Content Fields

### EditorField

WYSIWYG editor for editing rich content.

```php
use Ginkelsoft\Buildora\Fields\Types\EditorField;

EditorField::make('body', 'Article Body')
    ->hideFromTable()
    ->columnSpan(12);
```

### JsonField

JSON editor for structured data.

```php
use Ginkelsoft\Buildora\Fields\Types\JsonField;

JsonField::make('metadata', 'Metadata')
    ->hideFromTable()
    ->hideFromExport();
```

### DisplayField

Custom computed content.

```php
use Ginkelsoft\Buildora\Fields\Types\DisplayField;

DisplayField::make('full_address', 'Address')
    ->content(fn($model) => "{$model->street}, {$model->city}");
```

### ViewField

Render a custom Blade view.

```php
use Ginkelsoft\Buildora\Fields\Types\ViewField;

ViewField::make('custom_display')
    ->view('components.order-summary')
    ->closure(fn($model) => [
        'total' => $model->calculateTotal(),
        'items' => $model->items,
    ]);
```

---

<a name="file-field"></a>
## File Upload Field

### FileField

File upload with preview support.

```php
use Ginkelsoft\Buildora\Fields\Types\FileField;

FileField::make('document', 'Document')
    ->disk('public')                    // Storage disk
    ->path('documents')                 // Upload path
    ->accept('.pdf,.doc,.docx')         // Allowed extensions
    ->maxSize(2048)                     // Max size in KB
    ->showPreview(true);                // Show inline preview

// For images with dimension limits
FileField::make('avatar', 'Profile Picture')
    ->disk('public')
    ->path('avatars')
    ->accept('.jpg,.jpeg,.png,.webp')
    ->imageDimensions(800, 800)         // Max width, height
    ->showPreview(true);
```
