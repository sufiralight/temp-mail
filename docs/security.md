# Security requirements

Status: Phase 1 installer controls implemented; later identity/CMS/mail/billing controls remain specifications, not a security certification. [Installation](installation.md) uniquely owns installer transport/state; [Testing](testing.md) owns executable acceptance.

| Boundary | Enforceable requirement | Required evidence |
| --- | --- | --- |
| Authentication | Laravel hashing/broker/signed verification; neutral failures, per-actor/network throttle and recovery | Fixation, enumeration, expired/replayed reset and delivery failure |
| Authorization | Policies/Gates/middleware and ownership on every endpoint; allowlisted fields; default User | Direct requests for all roles, forged IDs/roles and unknown role |
| Sessions | Rotate on login; POST CSRF logout invalidates session/token; deliberate revocation on password change/reset | Multiple browsers/remember-me, retained vs revoked sessions |
| CSRF | Framework protection on mutations, including installer; correct same-origin host-only cookies | Real HTTP missing/invalid token, cross-origin submissions |
| XSS | Escaped Blade and translated output; no raw arbitrary HTML/x-html; vetted rich content sanitizer | Stored/reflected payloads, attribute/URL contexts |
| SQL injection | Bound Eloquent/query values; allowlisted column/sort names; no arbitrary DSN | Adversarial query/filter/DB-probe inputs |
| Rate limits | Bounded probes/login/register/reset/resend/ingest/creation/download; actor/network keys and resource budgets | Exhaustion/retry/backoff without easy global lockout |
| Uploads/mail | Private isolation, MIME/signature/decode limits, sanitization, authorized downloads, remote resources blocked | Polyglots, bombs, traversal, malicious HTML, cross-mailbox access |
| Billing | Raw-body signature, timestamp/replay, unique event receipt, state reconciliation | Tampering, duplicate/out-of-order/cross-account events |
| Installer | Deployment gate independent of empty users; verified peer and aliases; durable receipt/lock | Full matrix in Testing; no secret/credential UI |
| Operations | Restricted public root, least privilege, patched dependencies, backup/key custody and incident drills | Staging deployment/restore and access review |

## Secrets, privacy and logging

Use Laravel cryptography; no custom hashing/encryption. Privately provision and preserve APP_KEY. DB passwords/tokens/session IDs/mail contents never enter source, logs, URLs, analytics, browser storage, flash input or error dumps. Redact before logging and use bounded structured event IDs/categories; audit actor/action/target, not sensitive payloads. Test success and failure paths for leakage.

Narrow exception: framework emailed reset/signed verification URLs necessarily carry token/signature material. Generate from a trusted canonical HTTPS origin; redact route/query material at app/proxy/provider, set no-referrer/no-store and avoid third-party assets/instrumentation on these flows. Reset tokens are hashed at rest, expiring and single-use. Never extend this exception to installer secrets.

Cookies are HttpOnly, Secure under HTTPS, host-only with a deliberate SameSite policy. Session cookies are not shared between local aliases. Server validation and authorization remain mandatory with or without JS. Public registration/profile cannot set role, verified state, ownership or installation flags.

## Browser and infrastructure

Plan CSP with self/nonce or hashes, no blanket unsafe-eval/unsafe-inline, constrained script/style/connect/img/font/frame/form/base/object sources. Resolve Alpine build and early theme bootstrap under D-04 before claiming CSP compatibility. Fonts preferably self-hosted; if an external source is approved, constrain it explicitly. Production must not include HMR/public hot state.

Apply nosniff, framing denial, deliberate referrer policy and private/no-store for sensitive responses including errors. HSTS only after HTTPS/domain/subdomain review. Validate trusted hosts/proxies; never trust wildcard proxy headers for installer access. APP_DEBUG=false in production; error messages are safe categories, not connector exceptions.

Only public/ is served. Environment/state/config caches/logs/backups/quarantine and mail stay private. Database least privilege and separate migration/runtime privileges where deployment permits. No arbitrary user-controlled paths, destination hosts or network fetch. Data ownership is explicit even without a tenant model; tenant support requires its own future design.

## Incident handling

Operator can disable ingestion/public registration or revoke compromised sessions/capabilities without reopening installer. Isolate affected resources, preserve minimal restricted audit evidence, rotate compromised provider credentials, restore matching DB/state/key backups and reconcile jobs/webhooks. Key rotation requires a reviewed data re-encryption/recovery plan. Define incident contacts, retention and notification duties with deployment jurisdiction; no legal compliance claim is made here.

Phase 1 selects the approved single-node/tunnel strategy. An explicit two-hour operator-provisioned access profile and raw loopback peer check complement deployment isolation; they do not prove an SSH tunnel/firewall exists. Production setup remains disabled until provider/SSH/listener restrictions are confirmed. TLS certificate verification cannot be disabled by submitted fields or the production environment. Initial account uses Argon2id; role is fixed server-side, not client-selectable.

Sensitive installer exceptions are rendered as translated safe categories without raw exception chains and are not reported with credential-bearing context. Config contains only the opaque installed driver; decrypted DB configuration exists in the private encrypted state and connection object, never a config dump. Password inputs are excluded from trimming/flashing. Error responses get no-store, CSP, framing denial, nosniff and no-referrer.
