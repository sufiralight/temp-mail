# Architectural decision log

Status: Phase 0, 2026-10-31. Accepted means approved by Master Prompt 01; proposed means not yet a production choice. Earlier “resolved Phase 1” decisions are withdrawn from active authority and preserved in the archive.

| ID | Status | Decision and rationale |
| --- | --- | --- |
| A-01 | Accepted | Laravel 13 with compatible PHP, Blade SSR/components, Tailwind, Alpine, Vite, MySQL; minimal dependencies and Laravel conventions |
| A-02 | Accepted | Exactly Super Admin/Admin/User; public registration server-assigns User; explicit capabilities and ownership |
| A-03 | Accepted | One teal/neutral Inter design system; icon-only Sun/Moon/Monitor segments, Light/Dark/System and single shared logic |
| A-04 | Accepted | Four installer steps then Login; deployment-controlled authorization with no credential/token UI |
| A-05 | Accepted | Both direct verified local aliases accepted independently of APP_URL host; no automatic alias redirect or cookie sharing assumption |
| A-06 | Accepted | English first; structural RTL, stable translation keys and per-language CMS slugs; registry needs approval |
| A-07 | Accepted | No public installer on empty-user detection; durable completion and safe operator recovery preserve APP_KEY/data |
| A-08 | Accepted | Phase 0 docs only; historical reports are not proof; Phase 1 requires separate explicit approval |
| A-09 | Accepted | SMTP receipt is infrastructure responsibility; Laravel processes authenticated bounded ingestion asynchronously |
| A-10 | Proposed | Self-host Inter with script fallback to avoid third-party font requests; asset/license selection in Phase 1 |

## Open decisions and gates

| ID | Open choice / recommendation | Resolve before | Required evidence |
| --- | --- | --- | --- |
| D-01 | Supported MySQL version, utf8mb4 collation, TLS, privilege split and DDL qualification | Phase 1 DB implementation | Target host requirements, live isolated MySQL integration and failure tests |
| D-02 | Single persistent node vs shared coordinator; config secret owner; checkpoint/lock durability | Phase 1 installer | Crash/recovery prototype, backup model, lock scope, config-cache proof |
| D-03 | Explicit local opt-in and network topology; production gateway/private network/tunnel and origin firewall | Phase 1 access implementation | Actual peer provenance, no local proxy confusion, spoofing/remote negative tests |
| D-04 | Node/frontend locks, Alpine standard vs CSP build, CSP nonce/bootstrap loading and browser floor | Phase 1 asset integration | Official version requirements, production build and browser CSP/no-flash evidence |
| D-05 | Password hasher/max, email normalization/collation and session revocation | Initial Super Admin validation; full identity in Phase 2 | Unicode/no truncation, unique races, multiple sessions; no inherited Argon2 policy assumption |
| D-06 | Sender/transport and verification requirements, registration enumeration behavior | Phase 2 identity | Failure-safe transport, reset/verification replay and neutral outcomes |
| D-07 | Admin delegation/promotion/recovery and final Super Admin protection | Any privileged provisioning after installer | Recent auth, audit, races, no public escalation; operator-only baseline recommended |
| D-08 | Proposed 30-locale registry, zh/pt variants and public locale URL strategy | Treating registry as final; Phase 5 | Explicit user approval, native names/script review and SEO policy |
| D-09 | Receiving provider vs operated MTA, region/residency, retention and abuse capacity | Phase 7 | MX/TLS/auth integration, resource/retention budgets and incident owner |
| D-10 | Billing provider, taxes/currencies, usage enforcement and reconciliation | Phase 9 | Signed webhook/idempotency/replay and commercial/privacy review |

No production proxy or Cloud platform is selected. Docker/WSL access is not silently treated as direct localhost. Provider residency, retention law and commercial policy remain open; no legal or compliance certification is claimed.

## Evidence and sources

Observed PHP 8.5.12, Composer 2.10.3, locked Laravel v13.39.0; no Alpine/npm lock or product code. [Laravel 13 release policy](https://laravel.com/docs/13.x/releases) supports PHP 8.3–8.5. [Deployment](https://laravel.com/docs/13.x/deployment) documents required extensions and public document root. References checked 2026-10-31; API behavior must be verified again against installed versions during implementation. Product-specific security models here are proposed engineering designs, not framework guarantees.
