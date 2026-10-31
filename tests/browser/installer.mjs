import { chromium } from '@playwright/test';
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { mkdir, writeFile, readFile } from 'node:fs/promises';
import path from 'node:path';
import { randomBytes } from 'node:crypto';

const root = process.cwd();
const fixture = path.join(root, 'storage/framework/testing/browser-' + randomBytes(8).toString('hex'));
const port = 18083;
const nativeOnly = process.env.NATIVE_ONLY === '1';
const realMySQL = Boolean(process.env.MYSQL_INSTALLATION_TEST_PORT && process.env.MYSQL_INSTALLATION_TEST_CA);
const databaseName = realMySQL ? 'temp_mail_browser_' + randomBytes(8).toString('hex') : 'browser_fixture';
if (realMySQL) {
    const created = spawnSync('php', ['tests/Support/mysql-browser-db.php', 'create', databaseName], { encoding: 'utf8' });
    assert.equal(created.status, 0, created.stderr);
}
await mkdir(fixture, { recursive: true });
for (const folder of ['sessions', 'cache', 'views', 'private']) await mkdir(path.join(fixture, folder));
await writeFile(path.join(fixture, 'fixture.sqlite'), '');
const env = {
    ...process.env, APP_ENV: 'local', APP_DEBUG: 'false', APP_KEY: 'base64:' + Buffer.alloc(32, 'b').toString('base64'),
    APP_URL: `http://localhost:${port}`, INSTALLATION_ENABLED: 'true', INSTALLATION_TLS_REQUIRED: realMySQL ? 'true' : 'false',
    INSTALLATION_TLS_CA: process.env.MYSQL_INSTALLATION_TEST_CA || '', INSTALLATION_DATABASE_PORT: realMySQL ? process.env.MYSQL_INSTALLATION_TEST_PORT : '3306',
    INSTALLATION_DIRECTORY: path.join(fixture, 'private'), SESSION_FILES: path.join(fixture, 'sessions'),
    CACHE_PATH: path.join(fixture, 'cache'), CACHE_LOCK_PATH: path.join(fixture, 'cache'), VIEW_COMPILED_PATH: path.join(fixture, 'views'),
    BROWSER_FIXTURE_DIRECTORY: fixture, BROWSER_FIXTURE_DATABASE: realMySQL ? 'mysql' : 'sqlite-workflow-fixture', LOG_CHANNEL: 'null',
};
const prepare = spawnSync('php', ['artisan', 'install:prepare', '--restricted', '--port=' + port], { env, encoding: 'utf8' });
assert.equal(prepare.status, 0, prepare.stdout + prepare.stderr);
const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', 'public', 'tests/Support/browser-router.php'], { env, stdio: ['ignore', 'pipe', 'pipe'] });
let serverErrors = '';
server.stderr.on('data', chunk => { serverErrors += chunk; });
let browser;
let activePage;
try {
    for (let attempt = 0; attempt < 40; attempt++) {
        try { if ((await fetch(`http://localhost:${port}/install`)).status === 200) break; } catch {}
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ javaScriptEnabled: !nativeOnly, viewport: { width: 1440, height: 1000 } });
    const page = await context.newPage();
    activePage = page;
    const errors = [];
    page.on('pageerror', error => errors.push(String(error)));
    page.on('console', message => {
        // Deliberately exercised HTTP denial responses are asserted separately below.
        if (message.type() === 'error' && ! /status of (410|419)/.test(message.text())) errors.push(message.text());
    });
    const base = `http://localhost:${port}`;
    await page.goto(base + '/install');
    await page.getByRole('heading', { level: 1, name: 'Welcome & Requirements' }).waitFor();
    assert.equal(await page.locator('h1').count(), 1);
    assert.equal(await page.getByText('Action needed', { exact: true }).count(), 0);
    assert.equal(await page.locator('input[type=radio]').count(), 3);
    if (!nativeOnly) {
    await page.getByRole('radio', { name: 'Dark', exact: true }).check();
    assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
    await page.reload();
    assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
    await page.getByRole('radio', { name: 'System', exact: true }).check();
    await page.emulateMedia({ colorScheme: 'dark' });
    await page.waitForFunction(() => document.documentElement.dataset.theme === 'dark');
    assert.equal(await page.locator('html').getAttribute('data-theme'), 'dark');
    await page.emulateMedia({ colorScheme: 'light', reducedMotion: 'reduce' });
    await page.waitForFunction(() => document.documentElement.dataset.theme === 'light');
    assert.equal(await page.locator('html').getAttribute('data-theme'), 'light');
    for (const palette of ['Light', 'Dark']) {
        await page.getByRole('radio', { name: palette, exact: true }).check();
        const contrastFailures = await page.evaluate(() => {
            const style = getComputedStyle(document.documentElement);
            const luminance = token => {
                let hex = style.getPropertyValue(token).trim().slice(1);
                if (hex.length === 3) hex = hex.split('').map(char => char + char).join('');
                const channels = hex.match(/.{2}/g).map(part => parseInt(part, 16) / 255).map(value => value <= .04045 ? value / 12.92 : ((value + .055) / 1.055) ** 2.4);
                return channels[0] * .2126 + channels[1] * .7152 + channels[2] * .0722;
            };
            return [['--ink', '--surface', 4.5], ['--secondary', '--surface', 4.5], ['--muted', '--canvas', 4.5], ['--accent', '--surface', 4.5], ['--on-accent', '--accent', 4.5], ['--control', '--surface', 3], ['--focus', '--surface', 3]].filter(([a, b, minimum]) => (Math.max(luminance(a), luminance(b)) + .05) / (Math.min(luminance(a), luminance(b)) + .05) < minimum);
        });
        assert.deepEqual(contrastFailures, [], palette + ' token contrast');
    }
    await page.getByRole('radio', { name: 'Light', exact: true }).focus();
    await page.keyboard.press('ArrowRight');
    assert.equal(await page.getByRole('radio', { name: 'Dark', exact: true }).isChecked(), true);
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('.theme-option[data-dismissed] .tooltip').isVisible(), false);
    const tab = await context.newPage();
    await tab.goto(base + '/install');
    await page.getByRole('radio', { name: 'Light', exact: true }).check();
    await tab.waitForFunction(() => document.documentElement.dataset.theme === 'light');
    await tab.close();
    const blockedContext = await browser.newContext();
    await blockedContext.addInitScript(() => {
        Storage.prototype.getItem = () => { throw new Error('Storage blocked'); };
        Storage.prototype.setItem = () => { throw new Error('Storage blocked'); };
    });
    const blockedPage = await blockedContext.newPage();
    await blockedPage.goto(base + '/install');
    await blockedPage.getByRole('radio', { name: 'Dark', exact: true }).check();
    assert.equal(await blockedPage.locator('html').getAttribute('data-theme'), 'dark');
    await blockedContext.close();
    }

    const ipContext = await browser.newContext();
    const ipPage = await ipContext.newPage();
    await ipPage.goto(`http://127.0.0.1:${port}/install`);
    await ipPage.getByRole('heading', { name: 'Welcome & Requirements' }).waitFor();
    const cookieName = (await context.cookies()).find(cookie => cookie.name.endsWith('-session'))?.name;
    assert(cookieName);
    assert.notEqual((await context.cookies()).find(cookie => cookie.name === cookieName)?.value, (await ipContext.cookies()).find(cookie => cookie.name === cookieName)?.value);
    await ipContext.close();
    for (const width of [320, 360, 768, 1440]) {
        await page.setViewportSize({ width, height: 1000 });
        assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    }
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.screenshot({ path: path.join(fixture, 'requirements-light.png'), fullPage: true });
    await page.getByRole('link', { name: 'Continue', exact: true }).click();
    const csrf = await context.request.post(base + '/install/database', { form: { db_host: '127.0.0.1' } });
    assert.equal(csrf.status(), 419);
    await page.getByLabel('Database name', { exact: true }).fill(databaseName);
    await page.getByLabel('Database username', { exact: true }).fill(realMySQL ? 'root' : 'fixture');
    await page.getByLabel('Database password', { exact: true }).fill(realMySQL ? '' : 'test-only-secret');
    await page.getByLabel('Database name', { exact: true }).fill('invalid-name');
    await page.getByLabel('Database password', { exact: true }).fill('must-not-be-flashed');
    await page.getByRole('button', { name: 'Test & continue' }).click();
    await page.getByRole('heading', { name: 'Please check the highlighted fields.' }).waitFor();
    assert.equal(await page.locator('#db_password').inputValue(), '');
    if (!nativeOnly) await page.waitForFunction(() => document.activeElement.id === 'validation-summary');
    await page.getByLabel('Database name', { exact: true }).fill(databaseName);
    await page.getByLabel('Database password', { exact: true }).fill(realMySQL ? '' : 'test-only-secret');
    if (!nativeOnly) {
    await page.getByRole('button', { name: 'Show password' }).click();
    assert.equal(await page.locator('#db_password').getAttribute('type'), 'text');
    assert.equal(await page.getByRole('button', { name: 'Hide password' }).count(), 1);
    await page.getByRole('button', { name: 'Hide password' }).click();
    }
    await page.getByRole('button', { name: 'Test & continue' }).click();
    await page.getByRole('heading', { name: 'Super Admin Account' }).waitFor();
    if (!nativeOnly) await page.getByRole('radio', { name: 'Dark', exact: true }).check();
    await page.setViewportSize({ width: 360, height: 900 });
    await page.screenshot({ path: path.join(fixture, 'administrator-dark-mobile.png'), fullPage: true });
    assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    await page.getByLabel('Name', { exact: true }).fill('Browser Owner');
    await page.getByLabel('Email address', { exact: true }).fill('browser@example.test');
    await page.getByLabel('Password', { exact: true }).fill('Long test-only password ✓');
    await page.getByLabel('Confirm password', { exact: true }).fill('Long test-only password ✓');
    await page.getByRole('button', { name: 'Review installation' }).click();
    await page.getByRole('heading', { name: 'Complete Installation' }).waitFor();
    await page.getByRole('button', { name: 'Complete installation' }).click();
    await page.waitForURL('**/login');
    await page.getByRole('heading', { name: 'Installation complete' }).waitFor();
    assert.equal(await page.locator('form').count(), 0);
    assert.equal((await context.request.get(base + '/install')).status(), 410);
    assert(!((await readFile(path.join(fixture, 'private/state'), 'utf8')).includes('test-only-secret')));
    assert.deepEqual(errors, []);

    const nativeContext = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 320, height: 900 } });
    const nativePage = await nativeContext.newPage();
    await nativePage.goto(base + '/login');
    assert.equal(await nativePage.locator('h1').innerText(), 'Installation complete');
    assert(await nativePage.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    await nativeContext.close();
    console.log(JSON.stringify({ passed: true, javascript: !nativeOnly, mode: realMySQL ? 'Real MySQL 8.4 with verified TLS' : 'SQLite workflow fixture, not MySQL qualification', checks: ['two local hosts', 'host-only separate sessions', ...(!nativeOnly ? ['theme reload/system/keyboard/cross-tab/blocked storage', 'tooltips', 'password visibility'] : []), 'real HTTP CSRF', '320/360/768/1440px reflow', 'four-step completion', 'durable lock', 'no-JS login handoff', 'no CSP/JS console errors'], screenshots: fixture }));
} catch (error) {
    if (activePage) {
        console.error('Failed URL:', activePage.url());
        console.error((await activePage.locator('body').innerText()).slice(0, 2500));
        await activePage.screenshot({ path: path.join(fixture, 'failure.png'), fullPage: true });
    }
    console.error(serverErrors.slice(-2000));
    throw error;
} finally {
    await browser?.close();
    server.kill();
    if (realMySQL) spawnSync('php', ['tests/Support/mysql-browser-db.php', 'drop', databaseName], { encoding: 'utf8' });
}
