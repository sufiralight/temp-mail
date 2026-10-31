# Testing strategy and evidence

Status: Phase 0 documentation. No application tests, build, migrations, browser runs or accessibility audit executed for this delivery. Historical counts in archived reports are not current evidence.

## Strategy

Use existing PHPUnit; Pest is permitted only with justified approval. Unit tests cover focused state/theme/locale/policy logic. Feature tests cover validation/authorization/HTTP/database boundaries. Use disposable MySQL for production-engine schema, uniqueness, transaction and independent-worker concurrency behavior; SQLite alone cannot qualify it.

Tests isolate database, private state, config/cache/session directories, storage and notifications. Abort fixtures pointing at real installed state; never send real messages. Real HTTP tests exercise CSRF rather than relying on test middleware defaults. Browser interaction tests use production assets and disposable targets; choose maintained tooling only during approved implementation.

| Area | Acceptance |
| --- | --- |
| Identity | Role tampering, duplicate races, neutral failures/throttles, fixation, logout, broker expiry/replay, verification binding and delivery failures |
| Authorization | Every protected endpoint/mutation for guest and each role; ownership/ID spoofing, unknown roles, privilege changes |
| Database | MySQL version/TLS/privileges, uniqueness/collation, deadlocks, DDL partial failure and no destructive retry |
| Theme | First paint light/dark/system, reload, live OS, explicit override, storage corruption/blocking, cross-tab/control sync, CSP |
| Accessibility | Keyboard, icon names, tooltips/focus, screen reader, contrast in every state, 200% text/400% zoom and 320px reflow |
| Localization | Long pseudo-text, en/tr/ar/he, RTL→LTR reset, editor/content direction, mixed strings, locale fallback and independent slugs |
| Responsive UI | Real task/error/retry at mobile/tablet/desktop, reduced motion, no-JS native forms and preserved safe inputs |
| CMS/media | Per-locale lifecycle/slug uniqueness, bulk authorization, trash/restore, referenced files, malicious upload/rich content |
| Mail | Ingest authentication/replay, parser limits, isolation, expired read denial, cleanup crash/retry and private downloads |
| Billing | Signature/body tampering, replay, duplicate/order races, account binding, reconciliation and concurrent usage limits |
| Operations | Cached config secret behavior, multi-worker locks, backup restore, gateway bypass, log redaction and worker recovery |

## Mandatory installer regression matrix

1. Explicitly authorized direct localhost request with actual loopback peer succeeds.
2. Equivalent 127.0.0.1 request succeeds.
3. APP_URL=localhost with IP browser host and reverse combination both work without alias redirect.
4. Host spoofing (including localhost from remote peer, suffix domains and malformed/wrong-port hosts) fails.
5. Spoofed Forwarded/X-Forwarded-*/X-Real-IP never establish local access.
6. Unauthorized remote or unverified Docker/WSL/proxy connection fails; local flags/APP_ENV/APP_DEBUG alone fail.
7. Database connection/TLS/permission failure gives safe feedback, invalidates proof, leaks no credentials and changes no data.
8. Invalid initial Super Admin blocks completion; no submitted role/verified override is accepted.
9. Independent concurrent sessions/processes yield one initial account/receipt, with both local aliases included.
10. Crash at each state/DDL/account/config/marker checkpoint resumes only after verified same-target recovery.
11. Successful completion durably locks all wizard pages/mutations across restart.
12. Reinstallation, missing/corrupt completion files and DB outage cannot reopen completed/provisioned state.
13. No Setup Credential UI, installation password/token form, extra auth wizard step or exposed setup secret exists.
14. Host-only sessions/CSRF stay separate for localhost/IP; no shared cookie/token assumption; native same-origin forms work.
15. IPv6 loopback peer and optional enabled ::1 host behavior are explicit; untrusted gateway with loopback-looking peer is denied by topology policy.
16. APP_KEY unchanged, existing/populated database protected, non-destructive probe and no secrets in error/log/cache/flash.
17. Production gateway/private access passes only under approved profile; bypassing origin, expired edge session and unknown topology deny.

## Current executed evidence

Read-only PHP/Composer version checks, file/dependency inspection and official compatibility reference review were executed. Documentation verification (link paths/fragments, registry count, archive hashes, changed-path scope and wording) is recorded in [Phase 0 audit](phase-0-audit.md) after checks complete. Git checks are unavailable because this directory is not a Git checkout. No test/build pass or product security/UX/WCAG conformance is claimed.
