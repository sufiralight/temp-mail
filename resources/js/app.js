import { initializeComponents } from './components';

initializeComponents();

for (const preview of document.querySelectorAll('[data-appearance]')) {
    if (!window.appearance) break;
    preview.value = window.appearance.getPreference();
    preview.addEventListener('change', () => window.appearance.setPreference(preview.value));
    preview.closest('[hidden]').hidden = false;
}
