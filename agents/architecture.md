# Architecture

Keep HTTP orchestration, domain workflows and persistence separate. Use Laravel's conventional directories and keep authorization at request and resource boundaries.

Verify success, denial, failure and retry paths for workflows that span writes.

Read [architecture](../docs/architecture.md) and [coding standards](../docs/coding-standards.md) before changes.
