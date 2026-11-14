import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const read = (path) => readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');
const css = read('resources/css/app.css');
const engine = read('resources/js/appearance.js');
const palette = {
    "zinc-50": "FAFAFA",
    "zinc-100": "F4F4F5",
    "zinc-200": "E4E4E7",
    "zinc-300": "D4D4D8",
    "zinc-400": "A1A1AA",
    "zinc-500": "71717A",
    "zinc-600": "52525B",
    "zinc-700": "3F3F46",
    "zinc-800": "27272A",
    "zinc-900": "18181B",
    "zinc-950": "09090B",
    "white": "FFFFFF",
    "success": "16A34A",
    "warning": "D97706",
    "error": "DC2626",
    "info": "2563EB"
};
const mappings = {
    "canvas": "zinc-50 zinc-950",
    "surface": "white zinc-900",
    "surface-muted": "zinc-100 zinc-800",
    "foreground": "zinc-900 zinc-50",
    "foreground-muted": "zinc-600 zinc-400",
    "border": "zinc-200 zinc-700",
    "border-strong": "zinc-300 zinc-600",
    "primary": "zinc-900 zinc-50",
    "primary-foreground": "white zinc-900",
    "hover": "zinc-100 zinc-800",
    "selected": "zinc-200 zinc-700",
    "focus-ring": "zinc-700 zinc-300"
};

test('the approved palette and both semantic mappings are exact', () => {
    const actualPalette = [...css.matchAll(/--pz-([\w-]+): #([A-F0-9]+);/g)];
    assert.equal(actualPalette.length, 16);
    assert.deepEqual(Object.fromEntries(actualPalette.map(([, name, value]) => [name, value])), palette);

    const actualMappings = [...css.matchAll(/--([\w-]+): light-dark\(var\(--pz-([\w-]+)\), var\(--pz-([\w-]+)\)\);/g)];
    assert.equal(actualMappings.length, 12);
    assert.deepEqual(Object.fromEntries(actualMappings.map(([, name, light, dark]) => [name, `${light} ${dark}`])), mappings);
});

function initialize({ dark = false, storage = new Map(), blocked = false } = {}) {
    const dataset = {};
    const query = {
        matches: dark,
        addEventListener(event, listener) {
            assert.equal(event, 'change');
            this.change = listener;
        },
    };
    const window = {
        matchMedia(expression) {
            assert.equal(expression, '(prefers-color-scheme: dark)');
            return query;
        },
        get localStorage() {
            if (blocked) {
                throw new Error('Storage access denied');
            }

            return {
                getItem: (key) => storage.get(key),
                setItem: (key, value) => storage.set(key, value),
            };
        },
    };

    vm.runInNewContext(engine, { window, document: { documentElement: { dataset } } });
    return {
        appearance: window.appearance,
        theme: () => dataset.theme,
        changeSystem(matches) {
            query.matches = matches;
            query.change();
        },
    };
}

test('missing and invalid preferences follow changes to the OS theme', () => {
    for (const value of [undefined, 'invalid']) {
        const page = initialize({ dark: true, storage: new Map([['temp-mail.appearance', value]]) });
        assert.equal(page.appearance.getPreference(), 'system');
        assert.equal(page.theme(), 'dark');
        page.changeSystem(false);
        assert.equal(page.theme(), 'light');
    }
});

test('explicit preferences persist and override the OS until System is selected', () => {
    const storage = new Map();
    const page = initialize({ storage });
    page.appearance.setPreference('dark');
    assert.equal(storage.get('temp-mail.appearance'), 'dark');

    const reloaded = initialize({ storage });
    reloaded.changeSystem(false);
    assert.equal(reloaded.appearance.getPreference(), 'dark');
    assert.equal(reloaded.theme(), 'dark');
    reloaded.appearance.setPreference('system');
    assert.equal(reloaded.theme(), 'light');
    assert.equal(storage.get('temp-mail.appearance'), 'system');
});

test('blocked storage leaves appearance usable in memory', () => {
    const page = initialize({ blocked: true, dark: true });
    assert.equal(page.theme(), 'dark');
    page.appearance.setPreference('light');
    page.changeSystem(true);
    assert.equal(page.appearance.getPreference(), 'light');
    assert.equal(page.theme(), 'light');
});

test('the Vite production manifest resolves CSS, JavaScript and both local fonts', () => {
    const manifest = JSON.parse(read('public/build/manifest.json'));
    const stylesheet = read(`public/build/${manifest['resources/css/app.css'].file}`);
    assert.ok(read(`public/build/${manifest['resources/js/app.js'].file}`).includes('appearance-preview'));
    for (const selector of ['.bg-canvas', '.bg-surface', '.text-foreground-muted', '.text-primary-foreground']) {
        assert.ok(stylesheet.includes(selector), selector);
    }

    for (const family of ['inter', 'jetbrains-mono']) {
        const font = Object.entries(manifest).find(([source]) => source.includes(`${family}-latin-wght-normal.woff2`))?.[1];
        assert.ok(font, family);
        assert.ok(existsSync(new URL(`../../public/build/${font.file}`, import.meta.url)));
        assert.ok(stylesheet.includes(font.file), font.file);
        assert.ok(existsSync(new URL(`../../public/licenses/${family}-OFL.txt`, import.meta.url)));
    }

    assert.equal(existsSync(new URL('../../public/hot', import.meta.url)), false);
});
