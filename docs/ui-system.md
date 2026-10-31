# Premium UI and Blade component specification

Status: Phase 1 implements app/installation layouts, labels/inputs/password fields, buttons/cards/alerts, progress, validation, loading and the shared theme control. Other component families remain plans. [UX](ux.md) owns interactions; [Accessibility](accessibility.md) owns WCAG targets. Benchmark Stripe/Vercel/Linear quality without copying branding.

## Semantic tokens

Future single owner: resources/css/app.css, mapped to Tailwind utilities. No page-level literal palettes or second CSS system. Candidates below require rendered contrast measurement.

| Color token | Light | Dark |
| --- | --- | --- |
| canvas | #F8FAFC | #0B1215 |
| surface | #FFFFFF | #111D22 |
| elevated | #F1F5F9 | #182A30 |
| text | #0F172A | #F1F5F9 |
| secondary | #475569 | #CBD5E1 |
| muted | #526176 | #A8B8C5 |
| border decorative | #DCE4E9 | #344952 |
| border control | #64748B | #8096A0 |
| accent / hover | #0F766E / #115E59 | #5EEAD4 / #99F6E4 |
| on-accent | #FFFFFF | #042F2E |
| focus ring | #0F766E | #5EEAD4 |
| success / subtle | #166534 / #F0FDF4 | #86EFAC / #10291F |
| warning / subtle | #854D0E / #FEFCE8 | #FDE047 / #302714 |
| error / subtle | #B91C1C / #FEF2F2 | #FCA5A5 / #35191F |

| Scale | Candidate tokens |
| --- | --- |
| Spacing | 0, 4, 8, 12, 16, 24, 32, 48, 64, 96px; logical properties |
| Typography | Inter 400/500/600/700, system/script fallbacks; body 16/24, support/label 14/20, h3 18/26, h2 24/32, h1 32/40, display 40/48px |
| Radii | Control 8px, panel 12px, dialog 16px; pill only for semantic badges |
| Shadow | Small 0 1px 2px rgb(15 23 42 / .06); elevated 0 8px 24px rgb(15 23 42 / .12), dark uses subtle border plus restrained shadow |
| Focus | 2px outline, 2px offset; never clipped or hidden |
| Motion | Fast 120ms, standard 180ms ease-out; reduced-motion removes nonessential transitions |
| Breakpoints | sm 640, md 768, lg 1024, xl 1280, 2xl 1536px; mobile first, verify 320px |
| Layout | Main max 1280px; auth 448px, installer 640px, settings 896px; page padding 16px mobile, 24–32px desktop |
| Targets | Design around 44px controls; WCAG minimum and exceptions verified separately |

Quiet neutral surfaces, teal active actions, subtle elevation, no excessive gradients/clutter. One primary action per task. Inter Variable is bundled locally through @fontsource-variable/inter (OFL license in the package); no third-party font requests. Script fallback remains required.

## Blade contracts (planned component names, not existing files)

Small explicit props, escaped text, class/attribute merging, stable unique IDs, no permission decisions/queries inside presentation. Slots contain trusted application markup; untrusted rich text requires dedicated sanitization.

| Family | API/behavior contract |
| --- | --- |
| button, icon-button | Variant/size/type, real disabled/busy; native link for navigation; icon button requires accessible name |
| input, password-input, select | Name/id/value/error/help/autocomplete; label association; never repopulate secrets; password toggle preserves value/caret and names state |
| checkbox, radio-group | Native semantics, fieldset/legend, group error and checked state |
| dropdown | Disclosure/menu only when appropriate; expanded state, keyboard navigation and focus return |
| card, page-header | Optional heading/actions slots; avoid wrapping every field; one page h1 |
| table, pagination | Semantic headers, responsive containment, actual numbered server pagination and current page |
| modal | Name/description, initial focus, modal containment, Escape/cancel and return |
| toast, alert | Severity and persistent outcome; polite status; toast never sole error |
| badge | Text label as well as color; genuine state |
| tabs | URL navigation for page sections; ARIA tab behavior only for local panels with arrow keys |
| breadcrumbs, navigation | Named landmarks, current location, real destinations, keyboard/mobile disclosure |
| empty-state, loading-state, error-state | Actual task state and working recovery; aria-busy/status where appropriate |
| theme-switcher, icon, tooltip | Shared owner and icon-only segments below; supplementary dismissible hover/focus help |
| document-head, layouts | One font/meta/theme/Vite/CSP owner; public/install/auth/user/admin shells compose same primitives |

Tables/CMS/modal families are planned now, built only when approved screens need them. Avoid a premature generic UI framework. One local SVG stroke family, decorative icons aria-hidden; no arbitrary uploaded SVG.

## Single Light / Dark / System owner

**Compact permanently segmented icon-only selector:** Sun=Light, Moon=Dark, Monitor=System. No visible Light/Dark/System text inside the control, no labelled dropdown. Teal selected segment with shape/state beyond color. Same reusable Blade component in installer, auth, public, user and admin shells.

Implemented semantics: three native radio inputs grouped by a visually hidden legend; icon labels and visually hidden choice names, native checked state. Tab enters/exits group, arrow keys move/select, Space selects; verify direction-aware order in RTL. Icons are decorative; accessible names remain Light, Dark, System. Tooltip appears on hover/focus outside the control, dismisses with Escape and remains hoverable; not the sole accessible name.

One resolver stores allowlisted light/dark/system browser preference; missing/invalid defaults to System. An early small CSP-compatible bootstrap before stylesheet paint sets the resolved root theme/color-scheme. Main Alpine behavior uses the same resolver; no independent per-component logic. The early resolver is nonce-protected; bundled Alpine uses its CSP build. Listen to OS changes only in System; storage events synchronize same-origin tabs and all controls. Catch storage errors, preserve in-memory operation, never persist sensitive inputs.

Without JS use prefers-color-scheme CSS and hide inactive enhancement controls with an accessible explanation; core forms stay usable. Storage is origin-local: localhost and 127.0.0.1 do not share it; account synchronization is not promised. Verify initial paint, reload, live OS changes, explicit override, corruption, blocked storage, multiple controls/tabs and CSP. No no-flash or contrast claim until browser evidence exists.
