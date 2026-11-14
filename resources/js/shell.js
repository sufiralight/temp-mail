export function initializeShell(root = document) {
    const theme = root.querySelector('[data-shell-theme]');
    if (theme && window.appearance) {
        const choices = [...theme.querySelectorAll('[data-theme-choice]')];
        const update = () => {
            for (const choice of choices) {
                choice.setAttribute('aria-pressed', String(choice.dataset.themeChoice === window.appearance.getPreference()));
            }
        };
        for (const choice of choices) {
            choice.addEventListener('click', () => {
                window.appearance.setPreference(choice.dataset.themeChoice);
                update();
            });
        }
        update();
        theme.hidden = false;
    }

    const menu = root.querySelector('[data-account-menu]');
    if (menu) {
        menu.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape' || !menu.open) return;
            event.preventDefault();
            menu.open = false;
            menu.querySelector('summary').focus();
        });
        root.addEventListener('click', (event) => {
            if (menu.open && !menu.contains(event.target)) menu.open = false;
        });
    }

    const drawer = root.querySelector('.shell-drawer');
    if (drawer) {
        for (const link of drawer.querySelectorAll('nav a')) {
            link.addEventListener('click', () => drawer.close());
        }
        window.matchMedia('(min-width: 64rem)').addEventListener('change', ({ matches }) => {
            if (matches && drawer.open) drawer.close();
        });
    }
}
