# Installation flow

The wizard has three visible steps.

1. **Database Configuration** runs a read-only SELECT 1 on a temporary MySQL or MariaDB connection with a three-second PDO connection timeout. Only authorized operators can test connections. Continue uses the last verified configuration.
2. **Administrator Account** validates the name, normalized email and confirmed password. Back revokes verification while preserving non-sensitive database fields. Install acquires the private filesystem lock, rechecks authorization, opens the verified database again and acquires a database advisory lock before checking for an empty schema.
3. **Installation Complete** confirms the migrated schema and first Super Admin. The completing browser session can view this screen for ten minutes while operator authorization is still active. Continue to Login leads to a deliberate 503 sign-in handoff until Phase 3 supplies authentication.

## Operator authorization

On cPanel, have the hosting administrator configure Apache authentication over HTTPS before enabling setup. Protect the entire web root during setup using Directory Privacy or equivalent vhost rules so every rewritten installer route is covered. Store the password file outside public/. Configure PHP to receive the authenticated identity as the server variable REMOTE_USER. Set INSTALL_OPERATOR to that exact username and APP_URL to the canonical HTTPS origin. The application requires the identity, direct server HTTPS indication and canonical host on every installer request. Verify anonymous denial and authenticated access on the exact host before starting. If the host cannot supply a trusted identity and HTTPS indication, leave installation disabled. Never populate REMOTE_USER from forwarded headers or an HTTP header. Remove the temporary authentication rules and clear INSTALL_OPERATOR after successful setup.

On Windows, set INSTALL_LOCAL=true and APP_ENV=local, then run php artisan serve --host=127.0.0.1 --port=8000. Use localhost or 127.0.0.1 under one APP_URL. The local policy requires the CLI development server, a direct loopback peer, a loopback hostname and no forwarded identity headers. Complete each flow on one hostname because cookies are host scoped. Keep INSTALL_LOCAL=false in production.

## Private configuration and state

From an authenticated hosting terminal, prepare .env privately, run php artisan key:generate only if APP_KEY is missing, and clear any config cache with php artisan config:clear before installation. Set APP_DEBUG=false in production. Use a dedicated empty MySQL or MariaDB schema and a restricted account with DDL and required DML privileges on that schema. Review DB_SOCKET and TLS CA configuration. localhost can use the configured Unix socket; 127.0.0.1 uses TCP.

Installer sessions use private file storage with an encrypted payload and an isolated host-only cookie. Connection data is also encrypted before being placed in the session and expires after ten minutes. The installer never flashes credentials. Session-bound CSRF tokens and separate rate limits protect write endpoints. The installation POST uses only the last verified server-side credential snapshot.

Ensure the private installation directory, file session and file cache directories, and the .env parent directory permit the required writes. Restrict access to the PHP account. The installer backs out before migrations if it cannot read and prepare .env or if config is cached. It preserves unrelated environment entries and the existing APP_KEY, parses the candidate with Dotenv, and atomically replaces .env using a synced temporary file in the same directory. Ensure host-level DB variables do not override .env. Rebuild the config cache only after confirming the completed configuration.

## Recovery and backups

The installation directory contains a persistent flock file and the state marker. Do not delete or replace the lock file while PHP requests might be active. Before migrations the state becomes in-progress. MySQL and MariaDB can commit DDL implicitly, so partial schema changes may remain after interruption. Laravel runs migrations and checks required tables. The administrator is inserted in a transaction with a Laravel password hash and is_super_admin=true. Completion is recorded only after the administrator and environment file are persisted.

A missing .env after completion never reopens installation. Restore it from backup with the original APP_KEY. A corrupt or in-progress marker requires offline operator recovery. Stop web traffic, preserve the state directory, .env and database, and inspect migration history, required tables and the users table without exposing hashes or credentials. Prefer restoring a consistent backup. For a verified disposable failed installation, use a fresh dedicated schema and restore the pre-installation configuration and private state from backup while the application is offline. Never clear state against a populated database. When data must be retained, reconcile the schema, settings and existing administrator offline before restoring a verified completed marker. Back up the database, .env and private installation directory together and retain the marker across releases.
