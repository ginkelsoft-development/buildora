# Theming

---

- [Publishing the Theme](#publishing)
- [CSS Variables](#variables)
- [Dark Mode](#dark-mode)
- [Theme Presets](#presets)

<a name="publishing"></a>
## Publishing the Theme

```bash
php artisan vendor:publish --tag=buildora-theme
```

This creates `resources/buildora/buildora-theme.css` in your Laravel application.

<a name="variables"></a>
## CSS Variables

### Colors

```css
:root {
    /* Primary/Accent Colors */
    --accent-color: #667eea;
    --accent-hover: #5a6fd6;

    /* Background Colors */
    --bg-primary: #f8fafc;
    --bg-secondary: #ffffff;
    --bg-card: #ffffff;
    --bg-dropdown: #ffffff;
    --bg-input: #ffffff;
    --bg-sidebar: #1e293b;

    /* Text Colors */
    --text-primary: #1e293b;
    --text-secondary: #475569;
    --text-muted: #94a3b8;
    --text-sidebar: #e2e8f0;

    /* Border Colors */
    --border-color: #e2e8f0;

    /* Status Colors */
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
}
```

<a name="dark-mode"></a>
## Dark Mode

Dark mode is automatically applied based on the `dark` class on the HTML element:

```css
.dark {
    --bg-primary: #0f172a;
    --bg-secondary: #1e293b;
    --bg-card: #1e293b;
    --bg-dropdown: #334155;
    --bg-input: #334155;

    --text-primary: #f1f5f9;
    --text-secondary: #cbd5e1;
    --text-muted: #64748b;

    --border-color: #334155;
}
```

Buildora includes a theme switcher with three options:
- **Light** - Force light mode
- **Dark** - Force dark mode
- **System** - Follow OS preference

<a name="presets"></a>
## Theme Presets

### Blue Theme (Default)

```css
:root {
    --accent-color: #667eea;
    --accent-hover: #5a6fd6;
}
```

### Green Theme

```css
:root {
    --accent-color: #10b981;
    --accent-hover: #059669;
}
```

### Purple Theme

```css
:root {
    --accent-color: #8b5cf6;
    --accent-hover: #7c3aed;
}
```

### Orange Theme

```css
:root {
    --accent-color: #f97316;
    --accent-hover: #ea580c;
}
```

## Custom Example

Edit `resources/buildora/buildora-theme.css`:

```css
:root {
    /* Custom brand color */
    --accent-color: #0ea5e9;
    --accent-hover: #0284c7;

    /* Custom sidebar */
    --bg-sidebar: #0c4a6e;
    --text-sidebar: #e0f2fe;
}

.dark {
    --accent-color: #38bdf8;
}
```

## Using Custom Fonts

```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

:root {
    --font-family: 'Inter', sans-serif;
}

body {
    font-family: var(--font-family);
}
```

> {primary} Using CSS variables ensures consistent styling across light and dark modes.
