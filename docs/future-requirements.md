# Future module architecture

Status: documentation only; no feature implementation authorized. [Scope](scope.md) owns phases.

## Public website

Homepage, Features, Pricing, FAQ, Contact, legal and localized SEO use common Blade layouts/tokens. Content must be approved/real; contact needs rate limiting, server validation and genuine delivery feedback. Legal/privacy/retention copy depends on provider/residency decisions. Do not expose placeholder checkout/navigation. Canonical/hreflang belongs to [Internationalization](internationalization.md).

## Multilingual CMS

Posts, Pages, Categories, Tags and Media share consistent authorized CRUD, active/inactive where appropriate, draft/published, soft deletion, Trash/Restore and separately authorized permanent deletion. Proposed normalized logical entity plus translation rows; localized fields include title/body/excerpt, slug, SEO, publication state/timing. Publication requires valid reviewed content; fallback is not a translation.

Server-side search, allowlisted filter/sort, bounded numbered pagination and real totals. Selection supports single/current-page select-all; show its scope explicitly. Any all-matching bulk operation needs a distinct explicit confirmation. Revalidate and authorize every ID; transactions where possible, report actual partial outcomes if external files prevent atomicity. Audit actor/action/target without storing sensitive bodies.

Localized slug auto-generates from title until manually edited. Show beneath title; compact expandable editor. Preserve manual override until confirmed regeneration. Separate dirty/manual state per locale; DB uniqueness scoped to locale/content route and race-safe validation. Taxonomy assignments cannot cross unauthorized locale relationships; removing taxonomy must not destroy posts. Restore rechecks slug conflicts and dependencies.

Media supports upload, browse, search, existing-image selection, metadata, alt/decorative/caption and authorized delivery. Validate MIME/signature/size/dimensions and bounded decoding/re-encoding; randomized names, no executable/SVG uploads by default. Private quarantine/scan decision before publish; metadata stripping and processing budgets selected later. Track references so shared content, revisions and Trash do not lose images on deletion. Explain usage before permanent deletion. Filesystem cleanup uses idempotent jobs/outbox, not assumptions of DB/file atomicity.

Editors preserve per-language unsaved state and prompt before losing work. Rich HTML/documents need bounded schema and server-side allowlist sanitation; no raw trusted client HTML. Pagination/search/picker, feedback and focus follow shared UI/accessibility contracts. Schema/editor package/revision retention remain later ADRs.

## Temporary email engine

**Laravel is not an SMTP receiving server.** Choose managed inbound provider or operated MTA separately under D-09. Infrastructure owns MX/DNS, SMTP acceptance/TLS, routing, spam/resource envelope and inbound delivery. Laravel owns authenticated ingestion, mailbox/message state, access checks, parsing jobs, expiration, API/UI responses and cleanup.

Proposed pipeline: approved domain/MX → bounded receiver → authenticated replay-safe ingest → private quarantine/object storage plus metadata receipt → idempotent parse/scan queue → authorized message availability → expiry tombstone → retryable object deletion. Do not acknowledge durable acceptance before payload/receipt durability. Duplicate provider IDs/content receipts must not create duplicate messages.

Domain lifecycle needs ownership/DNS verification, activation/disable and routing reconciliation. Generate unpredictable addresses and separate high-entropy access capability for anonymous mailboxes; an email address alone must not grant inbox access. Store hashed access secrets, support revocation and never log tokens. User mailbox ownership is explicit; every read/download checks owner/capability, expiry and entitlement server-side.

Limits cover mailbox creation per actor/network, inbound bytes, header count, MIME nesting/parts, attachment dimensions/count, parse CPU/memory/time, message/mailbox storage and retention. Defend parser bombs, SSRF, malicious links, tracking pixels and cross-mailbox access. Sanitize mail HTML and isolate rendering; block remote resources by default. Text-first fallback; attachments are untrusted private downloads with appropriate disposition and nosniff. No fetch of sender URLs.

Persist expires_at in UTC and deny access immediately on expiry even if cleanup lags. Scheduler enqueues bounded idempotent tombstone/object cleanup; retries/dead-letter handling and monitored backlog. Separate metadata retention/audit from raw-mail retention; delete capability/material and account for backups without promising instantaneous backup erasure.

Operational metrics: receiver rejects, queue latency/failures, parse budgets, storage pressure, cleanup lag, domain health and abuse trends, without recording message content. Define dead-letter inspection authorization, outage buffering/replay, provider retry contract and disaster recovery before launch. Receiver choice, region, TTL, attachment policy, anonymous ownership, quotas, transport (poll/SSE) and abuse escalation remain open. No outbound relay is assumed.

## SaaS billing

Plans map to versioned entitlements/usage limits, not client labels. Server checks current entitlement atomically when consuming scarce resources. Choose billing provider/currencies/tax/refund policy later. Prefer provider-hosted checkout/portal; no card data storage.

Verify webhook signature over raw body, bounded timestamp/replay window, event uniqueness, account/customer binding and legal state transitions. Handle duplicate/out-of-order deliveries, queue after durable receipt and reconcile with provider independently. Use integer minor units with explicit currency exponent or exact decimal policy, never float money. Audit operator changes; no privileged access from payment metadata alone. Define grace/cancel/refund/past-due and usage reconciliation before implementation.
