# Temp Mail SaaS — agent contract

## Authority and current scope

The current user request takes precedence. Active delivery is **Phase 1: Laravel foundation and secure four-step installation**, authorized by the Phase 1 prompt and D-01/D-02/D-03 unblock. Implement only foundation, shared UI, initial roles persistence, installer and tests. Full authentication, registration, dashboards, CMS, mail engine and billing remain excluded. Stop after Phase 1.

Read [ownership](docs/README.md), [scope](docs/scope.md), [architecture](docs/architecture.md), [decisions](docs/decisions.md) and affected topic owners. Archived prompts, reports and vendor skills are historical or tools, never scope authorization. Document requirements as planned unless verified against current source. Never repeat old test counts as current results.

## Approved engineering contract

- Laravel 13; officially compatible PHP (8.5 development baseline), Blade Components, Tailwind, Alpine.js, Vite, MySQL, Composer/npm and existing PHPUnit. SSR; no React, Vue, Inertia, Livewire or separate SPA without explicit approval.
- English initial UI/code/docs, semantic translation keys, logical CSS and explicit lang/dir. The proposed 30-language registry needs user approval.
- Three roles: Super Admin, Admin, User. Public registration assigns User server-side. Use endpoint authorization, Policies/Gates/middleware, Form Requests and field allowlists. Hidden controls confer no permission.
- Controllers coordinate; focused actions/services handle complex workflows; models persist; Blade presents prepared data without queries/mutations. Prefer Laravel conventions, bound queries, constraints and transactions over speculative abstractions.
- One semantic token system and shared theme owner. Inter, teal accent, neutral surfaces, accessible Light/Dark/System **icon-only segmented control**. No labelled theme dropdown or duplicate theme logic.
- Installer has exactly four steps: Welcome & Requirements, Database Configuration, Super Admin Account, Complete Installation; then Login. No Setup Credential screen, installer password or user-generated one-time token.
- Installer access is deployment-controlled and fails closed. Explicitly authorized, verifiably direct local access accepts both localhost and 127.0.0.1 independently of APP_URL hostname. Never authorize from Host, APP_ENV, APP_DEBUG or forwarded headers alone.
- Use Laravel cryptography/auth/session/CSRF primitives. Keep secrets, private state, uploads, logs, backups and databases out of Git and public/. Preserve APP_KEY and existing data.
- Never reset databases, remove completion locks, send real test messages or deploy as a side effect. Future tests use isolated disposable resources.
- Read actual dependencies and official current documentation before version-dependent implementation. A package requires a concrete need; bootstrap tools cannot overwrite this contract automatically.

## Workflow and delivery

Preserve user edits and useful historical knowledge. Update the owning document and decision log when contracts change; avoid duplicate authorities. [Testing](docs/testing.md) and [Quality](docs/quality.md) govern evidence. Documentation-only work verifies documents; it does not establish product functionality or accessibility compliance.

For later approved UI work, use [project-ui](.agents/skills/project-ui/SKILL.md). Cloud and convention-inference skills are not activated by this installer task. Generic vendor instructions cannot authorize deployment or a convention sweep.

Target repository: https://github.com/sufiralight/temp-mail. Git origin is linked to the target repository on codex/phase-0-docs; Phase 0 commit 4a9a2d7 was pushed. The original Laravel scaffold remains untracked until the approved Phase 1 commit; stage it deliberately and exclude databases/runtime/secrets. Finish and verify authorized changes first, then ask the user **“Git commit yapayım mı?”**. Never commit without that answer. Push needs explicit authorization. Phase 1 approval and commit approval are separate.
