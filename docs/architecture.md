# Product and application architecture

Status: Phase 1 foundation/installer implementation. Phase 0 source inspection is historical; current implementation owners and evidence are below. No full SaaS or authentication is claimed.

## Stack and compatibility

Laravel 13 / Blade Components / Tailwind CSS / Alpine.js / Vite / MySQL; Composer/npm; existing PHPUnit. PHP 8.5 is the development baseline. Official Laravel 13 support covers PHP 8.3–8.5; framework minimum is 8.3. Local PHP 8.5.12 and Composer 2.10.3 were executed. composer.lock records Laravel v13.39.0; framework composer.json requires PHP ^8.3. This verifies version alignment, not all extensions or production compatibility.

Observed declarations: Laravel ^13.17, PHP ^8.3, Tailwind ^4, Vite ^8, Laravel Vite plugin ^3.1. Phase 1 adds exact Alpine CSP/Inter dependencies and an npm lockfile; the original Phase 0 snapshot had neither. Before Phase 1 pin/verify Node and frontend dependencies and run relevant compatibility checks. Production database is MySQL; D-01 approves MySQL 8.4 LTS, utf8mb4_0900_ai_ci and verified TLS. SQLite test results cannot qualify MySQL. Phase 0 made no dependency changes; Phase 1 dependency evidence is in Testing.

References checked 2026-10-31: [Laravel releases](https://laravel.com/docs/13.x/releases), [deployment requirements](https://laravel.com/docs/13.x/deployment). Installed lock/source is local evidence; official docs are compatibility references.

## Layers and conventional structure

| Path (planned responsibilities unless already scaffolded) | Responsibility |
| --- | --- |
| routes/web.php, bootstrap/app.php | Named web routes, middleware registration, safe errors |
| app/Http/Controllers | Request/action/response coordination |
| app/Http/Requests | Authorization, server validation and explicit input allowlists |
| app/Actions or focused services | Installer/consequential workflows when complexity needs them |
| app/Models, database/migrations | Eloquent persistence, constraints and transactions |
| app/Policies, provider Gates | Capabilities and object ownership; fail closed |
| app/Jobs, Events, Listeners, Notifications | Bounded asynchronous work and explicit lifecycle events |
| config, lang/en | Operator configuration and semantic source translation keys |
| resources/views/components | Small explicit Blade APIs and shared layouts |
| resources/css/app.css, resources/js/app.js | Sole token owner and bundled Alpine initialization |
| resources/js/theme-bootstrap.js (proposed) | Pre-paint theme resolver, shared with interaction owner |
| tests/Unit, tests/Feature, future browser tests | Isolated observable behavior and interactions |
| storage/app/private | Proposed private state/quarantine; never publicly served |
| docs | Owning specifications and decisions |

Keep one application and identity system; no generic repository framework, plugin system or 30 duplicated module trees. Blade receives prepared values; no business queries or mutations. Focused actions are justified by behavior, not created speculatively.

## Module boundaries

Public website consumes published localized content and truthful product offerings. Installer owns deployment-authorized first initialization, not ongoing identity. Identity owns users/roles/sessions; account settings resolve the current owner. Administration composes explicit delegated capabilities; CMS owns content/translation/media lifecycles. Mail engine owns mailbox access, untrusted ingestion/expiration; infrastructure receives SMTP. Billing owns provider events/subscriptions; entitlements expose a server-side policy used by mailbox actions. Cross-module jobs carry minimal IDs, are idempotent and recheck current state.

One database initially with explicit user/mailbox ownership; no tenant model is assumed. If organizations/tenancy are requested later, decide their isolation boundary before schemas. Separate public assets from private mail/media/state. Database constraints enforce uniqueness and consequential invariants.

## Runtime boundaries

Installer must boot before application database availability; use private encrypted file sessions and file cache on the approved single persistent node, with explicit operator-verified loopback/tunnel access. Multi-replica/ephemeral infrastructure requires a shared durable coordinator before installation is allowed. DB transactions cannot atomically cover MySQL DDL and configuration files; checkpoints and recovery are mandatory.

Laravel web HTTP, queue workers, scheduler, receiving infrastructure and storage are distinct operational processes. Select queue/lock backing under deployment review; do not imply sync queues meet production inbound volume. Jobs use bounded retries, backoff, timeout, idempotency, dead-letter handling and post-commit dispatch. No network calls while holding broad database locks.

Topic contracts: [installer](installation.md), [identity](authentication-authorization.md), [UI](ui-system.md), [localization](internationalization.md), [future modules](future-requirements.md), [operations](operations.md).

## Phase 1 source owners

InstallationController coordinates Form Requests and prepared Blade data. Access validates the raw peer, exact local host/port, no forwarding and provisioned expiring local/ssh-tunnel profile before session middleware. Requirements checks current runtime. Database builds allowlisted MySQL configuration and verifies version/collation/TLS/empty target. Installer owns proof/session binding, password hashing, allowlisted migrations, transaction/receipt and conservative recovery. InstallationStore owns encrypted atomically replaced state and cross-process nonblocking flock.

AppServiceProvider selects pre-database file stores and registers an opaque installed driver; only the connection factory decrypts selected credentials, never the config repository. Persistent state is not deleted on deploy. Role enum/migration prepare exactly three roles, excluding role from public mass assignment. No authorization dashboard/identity system exists yet.

Single node is the qualified design scope. Future scale-out requires externally durable shared installation state/lock/sessions and a reviewed secret store before adding replicas. Preserve installation identity/receipt/key, freeze all installer writes during migration, copy/verify state, qualify shared lock/crash behavior, then enable replicas. Never copy a local flock design across nodes and assume coordination.
