# Proposed Blade component APIs

These interfaces are proposals for Phase 1. Use anonymous Blade components by default, slots for content, and attribute bags for native attributes. Use semantic tokens for all component colors.

| Component | Proposed API and behavior |
|---|---|
| Button | variant, type, disabled, loading, default label slot. Keep the accessible name during loading. |
| Input | name, id, label, hint, error, native attributes. Associate hint and error IDs with the control. |
| Password field | Input API plus an accessible show/hide toggle with a state-aware label. |
| Select | Input labeling API plus an options slot for native options. |
| Card | Optional heading and footer slots and a default content slot. |
| Alert | tone, title and message slot. Use live announcements where urgency warrants. |
| Spinner | Accessible status label when standalone. Hide decorative spinners beside a status label. |
| Dialog | id, title, content and actions slots. Restore focus to the trigger after dismissal. |
| Navigation | Labeled navigation and item slots with href and current state. Expose aria-current. |
| Appearance control | Current preference and labeled Light, Dark, and System choices. |

See [accessibility](accessibility.md) and [design system](design-system.md).

Future appearance controls should use the shared `window.appearance` API documented in the [design system](design-system.md). The welcome page currently provides a verification control.
