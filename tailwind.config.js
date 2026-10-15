import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                pink: {
                    DEFAULT: '#FA0467', // brand. actions, active state, sale price
                    deep: '#C40350', // hover, pressed
                    wash: '#FFF0F6', // tint section backgrounds
                    line: '#FFCCE0', // pink hairlines
                    btn: '#C40350', // button fills. see contrast note in design spec
                },
                citrus: {
                    DEFAULT: '#FFD84D', // badges, routine step highlights. sparingly
                    deep: '#E6BC20',
                },
                ink: {
                    DEFAULT: '#14060E', // body text
                    soft: '#6B5A63', // secondary text, captions
                    faint: '#7A6875', // 4.84:1 on bone, safe for small text
                },
                bone: '#FAF7F5', // page ground
                line: '#EADFE4', // default hairline
                mint: '#0E9F6E', // in stock, success
                alert: '#D92D20', // errors only
            },
            fontFamily: {
                display: ['"Bricolage Grotesque"', 'system-ui', 'sans-serif'],
                body: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                edito: ['"Instrument Serif"', 'Georgia', 'serif'],
                mono: ['"Space Grotesk"', 'ui-monospace', 'monospace'],
            },
            fontSize: {
                xs: ['0.75rem', { lineHeight: '1.4', letterSpacing: '0.04em' }],
                sm: ['0.875rem', { lineHeight: '1.5' }],
                base: ['1rem', { lineHeight: '1.6' }],
                lg: ['1.125rem', { lineHeight: '1.5' }],
                xl: ['1.375rem', { lineHeight: '1.3' }],
                '2xl': ['1.75rem', { lineHeight: '1.2', letterSpacing: '-0.01em' }],
                '3xl': ['2.25rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
                '4xl': ['3rem', { lineHeight: '1.05', letterSpacing: '-0.03em' }],
                '5xl': ['4rem', { lineHeight: '0.95', letterSpacing: '-0.035em' }],
                '6xl': ['5.5rem', { lineHeight: '0.9', letterSpacing: '-0.04em' }],
            },
            borderRadius: {
                DEFAULT: '4px',
                card: '12px',
                pill: '999px',
            },
            boxShadow: {
                card: '0 1px 2px rgba(20,6,14,0.04), 0 8px 24px rgba(20,6,14,0.06)',
                lift: '0 4px 8px rgba(20,6,14,0.06), 0 16px 40px rgba(20,6,14,0.10)',
                drawer: '-8px 0 40px rgba(20,6,14,0.14)',
            },
            transitionTimingFunction: {
                out: 'cubic-bezier(0.22, 1, 0.36, 1)',
                in: 'cubic-bezier(0.7, 0, 0.84, 0)',
            },
            maxWidth: {
                wrap: '1320px',
                narrow: '680px',
            },
        },
    },

    plugins: [forms],
};
