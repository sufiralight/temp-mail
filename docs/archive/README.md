# Historical archive — not active instructions

Snapshot date: 2026-10-31, before Phase 0 reconstruction. [Download original files](legacy-2026-10-31.zip); [per-file SHA-256/byte manifest](manifest.json). The archive contains all 30 original docs files, the pre-edit root AGENTS.md/CLAUDE.md and existing project-ui skill: 33 files total. ZIP entries preserve their original relative paths and exact bytes. Original files were verified against the archive before replacing active documentation.

The ZIP is deliberately a non-active historical snapshot: saved role prompts, old setup commands and implementation claims are not agent instructions. Its original relative links remain original context; some refer to old source/tests/assets absent from the rebuild. Extract for historical reading only, never overlay it onto the project or run its commands. Active documentation links must resolve against the current tree instead.

## Reading guide

| Historical entries | Useful context retained | Current authority |
| --- | --- | --- |
| docs/scope.md, architecture.md, decisions.md, operations.md | Old system topology, class/table choices, old setup and recovery history | [Scope](../scope.md), [Architecture](../architecture.md), [Decisions](../decisions.md), [Operations](../operations.md) |
| docs/security.md, account.md | Secret isolation, authorization, session revocation, old email-code/photo workflows | [Security](../security.md), [Identity](../authentication-authorization.md), [Account](../account.md) |
| docs/ui-system.md, ux.md, accessibility.md, public-ui.md, design-audit.md | Shared identity, task-first UX, accessibility and competing redesign history | [UI system](../ui-system.md), [UX](../ux.md), [Accessibility](../accessibility.md) |
| docs/internationalization.md, languages.md | Catalog, direction, fallback/completeness and override provenance | [Internationalization](../internationalization.md), [Future requirements](../future-requirements.md) |
| docs/blog.md, pages.md, images.md, content-translations.md, cms-completion.md | CMS/media/localized-state invariants and previous audit/completion narrative | [Future requirements](../future-requirements.md) |
| docs/testing.md, quality.md | Dated test/audit counts, fixtures, gaps and independent acceptance checks | [Testing](../testing.md), [Quality](../quality.md) |
| docs/prompts/* (10 entries including README) | Nine saved requests and their historical index | [Scope](../scope.md); no archived request grants current scope |
| AGENTS.md, CLAUDE.md, .agents/skills/project-ui/SKILL.md | Prior agent authority and UI workflow | [Canonical AGENTS](../../AGENTS.md) |

Prior test counts, package versions, screenshots, localhost paths, OPcache settings and migration reports are provenance only. They are not verification of the new scaffold or mandates to recreate old architecture. [Phase 0 audit](../phase-0-audit.md) records what was superseded, preserved and deferred.

## Pre-Master Prompt 01 snapshot

[pre-master-01-2026-10-31.zip](pre-master-01-2026-10-31.zip) preserves the 23 current input root/docs/project-UI files before this reconciliation; [pre-master-01-manifest.json](pre-master-01-manifest.json) records exact bytes and SHA-256. This is separate from the older 33-file archive, which remains unchanged. Both snapshots are historical and non-executable. Claims of prior Git/test/Phase 1 completion are not verified by preserving them.
