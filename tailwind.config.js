// tailwind.config.js
module.exports = {
    content: [
        './resources/**/*.{blade.php,js,vue}',           // je app views/components
        './app/**/*.php',                                // als je klassen in PHP samenstelt
        './vendor/ginkelsoft/buildora/resources/**/*.{blade.php,js,vue}', // Buildora componenten
    ],
    
    // Voeg een safelist toe voor alle col-span varianten en bestaande kleurklassen
    safelist: [
        'bg-base',
        'text-foreground',
        'bg-primary',
        'text-primary-foreground',
        'bg-muted',
        'text-muted-foreground',
        {
            pattern: /(sm:|md:|lg:|xl:|2xl:)?col-span-(1|2|3|4|5|6|7|8|9|10|11|12)/,
        },
    ],

    darkMode: 'class',

    theme: {
        extend: {
            colors: {
                base: 'rgb(var(--color-base) / <alpha-value>)',
                background: 'rgb(var(--color-background) / <alpha-value>)',
                foreground: 'rgb(var(--color-foreground) / <alpha-value>)',
                muted: 'rgb(var(--color-muted) / <alpha-value>)',
                border: 'rgb(var(--color-border) / <alpha-value>)',
                input: 'rgb(var(--color-input) / <alpha-value>)',
                ring: 'rgb(var(--color-ring) / <alpha-value>)',
                primary: {
                    DEFAULT: 'rgb(var(--color-primary) / <alpha-value>)',
                    foreground: 'rgb(var(--color-primary-foreground) / <alpha-value>)',
                },
                secondary: {
                    DEFAULT: 'rgb(var(--color-secondary) / <alpha-value>)',
                    foreground: 'rgb(var(--color-secondary-foreground) / <alpha-value>)',
                },
                destructive: {
                    DEFAULT: 'rgb(var(--color-destructive) / <alpha-value>)',
                    foreground: 'rgb(var(--color-destructive-foreground) / <alpha-value>)',
                },
            },
            borderRadius: {
                sm: 'var(--radius-sm)',
                DEFAULT: 'var(--radius)',
                lg: 'var(--radius-lg)',
            },
            spacing: {
                0: '0px',
                px: '1px',
                1: 'var(--space-1)',
                2: 'var(--space-2)',
                3: 'var(--space-3)',
                4: 'var(--space-4)',
                6: 'var(--space-6)',
                8: 'var(--space-8)',
                12: 'var(--space-12)',
            },
            fontFamily: {
                sans: ['var(--font-sans)', 'ui-sans-serif', 'system-ui'],
                mono: ['var(--font-mono)', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
            },
            fontSize: {
                sm: 'var(--text-sm)',
                base: 'var(--text-base)',
                lg: 'var(--text-lg)',
                xl: 'var(--text-xl)',
                '2xl': 'var(--text-2xl)',
            },
            boxShadow: {
                sm: 'var(--shadow-sm)',
                DEFAULT: 'var(--shadow)',
                md: 'var(--shadow-md)',
                lg: 'var(--shadow-lg)',
            },
            zIndex: {
                0: 'var(--z-index-0)',
                10: 'var(--z-index-10)',
                50: 'var(--z-index-50)',
                auto: 'var(--z-index-auto)',
            },
        },
    },
};
