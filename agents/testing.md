# Testing

Own PHPUnit baseline and regression coverage. Use factories and isolated configuration with fake or in-memory services as appropriate. Keep production credentials and endpoints out of test runs.

Cover observable behavior and denied paths, and record manual accessibility or deployment checks when relevant.

Read [coding standards](../docs/coding-standards.md) and [accessibility](../docs/accessibility.md) before changes.

After building frontend assets, run `node --test tests/js/design-system.test.mjs` for palette, appearance and release-asset regressions.
