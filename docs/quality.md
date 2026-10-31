# Quality and delivery gate

Status: approved Phase 1. [Testing](testing.md) owns cases/evidence; [Accessibility](accessibility.md) owns WCAG targets.

Documentation delivery requires inspected inputs, historical preservation, unique topic ownership, explicit planned-versus-observed statements, valid links, coherent dependencies and scope. For documentation-only tasks check no non-documentation files changed; never inherit previous test or Git claims. Do not install dependencies or build screens to validate a specification.

Later approved feature delivery requires independent functional/security evidence, rendered visual review and task/error/recovery walkthrough. A passing build/page render alone cannot prove usability, accessibility or production readiness. Reuse shared primitives, real data and working controls, preserve safe values and record limitations.

Phase 1 is approved and stops after the installer/foundation. Phase 2 requires a new explicit request. Before any commit ask “Git commit yapayım mı?” and wait for consent. Commit approval is not deployment/push/next-phase approval.
