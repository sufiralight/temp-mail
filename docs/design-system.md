# Design system

Pure Zinc 16 v1.0 supplies the [semantic color tokens](color-system.md). Phase 1 should define spacing, corner radius, and elevation scales centrally alongside typography and motion. Prefer balanced spacing, subtle borders, restrained shadows, clean hierarchy, and responsive layouts. Define default, hover, focus-visible, selected, disabled, loading, and invalid states at the component level. Keep color and motion purposeful and avoid decorative gradients.

Inter is the primary UI font and JetBrains Mono is the monospace font. Self-host future WOFF2 assets with license notices and provide fallback stacks with appropriate script coverage. Support translated labels, text expansion, document language and direction, CSS logical properties, and selective mirroring of directional icons. Use a controlled Lucide SVG catalog and keep decorative icons hidden from assistive technology.

Maintain one canonical Light, Dark, or System preference. Resolve it early to avoid a flash of the wrong theme. System follows OS changes while active. Provide a labeled keyboard-accessible selector, fall back to System if preference storage is unavailable, and respect reduced motion. Theme persistence and initialization details belong to Phase 1.
