# Secure installer specification

Status: planned Phase 1; no installer exists in inspected source. [Security](security.md) owns common security controls. [Operations](operations.md) owns deployment access/recovery.

## Exact user journey

| Step | Visible title | Outcome |
| --- | --- | --- |
| 01 | Welcome & Requirements | Actual runtime/deployment requirements; continue only on critical pass |
| 02 | Database Configuration | Validate approved MySQL settings; non-destructively test connection |
| 03 | Super Admin Account | Validate initial identity/password; role selected only by server |
| 04 | Complete Installation | Review safe facts, finalize, durably lock and redirect to Login |

No Setup Credential screen, installation password, generated one-time token, secret in URL, extra authorization step or automatic login. Infrastructure enforces access before the journey; an inaccessible request receives safe operator guidance, not a replacement credential form.

## Trust model and local aliases

All conditions must hold before any wizard route, probe or mutation:

1. Deployment configuration explicitly enables installation and selects a reviewed access profile. APP_ENV=local and APP_DEBUG are not authority. Unknown/missing profile denies.
2. Authoritative private installation state permits first initialization; missing/corrupt state on a provisioned deployment denies. Empty users is never evidence of permission.
3. Transport matches the profile and validated allowed origin; retain framework session/CSRF controls on every mutation.
4. Completion has not been durably recorded; every step checks again server-side.

**Direct local profile:** operator binds the application to loopback and explicitly authorizes local installation in server-controlled configuration. Validate the raw web-server connection peer (not proxy-resolved request IP) as exact IPv4 127.0.0.1 or IPv6 ::1. Normalize IPv4-mapped loopback only using a reviewed IP parser; never broad string prefixes. Validate request hostname against exact localhost or 127.0.0.1 and the configured installation port/scheme. Literal [::1] is optional only if explicitly enabled/tested; ::1 peer support does not authorize arbitrary IPv6 Host values.

Allow http://localhost:8000/install and http://127.0.0.1:8000/install for a verified authorized listener on port 8000. APP_URL remains canonical but either local alias works when APP_URL uses the other. Do not redirect between them. Reject suffixes (localhost.attacker), userinfo, malformed authorities, arbitrary loopback-looking domains and wrong ports. Host is routing validation, never authorization.

Reject unexpected Forwarded/X-Forwarded-*/X-Real-IP on the direct profile; stripping them alone does not establish safety. A local reverse proxy can make a remote request's raw peer appear loopback. Therefore loopback-peer checks are valid only with deployment assurance that no public/local forwarding gateway can reach the installer listener. An exposed localhost proxy invalidates this profile even without forwarded headers. The application cannot infer that topology from a request.

Docker/WSL/VM bridges commonly expose non-loopback peers or forward remote traffic. Default deny; use an explicitly reviewed restricted tunnel/direct loopback topology or the production access profile. Do not add bridge CIDRs, wildcard proxies or trust-all forwarded headers to make a screen work.

## Production access

Installation is disabled by default. Choose a deployment-controlled private network with enforced operator access, authenticated infrastructure gateway or restricted SSH tunnel. HTTPS is required for non-loopback transport. Gateway authentication must be applied to every installer path and method; firewall the origin against bypass. The edge strips client-supplied identity/forwarded fields and replaces only documented assertions; trust only exact reviewed proxy peers. The application does not accept an arbitrary identity header as authorization.

A restricted tunnel can carry browser traffic to an otherwise inaccessible listener; it is a distinct deployment-authorized profile, not an APP_ENV shortcut. Define edge/session expiry and access revocation. Public direct HTTPS plus a canonical hostname is insufficient. If the topology or gate cannot be verified, deny and instruct the operator to enable a private approved path. Details are D-03; production remains blocked until resolved.

## Sessions and CSRF

Host-only cookies (no shared Domain), HttpOnly, deliberate SameSite and Secure on HTTPS. Keep relative wizard links/form actions on the current validated local origin; do not force APP_URL during setup. Separate localhost and 127.0.0.1 sessions and CSRF tokens are expected. Switching host starts a separate session; never copy tokens or credentials between aliases. Server completion concurrency protects against both sessions racing.

Use Laravel CSRF and session rotation at consequential transitions; bind database verification to an installation attempt, session owner, settings digest and short expiry chosen/tested in Phase 1. Any new probe invalidates prior proof, including failed/invalid submissions. No browser storage for credentials; secret fields remain blank after errors.

## Lifecycle and durable state

Proposed state machine: unprovisioned → requirements_passed → database_verified → admin_ready → finalizing → completed; failures after writes enter recovery_required. Unknown/corrupt/mismatched state blocks all normal setup. Pre-database sessions/cache/state must not query unavailable application tables.

Requirements inspect actual PHP/extensions, key validity, private storage/locks, selected MySQL PDO/TLS capability and deployment profile. Do not require or regenerate a new key when one exists. Errors give actionable categories without connector messages/DSNs/passwords.

Database probe uses an allowlisted driver, approved hosts/ports, bounded timeout and no arbitrary URL/options. Check connectivity/version, target suitability and privileges without migrations or destructive writes. Prefer a fresh empty approved target. Existing data blocks installation with safe operator instructions; never drop/reset or claim unknown tables are disposable. A successful SELECT is not proof of DDL rights.

Completion acquires a cross-worker durable exclusive lock, revalidates all state and uses a server-generated attempt ID. Persist intent/checkpoints before irreversible stages. MySQL DDL is not transactionally rolled back with user creation: migrations are allowlisted and tracked, not blindly retried after partial failure. Create initial Super Admin and unique installation receipt in one database transaction where possible. Constraints/receipt plus lock prevent duplicate accounts across retries.

Persist selected credentials only through the reviewed encrypted configuration/secret owner; restrict access, never expose through config dumps or caches. After schema/account receipt and required configuration succeed, atomically persist durable completion. A database receipt and private completion/provisioned sentinel cross-check each other. A DB outage or missing marker on a provisioned deployment cannot reopen setup. Unknown state denies even if users table is empty.

## Recovery and limitations

No single transaction spans MySQL DDL, database receipt and filesystem/config. Crash tests must cover each boundary. After failure, keep the exact target/attempt/key; an operator inspects checkpoints and reconciles receipts before resuming. Partial DDL needs reviewed repair or matching backup restoration, not migrate:fresh. Account already committed means reuse its receipt; never create another Super Admin. A completed installation has no web reopen/reset mechanism.

Choose durable state, provisioned manifest and lock scope under D-02. Single-node private disk cannot safely coordinate independent ephemeral replicas. Filesystem atomic rename alone does not prove power-loss durability. Operator/host compromise and a misconfigured gateway are outside what application peer checks can solve. These limitations must remain visible in deployment acceptance.

Required executable cases are in [Testing](testing.md); none has been executed for this planned feature.
