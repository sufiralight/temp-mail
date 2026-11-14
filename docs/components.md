# Blade UI components

Use `x-ui.*` components from `resources/views/components/ui/`. Attribute bags merge classes and forward native attributes to the actionable element or the outer element for presentational components. Pass booleans as bare attributes or bound booleans.

| Component | Public API |
|---|---|
| Button | `variant` primary, secondary, ghost or danger. `size` sm, md or lg. `type` defaults to button. Supports disabled, loading, leading-icon, trailing-icon, full-width and a label slot. |
| Icon button | Button variants, sizes, type and states, plus required `icon` and `label`. |
| Icon | `name`, `size` 16, 20 or 24, and optional `label` for a standalone meaningful icon. |
| Input | Common field props, `size`, `type`, `value`, readonly, and native attributes such as placeholder and autocomplete. Supported types are text, email, password, url, search, number, tel, date, time, datetime-local, month and week. |
| Password field | Input API with a fixed password type. The component does not render a preset value. |
| Textarea | Common field props, `size`, `rows`, `value`, readonly and native attributes. |
| Select | Common field props, `size`, and a slot containing native options or optgroups. Set selected options in the slot. |
| Checkbox and radio | Common field props, `value` and checked. Supply a meaningful label. Group radios by name and use a fieldset and legend. |
| Form field | Explicit `id`, `label`, `description`, `error`, required, and a control slot. Custom controls use the matching ID and add their own ARIA associations. |
| Card | Optional muted, header and footer slots, and a content slot. |
| Alert | `variant` info, success, warning or error, optional `title`, and description slot. Opt into live announcements with `live="polite"` or `live="assertive"`. |
| Badge | `variant` neutral, success, warning, error or info. Use text that communicates the state. |
| Spinner | `size` sm, md or lg, plus an optional `label` for a standalone status. Omit the label when nearby text already describes loading. |
| Modal | Unique `id`, `title`, optional dismiss-on-backdrop, a body slot and optional footer slot. |

Common field props are `name`, `id`, `label`, `description`, `error`, required and disabled. Omitted IDs are generated per field. With a name, fields read the default Laravel validation bag unless an explicit error is supplied. External `aria-describedby` references are preserved and combined with field help and errors. Use `aria-label` or `aria-labelledby` for controls whose label is supplied externally.

```blade
<x-ui.input name="email" type="email" label="Email" description="Use your work address."
    autocomplete="email" required />
<x-ui.button type="submit" leading-icon="check">Save</x-ui.button>
<x-ui.icon-button icon="trash-2" label="Remove item" variant="danger" />

<x-ui.button data-modal-open="details" aria-haspopup="dialog" disabled>Open details</x-ui.button>
<x-ui.modal id="details" title="Details">
    <p>Review the information before continuing.</p>
    <x-slot:footer><x-ui.button data-modal-close>Done</x-ui.button></x-slot:footer>
</x-ui.modal>
```

The Vite entry activates declared dialog triggers after connecting their dialogs. Start triggers disabled for progressive enhancement. Escape uses native dismissal. The dialog focuses its close button when opened and restores focus to the connected trigger after closing. Backdrop dismissal is opt-in and requires both press and click outside the panel.

A loading button renders disabled with `aria-busy` and preserves its label. For asynchronous forms, set the loading state after accepting the first submission and enforce duplicate protection on the server where required. Password visibility preserves the native input and uses a stable accessible toggle name with `aria-pressed`.

The Lucide 0.468.0 catalog is explicitly allowlisted and includes arrow-right, check, circle-alert, circle-check, eye, eye-off, info, plus, search, trash-2, triangle-alert and x. Icons render from local Blade partials with currentColor, and unknown names and sizes raise an exception. [Lucide license](https://github.com/lucide-icons/lucide/blob/0.468.0/LICENSE)

The local verification page is at `/_dev/components`. It requires Laravel's local environment and a loopback socket peer. Use the page to exercise both themes, validation, native form controls and dialog behavior. See [accessibility](accessibility.md).
