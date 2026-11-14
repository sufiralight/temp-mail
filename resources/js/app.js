import { initializeComponents } from './components';

initializeComponents();

for (const preview of document.querySelectorAll('[data-appearance]')) {
    if (!window.appearance) break;
    preview.value = window.appearance.getPreference();
    preview.addEventListener('change', () => window.appearance.setPreference(preview.value));
    preview.closest('[hidden]').hidden = false;
}

for (const form of document.querySelectorAll('[data-install-form]')) {
    form.addEventListener('submit', (event) => {
        if (form.getAttribute('aria-busy') === 'true') {
            event.preventDefault();
            return;
        }

        form.setAttribute('aria-busy', 'true');
        if (event.submitter) {
            event.submitter.setAttribute('aria-disabled', 'true');
            event.submitter.querySelector('[data-install-spinner]')?.removeAttribute('hidden');
        }
    });

    window.addEventListener('pageshow', () => {
        form.removeAttribute('aria-busy');
        form.querySelector('[aria-disabled]')?.removeAttribute('aria-disabled');
        form.querySelector('[data-install-spinner]')?.setAttribute('hidden', '');
    });
}
