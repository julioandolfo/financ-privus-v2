import './bootstrap';
import Alpine from 'alpinejs';

// Theme management
const theme = {
    init() {
        const saved = localStorage.getItem('theme') ?? 'system';
        this.apply(saved);
    },
    apply(value) {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = value === 'dark' || (value === 'system' && prefersDark);
        document.documentElement.classList.toggle('dark', isDark);
        localStorage.setItem('theme', value);
    },
};

theme.init();

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (localStorage.getItem('theme') === 'system') theme.apply('system');
});

// Alpine.js stores
document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        current: localStorage.getItem('theme') ?? 'system',
        set(value) {
            this.current = value;
            theme.apply(value);
        },
    });

    Alpine.store('sidebar', {
        open: window.innerWidth >= 1024,
        toggle() { this.open = !this.open; },
    });
});

window.Alpine = Alpine;
Alpine.start();
