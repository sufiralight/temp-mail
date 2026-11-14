export function togglePassword(button, input) {
    if (!input || input.disabled || input.readOnly || button.disabled) return;

    const visible = input.type === 'password';
    const { selectionStart, selectionEnd, selectionDirection } = input;
    input.type = visible ? 'text' : 'password';

    if (selectionStart !== null && selectionEnd !== null) {
        input.setSelectionRange(selectionStart, selectionEnd, selectionDirection);
    }

    button.setAttribute('aria-pressed', String(visible));
}

export function connectDialog(dialog) {
    let opener;
    let pressedBackdrop = false;
    const onBackdrop = (event) => {
        if (event.target !== dialog) return false;
        const rect = dialog.getBoundingClientRect();
        return event.clientX < rect.left || event.clientX >= rect.right
            || event.clientY < rect.top || event.clientY >= rect.bottom;
    };

    dialog.addEventListener('pointerdown', (event) => {
        pressedBackdrop = onBackdrop(event);
    });

    dialog.addEventListener('click', (event) => {
        if (event.target.closest('[data-modal-close]')
            || (dialog.hasAttribute('data-dismiss-backdrop') && pressedBackdrop && onBackdrop(event))) {
            dialog.close();
        }
        pressedBackdrop = false;
    });

    dialog.addEventListener('close', () => {
        if (!dialog.open && opener?.isConnected) opener.focus();
    });

    return (trigger) => {
        if (dialog.open) return;
        opener = trigger;
        dialog.showModal();
    };
}

export function initializeComponents(root = document) {
    for (const button of root.querySelectorAll('[data-password-toggle]')) {
        const input = root.getElementById(button.getAttribute('aria-controls'));
        if (!input) continue;
        button.hidden = false;
        button.addEventListener('click', () => togglePassword(button, input));
    }

    const dialogs = new Map();
    for (const dialog of root.querySelectorAll('dialog[data-ui-modal]')) {
        if (typeof dialog.showModal === 'function') dialogs.set(dialog.id, connectDialog(dialog));
    }

    for (const trigger of root.querySelectorAll('[data-modal-open]')) {
        const open = dialogs.get(trigger.dataset.modalOpen);
        if (!open) continue;
        trigger.disabled = false;
        trigger.addEventListener('click', () => open(trigger));
    }
}
