# Accessibility

Target WCAG 2.2 AA. Use native controls and semantic landmarks, logical headings, and a skip link where repeated navigation warrants one. Verify keyboard operation, tab order, visible and unobscured focus, and focus restoration for dialogs. Verify touch targets against the AA minimum and spacing exceptions. Check responsive reflow and zoom in both themes and test right-to-left layouts.

Require 4.5:1 for normal text, 3:1 for large text, and 3:1 for applicable control boundaries, states, and meaningful graphics. Give controls visible associated labels and connect field hints and errors programmatically. Put text next to status colors and announce asynchronous feedback where appropriate. Respect reduced motion and preserve progress information when animation is suppressed.

Pair automated accessibility scans with manual keyboard, screen reader, contrast, and responsive checks.

The Phase 1.1 preview uses a visible label, native keyboard selection, and a two-pixel focus outline. Theme changes are immediate and motion-free. The page follows the OS appearance in CSS when scripts are unavailable. Decorative panel borders can have lower contrast than interactive control boundaries. Evaluate status colors against the actual background before using them for text or essential graphics.

Calculated contrast ratios for the preview (light / dark) are 16.97 / 19.06 for foreground on canvas, 7.73 / 6.91 for muted foreground on surface, 17.72 / 16.97 for primary foreground on primary, and 10.44 / 11.99 for the focus ring on surface. The approved border-strong token reaches 1.48 / 2.29 against surface. Use a higher-contrast semantic color when a control boundary must reach 3:1. The preview select uses the primary fill to establish its boundary.
