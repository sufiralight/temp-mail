# Scope and dependency-aware roadmap

Status: Phase 1 authorized by the Foundation & Secure Installation Wizard prompt and the D-01/D-02/D-03 unblock. Historical reports remain non-authoritative. Existing data and APP_KEY must be preserved.

## Current acceptance

Deliver Laravel 13/MySQL 8.4 foundation, shared Blade/Tailwind/Alpine CSP/Inter UI, icon-only three-theme control, four-step private installer, initial Super Admin and durable completion/recovery. Tests use isolated resources; never migrate the real configured database during development. Full login/registration/password reset/dashboards remain Phase 2; /login is an honest handoff only.

## Roadmap

| Phase | Delivery | Dependencies / exit gate |
| --- | --- | --- |
| 0 | Architecture, reconciled docs, design specification | Documentation review; explicit approval to start 1 |
| 1 | Laravel 13 foundation, reusable UI primitives, secure installer | Resolve topology/access/storage and MySQL gates; isolated security/browser acceptance |
| 2 | Authentication, registration, three roles, authorization, account | Completed installer; identity delivery/normalization/revocation choices |
| 3 | Public website, layouts and responsive navigation | Shared UI; content/legal/pricing copy approved |
| 4 | Admin dashboard and settings | Verified identity, delegated capabilities and audited settings |
| 5 | Internationalization and translation infrastructure | User approves registry and URL policy; native language release review |
| 6 | Multilingual CMS and media | Locales, authorization, storage and sanitizer decisions |
| 7 | Inbound email infrastructure/backend | Receiving provider/MTA, DNS, abuse/retention and operational capacity |
| 8 | Mailbox UX and user dashboard | Engine access/isolation/expiry contracts and measured refresh transport |
| 9 | Plans, subscriptions, billing and entitlements | Provider, reconciliation and enforceable usage counters |
| 10 | SEO, accessibility, performance, security and operations readiness | Full staging acceptance, restore/incident drills and release review |

No major phase has been reordered. Phases 1–4 must use translation keys/logical CSS and accessible shared primitives before Phase 5; this is foundational readiness, not shipping translation management early. Phase 2 may provide minimal role entry destinations for authentication acceptance; complete admin functionality remains Phase 4 and mailbox dashboard remains Phase 8. Phase 3 pricing must use approved truthful content before Phase 9 checkout exists; do not expose fake purchase controls. Hardening is continuous; Phase 10 consolidates release evidence.

## Phase 1 approved implementation plan

1. Reinspect actual scaffold/lockfiles and apply approved D-01–D-03 in Decisions. Choose supported MySQL version, access topology, persistent state/locking and credential configuration owner.
2. Establish no-database bootstrap, secure headers/errors, English catalog/dir, private state, typed validation and disposable test boundaries. Review scaffold setup scripts before any use: they can generate keys and migrate.
3. Integrate one Alpine/Vite/Tailwind/Inter theme owner and minimal accessible Blade components; implement the specified icon segments, not the legacy dropdown.
4. Implement infrastructure authorization boundary independently of user wizard steps; test localhost and 127.0.0.1 against both APP_URL variants before building later steps.
5. Implement requirements → non-destructive MySQL probe → validated pending Super Admin → locked completion, checkpoints/receipts and operator recovery.
6. Verify concurrent completion, interrupted DDL, key/data preservation, reinstall lock, spoofing and browser/no-JS behavior. Redirect to /login; if Phase 2 auth is absent, disclose that truthfully with no fake sign-in form.
7. Update evidence and owning docs; stop at the Phase 1 boundary. Never implement auth/CMS/mail/billing merely to fill navigation.

Additional languages, public role editors, MFA, social login and custom SPA tooling are not implicit approvals. Commit permission does not authorize Phase 1.
