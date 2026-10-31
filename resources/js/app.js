import Alpine from '@alpinejs/csp';

Alpine.data('passwordField', () => ({
    visible: false,
    toggle() {
        const input = this.$refs.password;
        const start = input.selectionStart;
        const end = input.selectionEnd;
        this.visible = !this.visible;
        input.type = this.visible ? 'text' : 'password';
        input.setSelectionRange(start, end);
    },
    get label() { return this.visible ? this.$root.dataset.hide : this.$root.dataset.show; },
}));
Alpine.data('submitState', () => ({
    busy: false,
    init() { window.addEventListener('pageshow', () => { this.busy = false; }); },
    submit(event) {
        if (this.busy) { event.preventDefault(); return; }
        this.busy = true;
    },
}));
window.Alpine = Alpine;
Alpine.start();
document.getElementById('validation-summary')?.focus();
