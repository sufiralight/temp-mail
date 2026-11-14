# Design system

[Pure Zinc 16 v1.0](color-system.md) supplies the semantic palette. The shared UI library uses semantic tokens and centralized component styles in resources/css/components.css. See the [component APIs](components.md).

## Typography

Tailwind provides `font-sans` for Inter and `font-mono` for JetBrains Mono with system fallbacks. Use `text-body`, `text-small`, `text-label`, `text-heading`, and `text-technical` for size, line height and tracking. Pair technical text with `font-mono`. Labels and headings set their own weight.

The bundled upright variable WOFF2 files come from Fontsource 5.3.0 with SIL Open Font License notices in `public/licenses/`. They use `font-display: swap`. The Latin subsets cover basic Latin with selected additional characters and symbols. Other scripts, including Arabic and Hebrew, and missing Latin Extended characters use system fallback. Review script coverage, translated text expansion, document language and direction, logical properties, and directional icon mirroring before adding locales.

## Appearance

Blade inlines the trusted local `resources/js/appearance.js` in the head before Vite styles. Keep this file in the deployed application source. It reads `temp-mail.appearance` from localStorage and sets `html[data-theme]` to the resolved `light` or `dark` value. The storage values are `light`, `dark`, and `system`. Missing, invalid, or inaccessible preferences use System. Storage exceptions leave new choices usable in memory. Preferences are per browser origin.

Use `window.appearance.getPreference()` to read the selected mode and `window.appearance.setPreference(value)` to apply and persist it. System reacts to OS changes. CSS uses the OS preference when JavaScript is unavailable. The welcome preview uses a native select and changes appearance without moving focus or animating the palette.
