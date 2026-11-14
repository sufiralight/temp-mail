(() => {
    const key = 'temp-mail.appearance';
    const system = window.matchMedia('(prefers-color-scheme: dark)');
    const normalize = (value) => ['light', 'dark', 'system'].includes(value) ? value : 'system';

    let preference = 'system';
    try {
        preference = normalize(window.localStorage.getItem(key));
    } catch {
        // The OS preference still works when browser storage is unavailable.
    }

    const apply = () => {
        document.documentElement.dataset.theme = preference === 'system'
            ? (system.matches ? 'dark' : 'light')
            : preference;
    };

    apply();
    system.addEventListener('change', apply);

    window.appearance = Object.freeze({
        getPreference: () => preference,
        setPreference(value) {
            preference = normalize(value);
            apply();

            try {
                window.localStorage.setItem(key, preference);
            } catch {
                // Keep the current selection in memory for this page.
            }
        },
    });
})();
