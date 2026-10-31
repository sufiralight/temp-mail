(() => {
    const root = document.documentElement;
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const key = 'temp-mail.theme';
    const valid = value => ['light', 'dark', 'system'].includes(value) ? value : 'system';
    let preference = 'system';
    try { preference = valid(localStorage.getItem(key)); } catch { /* Device fallback. */ }
    const apply = () => {
        root.dataset.theme = preference === 'system' ? (media.matches ? 'dark' : 'light') : preference;
        root.dataset.preference = preference;
        document.querySelectorAll('[data-theme-choice]').forEach(input => { input.checked = input.value === preference; });
    };
    window.TempMailTheme = {
        select(value) {
            preference = valid(value);
            try { localStorage.setItem(key, preference); } catch { /* In-memory choice remains usable. */ }
            apply();
        },
    };
    media.addEventListener('change', apply);
    window.addEventListener('storage', event => {
        if (event.key === key || event.key === null) { preference = valid(event.newValue); apply(); }
    });
    document.addEventListener('DOMContentLoaded', () => {
        apply();
        document.querySelectorAll('[data-theme-choice]').forEach(input => input.addEventListener('change', () => window.TempMailTheme.select(input.value)));
        document.querySelectorAll('[data-tooltip]').forEach(control => {
            control.addEventListener('keydown', event => {
                if (event.key === 'Escape') control.dataset.dismissed = 'true';
            });
            control.addEventListener('focusout', () => { delete control.dataset.dismissed; });
            control.addEventListener('pointerleave', () => { delete control.dataset.dismissed; });
        });
    });
    apply();
    root.classList.add('js');
})();
