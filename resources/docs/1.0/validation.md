# Validation

---

- [Basic Validation](#basic)
- [Dynamic Validation](#dynamic)
- [Common Patterns](#patterns)
- [Password Validation](#password)

<a name="basic"></a>
## Basic Validation

Use the `validation()` method with an array of Laravel validation rules:

```php
TextField::make('name', 'Name')
    ->validation(['required', 'max:255']);

EmailField::make('email', 'Email')
    ->validation(['required', 'email', 'unique:users,email']);

NumberField::make('price', 'Price')
    ->validation(['required', 'numeric', 'min:0', 'max:99999.99']);
```

<a name="dynamic"></a>
## Dynamic Validation

Use a closure for context-aware validation rules:

```php
TextField::make('name', 'Name')
    ->validation(function ($model) {
        $rules = ['required', 'max:255'];

        // Add unique rule, ignoring current record on edit
        if ($model->exists) {
            $rules[] = 'unique:products,name,' . $model->id;
        } else {
            $rules[] = 'unique:products,name';
        }

        return $rules;
    });
```

### Conditional Validation

Apply different rules based on model state:

```php
NumberField::make('discount_percentage', 'Discount %')
    ->validation(function ($model) {
        if ($model->has_discount) {
            return ['required', 'numeric', 'min:1', 'max:100'];
        }
        return ['nullable', 'numeric', 'min:0', 'max:100'];
    });
```

<a name="patterns"></a>
## Common Patterns

### Required Fields

```php
TextField::make('title', 'Title')
    ->validation(['required']);
```

### String Length

```php
TextField::make('name', 'Name')
    ->validation(['required', 'min:2', 'max:255']);
```

### Numeric Values

```php
NumberField::make('quantity', 'Quantity')
    ->validation(['required', 'integer', 'min:0']);

NumberField::make('price', 'Price')
    ->validation(['required', 'numeric', 'min:0', 'max:99999.99']);
```

### Unique Values

```php
// Simple unique
TextField::make('slug', 'Slug')
    ->validation(['required', 'unique:products,slug']);

// Unique with exception for current record
TextField::make('email', 'Email')
    ->validation(fn($model) => [
        'required',
        'email',
        $model->exists
            ? 'unique:users,email,' . $model->id
            : 'unique:users,email'
    ]);
```

### Date Validation

```php
DateField::make('start_date', 'Start Date')
    ->validation(['required', 'date', 'after:today']);

DateField::make('end_date', 'End Date')
    ->validation(['required', 'date', 'after:start_date']);
```

### File Validation

```php
FileField::make('document', 'Document')
    ->validation(['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120']);

FileField::make('image', 'Image')
    ->validation(['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048']);
```

### Enum/In Validation

```php
SelectField::make('status', 'Status')
    ->options(['draft', 'published', 'archived'])
    ->validation(['required', 'in:draft,published,archived']);
```

### Relationship Validation

```php
BelongsToField::make('category')
    ->validation(['required', 'exists:categories,id']);
```

<a name="password"></a>
## Password Validation

Buildora handles passwords specially:

```php
PasswordField::make('password', 'Password')
    ->validation(fn($model) => $model->exists
        ? ['nullable', 'min:8', 'confirmed']  // Optional on edit
        : ['required', 'min:8', 'confirmed']   // Required on create
    )
    ->help('Leave empty to keep current password');
```

> {info} Empty password fields are automatically ignored during updates.
