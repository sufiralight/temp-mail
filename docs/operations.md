# Deployment and installation operations

Status: Phase 1 implementation, single persistent application server. No production deployment has been performed. [Installation](installation.md) owns state/trust; [Security](security.md) owns common controls.

## Approved deployment boundary

D-01: MySQL 8.4 LTS, utf8mb4_0900_ai_ci and verified production TLS. D-02: durable private state/OS locks on one application node. D-03: restricted SSH tunnel or equivalently reviewed infrastructure access. Hosting provider and actual SSH availability are **not confirmed**; production installation is blocked until these prerequisites and listener/firewall isolation are verified.

The app cannot inspect SSH authentication or prove no public reverse proxy forwards to loopback. The operator must verify that before using --restricted. Raw peer/host/port/header checks are additional application controls. No generic gateway/proxy profile is enabled. A production-equivalent gateway requires a reviewed profile and origin-bypass tests.

## Local development setup

1. Preserve the existing .env and APP_KEY. For a genuinely new checkout only, copy .env.example if absent and privately provision one valid key; never regenerate an installed key. Install locked dependencies with Composer and npm ci --ignore-scripts. Do not run composer setup blindly: scaffold scripts can generate keys and migrate.
2. Use PHP 8.3–8.5 (development verified 8.5.12), required extensions including pdo_mysql and Argon2id; Node 24.22.1/npm 11.19.1 are the exercised frontend runtime.
3. Provide a **fresh** MySQL 8.4 database. Set INSTALLATION_DATABASE_HOSTS to exact approved destinations and INSTALLATION_DATABASE_PORT appropriately. Provision a readable CA via INSTALLATION_TLS_CA. TLS is default; local-only developers may explicitly set INSTALLATION_TLS_REQUIRED=false for their private test database. Production overrides that opt-out.
4. Provision writable private installation directory, storage/framework/sessions, storage/framework/cache/data, storage/framework/views and bootstrap/cache. Restrict NTFS ACLs/POSIX permissions to operator/service identity. Default state is storage/app/private/installation. Never expose it under public/.
5. Set INSTALLATION_ENABLED=true only after verifying no public forwarding. Bind the web listener to loopback: php artisan serve --host=127.0.0.1 --port=8000. Run php artisan install:prepare --profile=local --port=8000 --restricted in a private operator terminal. It prints no secret and adds no user step; existing state is never overwritten.
6. Run npm run build (the enforced CSP is qualified against production assets; HMR is not enabled). Open either http://localhost:8000/install or http://127.0.0.1:8000/install. Both aliases work independently of APP_URL; keep one origin throughout an attempt because cookies/CSRF/storage are separate.
7. Review requirements → test database → enter Super Admin → review/complete. Only final completion runs the four approved migrations; do not migrate/seed beforehand. Completion redirects to an honest /login handoff, not an authentication form.
8. Disable INSTALLATION_ENABLED after completion and close the private listener/tunnel. Durable completion blocks reinstall regardless of this flag.

This delivery does not modify the real .env, APP_KEY or configured database. Test servers use isolated state/databases. Network connections/probes are rate limited and safe errors omit credentials/DSNs. The app uses encrypted file sessions/file cache before DB and an opaque installed connection after selection, overriding scaffold DB settings without writing .env.

## Production prerequisites and access

Confirm provider, SSH account/authentication, loopback-only listener and firewall/no-proxy-bypass before enabling installation. Disable debug. Establish an authenticated restricted tunnel (for example a local port forward whose destination is the private listener); use the tunnel's configured local port consistently. An explicit ssh-tunnel profile is required: php artisan install:prepare --profile=ssh-tunnel --port=8000 --restricted. No application setup password/token is involved.

Do not expose an uninstalled origin while “waiting for setup.” If restrictions cannot be verified, leave INSTALLATION_ENABLED=false and do not provision access. Never substitute APP_ENV/APP_DEBUG/Host/empty users for infrastructure authorization. Docker/WSL bridge forwarding is not silently trusted.

Production DB traffic requires an operator CA, certificate and hostname verification plus a negotiated cipher, even through a private network. Test privileges with separate least-privilege runtime/migration credentials if the final deployment supports that split; this installer currently persists the supplied installation connection, so reduce/rotate its privileges after installation through a reviewed operator change/rehearsal. It never disables certificate verification.

## State, recovery and cached configuration

Access attestation expires after two hours; database/session proof after ten minutes. To renew an unfinished original profile, reconfirm restrictions and use install:prepare --profile=<original> --port=<original> --restricted --renew. Completed state cannot be renewed. Renewing access does not renew database proof or change its target.

Back up original DB, encrypted state/provisioned marker, original APP_KEY and private environment together. A finalizing checkpoint blocks web requests. After inspection/backup, install:recover --reviewed checks exact source migration fingerprint/history, required schema and installation receipt. It seals an existing matching initial account; complete schema with no users/receipt allows administrator re-entry at /install/admin from a newly authorized session. Partial DDL stays blocked; repair the original schema under review or restore matching backups. Never delete markers/state, reset tables or generate another key.

config:cache contains ordinary Laravel secrets such as APP_KEY and must stay private. The installed DB password is not copied into config: selected credentials decrypt only while constructing a Connection object. Isolated tests exercise cache serialization and a fresh process querying the selected DB. Do not use config dumps or general debug tools to inspect connection objects.

Atomic replacement/fsync and nonblocking flock target a single persistent disk. Transient Windows destination holds get bounded retries without unlinking the previous state. Power-loss behavior, filesystem guarantees, service-account ACLs and full backup restoration remain deployment qualification tasks. Missing/corrupt state never opens setup.

## Future multi-server migration

Before adding replicas: stop installer writes and public mutations for the migration window, back up matching state/DB/key, move state/config custody and locks to a reviewed shared durable coordinator, move sessions/cache to shared stores, migrate private objects to authorized shared storage and preserve the installation UUID/receipt/completion lock. Qualify competing nodes, crashes, key custody, cache/config reload and restore behavior before enabling traffic. Local disk/flock is explicitly unsupported across replicas.

Queue workers, scheduler, inbound receiver and billing integration remain later phases. Release migrations need their own operator process and rollback/data preservation plan. No Cloud provider/resource or production infrastructure is provisioned here.
