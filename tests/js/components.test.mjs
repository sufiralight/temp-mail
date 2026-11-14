import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import { connectDialog, togglePassword } from '../../resources/js/components.js';

test('password visibility keeps the input, value and selection and respects readonly', () => {
    const button = {
        setAttribute(name, value) {
            assert.equal(name, 'aria-pressed');
            this.pressed = value;
        },
    };
    const input = {
        type: 'password', value: 'example', selectionStart: 2, selectionEnd: 4,
        selectionDirection: 'backward',
        setSelectionRange(...args) { this.selection = args; },
    };

    togglePassword(button, input);
    assert.equal(input.type, 'text');
    assert.equal(input.value, 'example');
    assert.equal(button.pressed, 'true');
    assert.deepEqual(input.selection, [2, 4, 'backward']);
    togglePassword(button, input);
    assert.equal(input.type, 'password');
    assert.equal(button.pressed, 'false');
    input.readOnly = true;
    togglePassword(button, input);
    assert.equal(input.type, 'password');
});

test('dialog uses showModal and restores focus on native close', () => {
    const listeners = {};
    const dialog = {
        open: false,
        addEventListener(name, callback) { listeners[name] = callback; },
        showModal() { this.open = true; },
        close() { this.open = false; listeners.close(); },
        hasAttribute() { return true; },
        getBoundingClientRect() { return { left: 10, right: 90, top: 10, bottom: 90 }; },
        closest() { return null; },
    };
    const trigger = { isConnected: true, focus() { this.focused = true; } };

    const open = connectDialog(dialog);
    open(trigger);
    assert.equal(dialog.open, true);
    listeners.pointerdown({ target: dialog, clientX: 20, clientY: 20 });
    listeners.click({ target: dialog, clientX: 0, clientY: 0 });
    assert.equal(dialog.open, true);
    const backdrop = { target: dialog, clientX: 0, clientY: 0 };
    listeners.pointerdown(backdrop);
    listeners.click(backdrop);
    assert.equal(dialog.open, false);
    assert.equal(trigger.focused, true);

    open(trigger);
    trigger.focused = false;
    listeners.click({ target: { closest: () => true } });
    assert.equal(dialog.open, false);
    assert.equal(trigger.focused, true);
});

test('the spinner opts into motion only when the user permits it', () => {
    const css = readFileSync(new URL('../../resources/css/components.css', import.meta.url), 'utf8');
    assert.equal([...css.matchAll(/animation:/g)].length, 1);
    assert.match(css, /@media \(prefers-reduced-motion: no-preference\)\s*{\s*\.ui-spinner\s*{\s*animation:/);
});
