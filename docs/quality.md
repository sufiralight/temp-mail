# Quality and delivery gate

Status: Phase 0. [Testing](testing.md) owns cases/evidence; [Accessibility](accessibility.md) owns WCAG targets.

Documentation delivery requires inspected inputs, historical preservation, unique topic ownership, explicit planned-versus-observed statements, valid links, coherent dependencies and scope. Check no non-documentation files changed; never inherit previous test or Git claims. Do not install dependencies or build screens to validate a specification.

Later approved feature delivery requires independent functional/security evidence, rendered visual review and task/error/recovery walkthrough. A passing build/page render alone cannot prove usability, accessibility or production readiness. Reuse shared primitives, real data and working controls, preserve safe values and record limitations.

Phase 0 stops after the documented architecture. Phase 1 requires explicit approval. Before any commit ask “Git commit yapayım mı?” and wait for consent. Commit approval is not deployment/push/next-phase approval.
