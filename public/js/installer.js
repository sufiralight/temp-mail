(() => {
    const root = document.documentElement;
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
    let preference;

    try {
        preference = localStorage.getItem('temp-mail-theme');
    } catch {
        // System appearance still works when storage is unavailable.
    }

    if (preference !== 'light' && preference !== 'dark') {
        preference = null;
    }

    const applyTheme = () => {
        const dark = (preference ?? (systemTheme.matches ? 'dark' : 'light')) === 'dark';
        root.dataset.theme = dark ? 'dark' : 'light';

        const toggle = document.querySelector('[data-theme-toggle]');
        if (toggle) {
            toggle.setAttribute('aria-pressed', String(dark));
            toggle.querySelector('[data-theme-light]').toggleAttribute('hidden', dark);
            toggle.querySelector('[data-theme-dark]').toggleAttribute('hidden', !dark);
        }
    };

    applyTheme();

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.querySelector('[data-theme-toggle]');
        applyTheme();

        if (toggle) {
            toggle.hidden = false;
            toggle.addEventListener('click', () => {
                preference = root.dataset.theme === 'dark' ? 'light' : 'dark';
                applyTheme();

                try {
                    localStorage.setItem('temp-mail-theme', preference);
                } catch {
                    // The selected appearance remains active for this page.
                }
            });
        }

        systemTheme.addEventListener('change', applyTheme);

        for (const button of document.querySelectorAll('[data-password-toggle]')) {
            const input = document.getElementById(button.getAttribute('aria-controls'));
            const status = button.nextElementSibling;
            const label = button.dataset.passwordToggle;

            button.hidden = false;
            button.addEventListener('click', () => {
                const reveal = input.type === 'password';
                const action = reveal ? 'Hide' : 'Show';

                input.type = reveal ? 'text' : 'password';
                button.textContent = action;
                button.setAttribute('aria-label', `${action} ${label}`);
                status.textContent = reveal ? 'Password visible.' : 'Password hidden.';
            });
        }

        for (const form of document.querySelectorAll('form[data-busy-label]')) {
            const button = form.querySelector('[type="submit"]');
            const label = button.querySelector('[data-submit-label]');
            const status = form.querySelector('[data-submit-status]');
            const original = label.textContent;

            form.addEventListener('submit', () => {
                button.disabled = true;
                label.textContent = form.dataset.busyLabel;
                status.textContent = form.dataset.busyLabel;
            });

            window.addEventListener('pageshow', () => {
                button.disabled = false;
                label.textContent = original;
                status.textContent = '';
            });
        }

        document.querySelector('[data-focus-on-load]')?.focus();
    });
})();
