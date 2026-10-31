# Deployment and operations considerations

Status: planning only. No deployment/provider/resources or runtime changes authorized in Phase 0.

## Foundation gates

Choose D-01 MySQL and D-02 persistent topology/state before coding installer. Confirm supported PHP extensions and real Node/frontend compatibility, lock dependencies, separate build/runtime credentials. Serve only public/, provision APP_KEY once and keep restricted backups. Do not use scaffold composer setup blindly: it generates a key and runs migrations.

Private development opt-in plus verified direct loopback permits both local aliases as specified in [Installation](installation.md). Production requires an authenticated infrastructure gateway, private operator network or restricted tunnel, with no origin bypass. Public HTTPS/canonical host/empty users do not authorize installation. If access cannot be verified, block with operator guidance. No install:authorize or install:seal command exists; historical commands must not be invoked.

Single persistent node is a proposal, not a universal deployment promise. Ephemeral/replicated/Cloud environments need durable shared state, locks, sessions and private object storage. A node-local lock cannot coordinate replicas; do not enable installation until topology is qualified. Docker/WSL bridge access does not qualify itself as loopback.

## Build and release

Future pipeline: inspect dependency advisories, install pinned dependencies, compile production assets without HMR, run isolated test/format/build checks, package immutable artifacts and keep secrets outside repository. Revalidate actual versions at implementation. No package/build command was run for Phase 0.

Separate first installation migrations from subsequent approved release migrations. Take verified backups; use reviewed expand/contract changes and a dedicated migration owner. Rollback code does not undo destructive schema/data changes. Configuration/route/view caches must use the correct environment and preserve private secret handling; test reload behavior and never expose decrypted installer DB passwords in diagnostics.

Long-running workers require controlled restart with release; scheduler has one durable execution owner/lock. Distinct web, ingestion, parsing and cleanup workloads have budgets, retries, timeouts, idempotency and dead-letter recovery. Select queue/lock backends after hosting review. External receiving service/MTA remains operationally separate from Laravel web.

## Backup and recovery

Back up matching database, private installation/checkpoint/config state, APP_KEY and required object store metadata/content with encryption, least privilege and a defined retention policy. Keep key custody separate but recoverable. Rehearse restoration in isolation and record achievable RPO/RTO before production. Never delete completion state or change APP_KEY to fix installation.

A crash after partial MySQL DDL needs operator-reviewed same-target recovery. A committed installation receipt must prevent duplicate Super Admin even if final files failed. A completed DB outage fails closed. A redeploy or scaling event cannot reset the provisioned manifest. Define exact state owner and atomicity limits under D-02.

## Observability and release limits

Measure request errors/latency, queue backlog/failures, receiver/parse rejects, DB/storage pressure, cleanup lag, installation lock state and billing reconciliation. No secrets/raw message bodies in metrics or trace payloads. Health endpoints expose only safe health categories; distinguish liveness/readiness without public infrastructure details.

Define alert routing, abuse operations, incident owner, provider retry/outage contract, recovery access and resource ceilings. No real SMTP, MX/DNS, queue worker, scheduler, MySQL integration, production TLS, restore or gateway was verified in Phase 0.
