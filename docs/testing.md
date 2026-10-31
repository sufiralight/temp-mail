# Testing strategy and evidence

Status: Phase 1 test strategy and executed evidence. Historical reports remain non-authoritative; actual current results appear below.

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

## Executed Phase 1 evidence — 2026-10-31

- PHP 8.5.12, Composer 2.10.3, locked Laravel v13.39.0; Node 24.22.1/npm 11.19.1, Vite 8.3.5. Installed @alpinejs/csp 3.17.5, @fontsource-variable/inter 5.3.0 and @playwright/test 1.64.1. No PHP dependency changes.
- PHPUnit: 48 tests / 135 assertions passed with a real isolated MySQL 8.4.13 server. This includes MySQL TLS probe/migrations/receipt/completion, wrong CA rejection, preservation of populated target, two competing independent PHP workers, and recovery after schema migration before account commit. SQLite workflow fixtures are separately named and do not count as MySQL qualification.
- Feature/security cases cover both approved local origins and both APP_URL mismatch combinations, raw IPv6 loopback, spoofed remote/Host/forwarded headers, wrong ports, missing/disabled/expired/corrupt state, step order, requirements, DB failure/redaction, invalid Super Admin/role tampering, session/proof ownership/expiry, durable lock, no credential UI, completed-state renewal rejection probe rate limiting, enforced production TLS opt-out refusal and partial-DDL recovery refusal.
- Independent config:cache serialization and fresh-process query test passed without serializing the fixture DB password. The original .env/APP_KEY/configured DB were not changed.
- Chrome headless browser runs completed all four steps against real MySQL with verified TLS, both JavaScript enabled and disabled. The browser server bound only to 127.0.0.1:18083 and used private disposable state/session/cache/view directories. Separate localhost/IP session cookies were checked; APP_URL used localhost while the IP alias remained usable.
- Enhanced browser checks: light/dark/system, reload, OS changes, explicit choice, keyboard arrows, tooltip Escape, cross-tab sync, blocked storage, password Show/Hide accessible names, validation-summary focus and blank password after errors, actual CSRF rejection (419), locked installer (410), no fake login form, 320/360/768/1440px reflow, selected light/dark token contrast and reduced-motion environment. Expected HTTP denial console messages are excluded from unexpected JS/CSP failures; their statuses are asserted separately.
- Native forms completed the four steps with JavaScript disabled. The no-JS login handoff also reflowed at 320px. No public registration/dashboard/password-reset route was added.
- Production frontend build passed. npm audit reported 0 vulnerabilities; Pint passed after formatting. Final document links/archive integrity and Git whitespace checks are recorded in the delivery report.

Visual review inspected Light desktop requirements and Dark 360px administrator forms from actual Chrome screenshots. They use empty password fields. QA images and MySQL binaries/data/certificates remain ignored under storage/framework/testing, not Git assets. Automated interaction and manual screenshot review are distinct evidence; no user study or WCAG certification is claimed.

Reproduce application tests with php vendor/bin/phpunit. Live MySQL cases require explicit MYSQL_INSTALLATION_TEST_PORT and MYSQL_INSTALLATION_TEST_CA pointing to a disposable loopback 8.4 instance; otherwise those tests skip honestly. They create/drop only random temp_mail_test_* databases. Browser tests use node tests/browser/installer.mjs; the same two variables select real MySQL, otherwise an explicitly labelled SQLite workflow fixture is used. Set NATIVE_ONLY=1 for the full no-JS flow. A disposable MySQL root account in this isolated test harness is never a production credential recommendation.

Limits: no production hosting/SSH/firewall/gateway verification, real external CA deployment, service-account NTFS ACL certification, filesystem power-loss test, full backup restore, native screen reader, Safari/Firefox or complete WCAG 2.2 AA audit. Responsive reflow checks are not a 400% zoom/200% text certification. Early nonce bootstrap runs before stylesheet paint by construction; no filmstrip-based first-paint measurement was performed. Phase 2 identity/mail/session revocation features remain unimplemented.
