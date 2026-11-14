const preview = document.querySelector('#appearance-preview');

if (preview && window.appearance) {
    preview.value = window.appearance.getPreference();
    preview.addEventListener('change', () => window.appearance.setPreference(preview.value));
    preview.closest('[hidden]').hidden = false;
}
