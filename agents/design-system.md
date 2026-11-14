# Design system

Own the approved palette and shared appearance preference. Use a controlled SVG catalog and licensed self-hosted fonts. Keep preference values constrained to the three supported modes.

Check text, controls and focus contrast in both themes and verify reduced motion and OS preference changes.

Read [design system](../docs/design-system.md) and [color system](../docs/color-system.md) before changes.

Use the `--pz-*` primitives through the semantic Tailwind aliases in `resources/css/app.css`. Treat `html[data-theme]` as the resolved scheme and use the shared API for preference changes.

Use control-boundary for essential interactive outlines and keep border and border-strong for decorative separators. Follow the contrast rationale in the accessibility guide.
