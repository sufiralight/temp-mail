# Security

Own trust boundaries, policies, secret handling and first-run authorization. Validate server-side and keep private data out of client output and logs. Fail closed on ambiguous installation state.

Keep loopback allowances scoped to local development. Enforce the configured production host and trust forwarded headers only from explicitly configured proxies. Verify that cPanel serves public/ and that first-run installer authorization applies in both environments.

Test unauthorized access, validation failures and sensitive error handling.

Read [coding standards](../docs/coding-standards.md) and [installation flow](../docs/installation-flow.md) before changes.
