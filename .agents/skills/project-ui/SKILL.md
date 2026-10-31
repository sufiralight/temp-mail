---
name: project-ui
description: Build or update this project's Blade interfaces using Vite, Tailwind CSS, Alpine.js, and its accessible shared theme and icon contracts.
---

# Project UI

Read [AGENTS](../../../AGENTS.md) and [scope](../../../docs/scope.md) before implementation. This skill does not authorize pending product features.

Use [architecture](../../../docs/architecture.md) to assign responsibilities, [UI system](../../../docs/ui-system.md) for tokens and shared controls, [UX](../../../docs/ux.md) for flow behavior, and [accessibility](../../../docs/accessibility.md) for semantics and keyboard interaction.

Inspect existing components and bundled entries first. Extend the responsible Blade component and Alpine behavior rather than adding parallel styles or handlers. Check the actual rendered control in both palettes; verify accessible icon names, tooltips, focus, and state. Use empty credential fields for visibility previews.

Apply relevant [testing](../../../docs/testing.md) checks, record actual evidence and gaps, and update only the owning topic document. Resolve version or product choices in [decisions](../../../docs/decisions.md) before dependent implementation.
