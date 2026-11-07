(() => {
    const root = document.documentElement;
    let preference = 'light';

    try {
        if (localStorage.getItem('temp-mail-theme') === 'dark') {
            preference = 'dark';
        }
    } catch {
        // The light theme remains available when storage is blocked.
    }

    const applyTheme = () => {
        const dark = preference === 'dark';
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

        for (const button of document.querySelectorAll('[data-password-toggle]')) {
            const input = document.getElementById(button.getAttribute('aria-controls'));
            const status = button.nextElementSibling;
            const label = button.dataset.passwordToggle;
            const showIcon = button.querySelector('[data-password-show]');
            const hideIcon = button.querySelector('[data-password-hide]');

            button.hidden = false;
            button.addEventListener('click', () => {
                const reveal = input.type === 'password';
                const action = (reveal ? 'Hide ' : 'Show ') + label;

                input.type = reveal ? 'text' : 'password';
                showIcon.toggleAttribute('hidden', reveal);
                hideIcon.toggleAttribute('hidden', !reveal);
                button.setAttribute('aria-label', action);
                button.title = action;
                status.textContent = `${label.charAt(0).toUpperCase()}${label.slice(1)} ${reveal ? 'visible' : 'hidden'}.`;
            });
        }

        const loading = document.querySelector('[data-installer-loading]');
        const status = document.querySelector('[data-installer-status]');
        for (const form of document.querySelectorAll('form[data-busy-label]')) {
            const button = form.querySelector('[type="submit"]');
            const label = button.querySelector('[data-submit-label]');
            const original = label.textContent;

            form.addEventListener('submit', () => {
                button.disabled = true;
                form.setAttribute('aria-busy', 'true');
                loading.hidden = false;
                label.textContent = form.dataset.busyLabel;
                status.textContent = form.dataset.busyLabel;
            });

            window.addEventListener('pageshow', () => {
                button.disabled = false;
                form.removeAttribute('aria-busy');
                loading.hidden = true;
                label.textContent = original;
                status.textContent = '';
            });
        }

        document.querySelector('[data-focus-on-load]')?.focus();
    });
})();
