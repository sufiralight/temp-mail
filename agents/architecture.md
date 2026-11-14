# Architecture

Keep HTTP orchestration, domain workflows and persistence separate. Use Laravel's conventional directories and keep authorization at request and resource boundaries.

Treat Windows localhost and Linux cPanel as first-class targets. Keep paths environment-aware and use Laravel-native filesystem, session, cache and queue abstractions. Review new dependencies and workflows against shared-hosting limits, including cron and request timeouts.

Verify success, denial, failure and retry paths for workflows that span writes.

Read [architecture](../docs/architecture.md) and [coding standards](../docs/coding-standards.md) before changes.
