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

## Layout primitives

| Component | API |
|---|---|
| Container | size sm, md, lg (default), xl or full. Centers content with responsive inline gutters. |
| Stack | direction vertical (default), horizontal or responsive. Gap none, sm, md, lg or xl. Align stretch, start, center or end. Justify start, center, end or between. Optional wrap for rows. |
| Grid | columns 1–4, default 2. Same gap and align options as stack. Starts at one column, uses two from md and reaches three or four from lg when requested. |
| Section | spacing none, sm, md or lg. Optional heading and description slots. Supply a heading at the appropriate level and reference its ID with aria-labelledby where a named region helps. |
| Page header | title, optional description, breadcrumbs and actions slots. Renders the page h1. Supply a labeled navigation element in the breadcrumbs slot. |
| Divider | orientation horizontal or vertical. Optional decorative flag hides the separator from assistive technology. |
| Empty state | title, optional description and allowlisted icon, heading-level 2–4, and primary and secondary action slots. |
| Skeleton | shape text, rectangle or circle. Size sm, md or lg. Text uses heights 0.75/1/1.25rem, rectangles 3/5/8rem, circles 2/3/4rem. Use classes to match the final content footprint. Decorative by default. |
| Sr-only | A span applying Tailwind's sr-only utility to its content. |
| Surface | Generic bordered surface with optional muted treatment. The caller sets padding. Use card for built-in padding and structured header and footer slots. |

Use a small container and a vertical stack for narrow forms, a default container and sections for standard pages, and xl or full for wide data pages. Pair a grid with cards for columns. Use an empty state inside a surface for centered feedback. For loading, mark the content region busy and provide a textual status alongside decorative skeletons. Wrap horizontal action groups or use a responsive stack.

```blade
<x-ui.container size="lg">
    <x-ui.page-header title="Records">
        <x-slot:actions><x-ui.button>Add record</x-ui.button></x-slot:actions>
    </x-ui.page-header>
    <x-ui.section>
        <x-ui.grid columns="2">
            <x-ui.card>Recent records</x-ui.card>
            <x-ui.surface class="p-5"><x-ui.empty-state title="No saved records" /></x-ui.surface>
        </x-ui.grid>
    </x-ui.section>
</x-ui.container>
```
