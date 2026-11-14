# Installation flow

The wizard has exactly three visible steps.

1. **Database Configuration** verifies MySQL or MariaDB connectivity server-side and only advances after verification. Support localhost with the host's configured socket behavior and 127.0.0.1 over TCP. Keep credentials out of URLs, logs, client storage, and error messages. Use a restricted database account with the grants required for installation.
2. **Administrator Account** requires verified installation state. Automatically run initial migrations after verification, before creating the first Super Admin. Serialize installation attempts and create the account and role assignment in a database transaction with uniqueness safeguards.
3. **Installation Complete** appears only after the account transaction and durable installation lock are confirmed. Further installer requests fail closed.

Use the same three visible steps on Windows loopback and Linux cPanel. On Windows verify both HTTP hostnames with one APP_URL and exercise each flow on a single hostname so session and CSRF state stay consistent. On cPanel require HTTPS and the configured production host. Verify that private configuration and durable state can be written with least privilege outside public/ on both systems. Fail closed if the required state cannot be persisted. Loopback access alone does not grant installer authorization.

Authorize a production operator before permitting any setup request, including database verification. The mechanism requires review in Phase 2. Generate the application key server-side and store configuration securely. Recheck authorization and state at each transition. Store installation evidence durably outside .env and the public web root. Missing .env on a previously installed system requires operator recovery and must never reopen installation.

Design for MySQL and MariaDB DDL implicit commits. Track migration progress separately from the account transaction and reconcile a crash between database commit and durable lock finalization. Serialize retries and resume only from authenticated, verified checkpoints without duplicate administrators. Treat ambiguous state as locked pending operator recovery. Test concurrency, interrupted writes, missing configuration, and each refused transition. No setup credential or manual application key screen belongs in the wizard.
