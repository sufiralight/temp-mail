# Accessibility standard

Status: WCAG 2.2 AA target for every future product screen; no conformance claim. [WCAG 2.2](https://www.w3.org/TR/WCAG22/) was consulted 2026-10-31. [Testing](testing.md) owns evidence and [UI system](ui-system.md) owns component presentation.

- Semantic landmarks, page title, one meaningful h1, ordered headings, skip navigation and correct document lang/dir. Prefer native elements before ARIA; no positive tabindex or clickable non-controls.
- Full keyboard operation and natural focus order; visible focus never obscured by sticky regions/dialogs. Modal focus stays inside and returns to its trigger. Escape closes appropriate overlays; no keyboard traps.
- Text contrast at least 4.5:1 (3:1 qualifying large text), necessary non-text/control indicators 3:1. Verify normal/hover/focus/error/selected states in both palettes; color alone never conveys status.
- Design controls around 44px targets; WCAG 2.2 AA minimum target criterion is 24 by 24 CSS px or its spacing/exceptions, not a blanket 44px conformance rule. Avoid dense adjacent tiny actions.
- Reflow at 320 CSS px, text resizing to 200% and browser zoom to 400% without lost functionality; necessary two-dimensional content has accessible containment. No clipped errors, hidden primary actions or fixed text boxes.
- Visible associated labels, instructions and errors; required state is textual/programmatic. Linked error summaries when useful. Preserve safe values, permit autofill/paste and avoid redundant entry. Accessible authentication must not rely on puzzles/cognitive tests without an accessible alternative.
- Meaningful icon-only names and accurate toggle/expanded/current state. Decorative icons hidden from assistive technology. Hover/focus content is dismissible, hoverable and persistent where required; tooltips never supply the only name/instruction.
- Polite status announcements for meaningful asynchronous outcomes; persistent errors, no excessive live-region chatter. Focus management must follow actual state, not arbitrary delays.
- Reduced motion, no essential animation or flashing; theme/font/storage failure must not block tasks. No reliance on text direction to infer permission or identity. Mixed Unicode content requires isolation.

Manual acceptance includes keyboard-only flows, representative screen-reader/browser pairing, zoom/reflow, contrast and both themes. Automated accessibility/lint scores and old screenshots are supporting evidence only. Record screen, viewport, input mode, assistive technology and known gaps. RTL readiness tests use isolated fixtures/pseudo-locales without claiming released translations.
