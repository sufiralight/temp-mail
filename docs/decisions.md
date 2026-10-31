# Architectural decision log

Status: Phase 1, 2026-10-31; initial deployment unblock applied. Accepted means approved by Master Prompt 01; proposed means not yet a production choice. Earlier “resolved Phase 1” decisions are withdrawn from active authority and preserved in the archive.

| ID | Status | Decision and rationale |
| --- | --- | --- |
| A-01 | Accepted | Laravel 13 with compatible PHP, Blade SSR/components, Tailwind, Alpine, Vite, MySQL; minimal dependencies and Laravel conventions |
| A-02 | Accepted | Exactly Super Admin/Admin/User; public registration server-assigns User; explicit capabilities and ownership |
| A-03 | Accepted | One teal/neutral Inter design system; icon-only Sun/Moon/Monitor segments, Light/Dark/System and single shared logic |
| A-04 | Accepted | Four installer steps then Login; deployment-controlled authorization with no credential/token UI |
| A-05 | Accepted | Both direct verified local aliases accepted independently of APP_URL host; no automatic alias redirect or cookie sharing assumption |
| A-06 | Accepted | English first; structural RTL, stable translation keys and per-language CMS slugs; registry needs approval |
| A-07 | Accepted | No public installer on empty-user detection; durable completion and safe operator recovery preserve APP_KEY/data |
| A-08 | Accepted | Phase gates require explicit approval; Phase 1 is now authorized, Phase 2 is not; historical reports are not proof |
| A-09 | Accepted | SMTP receipt is infrastructure responsibility; Laravel processes authenticated bounded ingestion asynchronously |
| A-10 | Proposed | Self-host Inter with script fallback to avoid third-party font requests; asset/license selection in Phase 1 |

## Approved initial-release resolutions

The user's Phase 1 unblock instruction approves D-01 and D-02 and the security strategy of D-03. These resolutions supersede their earlier open descriptions below.

- **D-01 approved:** MySQL 8.4 LTS, utf8mb4 / utf8mb4_0900_ai_ci. Probe the real server version, available collation and transport. Production database connections require TLS with an operator-provisioned CA and certificate verification; never disable verification. Empty approved targets only; credentials and connector errors are private.
- **D-02 approved:** one application server with persistent private storage, encrypted installation state, atomic replacement and cross-process OS file locks. Pre-database file sessions/cache need no application tables. A provisioned marker plus database receipt and completion state prevent reopening. Distributed deployment is future work, not an in-memory fallback.
- **D-03 strategy approved:** restricted SSH tunnel or an equivalently reviewed infrastructure restriction. Hosting and actual SSH availability remain unconfirmed production prerequisites. The implemented baseline accepts an explicitly provisioned loopback-only listener and verified peer, with no proxy forwarding. Public exposure is forbidden; no Setup Credential UI. An equivalent gateway requires a separate qualified profile, not a broad trust switch.

Implementation choices for this bounded phase: Alpine CSP build with nonce-based early theme bootstrap; local bundled Inter; Argon2id passwords of 12–128 Unicode characters and lowercase/trimmed initial email. Full identity/session revocation remains Phase 2. D-04/D-05 require actual tests/build evidence, not a historical claim.

## Remaining decisions and original gates

| ID | Open choice / recommendation | Resolve before | Required evidence |
| --- | --- | --- | --- |
| D-01 | Approved: MySQL 8.4 LTS, utf8mb4_0900_ai_ci, verified production TLS | Initial architecture resolved | Deployment privileges/TLS certificates still qualified per environment |
| D-02 | Approved: single persistent node, encrypted files and OS locks before DB | Initial architecture resolved | Production permissions, filesystem durability and restore rehearsal remain gates |
| D-03 | Approved strategy: restricted SSH tunnel / equivalent infrastructure control | Hosting/actual SSH and isolation confirmation pending | Actual peer provenance, origin firewall, tunnel and no public forwarding |
| D-04 | Resolved initial integration: Node 24.22.1, npm lockfile, Alpine CSP, nonce bootstrap, local Inter | Initial integration implemented/tested | Production build and Chrome checks passed; broader browser/first-paint qualification remains limited |
| D-05 | Initial installer resolved: Argon2id, 12–128 Unicode characters, trimmed lowercase email | Initial administrator implemented/tested | Full public identity normalization/enumeration and multi-session revocation remain Phase 2 |
| D-06 | Sender/transport and verification requirements, registration enumeration behavior | Phase 2 identity | Failure-safe transport, reset/verification replay and neutral outcomes |
| D-07 | Admin delegation/promotion/recovery and final Super Admin protection | Any privileged provisioning after installer | Recent auth, audit, races, no public escalation; operator-only baseline recommended |
| D-08 | Proposed 30-locale registry, zh/pt variants and public locale URL strategy | Treating registry as final; Phase 5 | Explicit user approval, native names/script review and SEO policy |
| D-09 | Receiving provider vs operated MTA, region/residency, retention and abuse capacity | Phase 7 | MX/TLS/auth integration, resource/retention budgets and incident owner |
| D-10 | Billing provider, taxes/currencies, usage enforcement and reconciliation | Phase 9 | Signed webhook/idempotency/replay and commercial/privacy review |

No hosting provider, production proxy or Cloud platform is selected; actual SSH availability is unconfirmed. Docker/WSL access is not silently treated as direct localhost. Provider residency, retention law and commercial policy remain open; no legal or compliance certification is claimed.

## Evidence and sources

Phase 0 observed PHP 8.5.12, Composer 2.10.3 and locked Laravel v13.39.0 without Alpine/product code. Phase 1 adds Alpine CSP 3.17.5, Inter Variable 5.3.0, the npm lockfile and scoped installer code; current evidence is in Testing. [Laravel 13 release policy](https://laravel.com/docs/13.x/releases) supports PHP 8.3–8.5. [Deployment](https://laravel.com/docs/13.x/deployment) documents required extensions and public document root. References checked 2026-10-31; API behavior must be verified again against installed versions during implementation. Product-specific security models here are proposed engineering designs, not framework guarantees.


Phase 1 implementation review: installed framework connector/migrator and request-forgery sources were inspected. Official references consulted: [PDO MySQL TLS options](https://www.php.net/manual/en/ref.pdo-mysql.php), [MySQL 8.4 encrypted connections](https://dev.mysql.com/doc/refman/8.4/en/using-encrypted-connections.html), [Laravel databases](https://laravel.com/docs/13.x/database), [Alpine CSP](https://alpinejs.dev/advanced/csp). This is source/API review, not infrastructure certification. MySQL 8.4.13 portable test package came from the [official download page](https://dev.mysql.com/downloads/mysql/8.4.html), checksum matched; only disposable loopback tests used it.
