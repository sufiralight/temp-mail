# Pure Zinc 16 — Official Color System v1.0

Owner approved on 2026-11-14. New official primitives require owner approval.

| Color | HEX |
|---|---|
| Zinc 50 | #FAFAFA |
| Zinc 100 | #F4F4F5 |
| Zinc 200 | #E4E4E7 |
| Zinc 300 | #D4D4D8 |
| Zinc 400 | #A1A1AA |
| Zinc 500 | #71717A |
| Zinc 600 | #52525B |
| Zinc 700 | #3F3F46 |
| Zinc 800 | #27272A |
| Zinc 900 | #18181B |
| Zinc 950 | #09090B |
| White | #FFFFFF |
| Success | #16A34A |
| Warning | #D97706 |
| Error | #DC2626 |
| Info | #2563EB |

| Token | Light | Dark |
|---|---|---|
| canvas | Zinc 50 | Zinc 950 |
| surface | White | Zinc 900 |
| surface-muted | Zinc 100 | Zinc 800 |
| foreground | Zinc 900 | Zinc 50 |
| foreground-muted | Zinc 600 | Zinc 400 |
| border | Zinc 200 | Zinc 700 |
| border-strong | Zinc 300 | Zinc 600 |
| primary | Zinc 900 | Zinc 50 |
| primary-foreground | White | Zinc 900 |
| hover | Zinc 100 | Zinc 800 |
| selected | Zinc 200 | Zinc 700 |
| focus-ring | Zinc 700 | Zinc 300 |

Reusable components must consume semantic tokens instead of hardcoded Zinc, Slate, Gray, black, white, or arbitrary color utilities. Reserve status primitives for meaningful states. Verify foreground and muted text on canvas, surfaces, hover and selected backgrounds, primary foreground on primary, focus rings against adjacent colors, and essential boundaries against surrounding surfaces in both themes. Verify each status color in its intended role and pair it with accessible text or symbols. See [accessibility](accessibility.md).

In code, `resources/css/app.css` defines primitive values once as `--pz-*`. Semantic custom properties use `light-dark()` with the approved pairs. The root `color-scheme` follows the OS by default and `html[data-theme]` applies the resolved scheme. Tailwind 4 `@theme inline` aliases these properties as `--color-*`, enabling utilities such as `bg-canvas` and `text-foreground-muted`. Status utilities reference the corresponding primitives directly.
