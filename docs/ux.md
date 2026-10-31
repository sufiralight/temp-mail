# UX patterns

Status: normative planned interaction standards. [Scope](scope.md) selects features; [UI system](ui-system.md) owns presentation. Archived redesign orders and brand recipes do not apply.

## Screen brief and hierarchy

Before substantial screen work identify actor, trigger, primary outcome/action, required information, observed friction versus assumptions, and success/error/recovery. Include mobile/keyboard/no-JS paths. Use the simplest composition that serves the task; do not force every screen into a table, dashboard cards or two-column form. Never fill space with fake counts or future controls.

Installer prioritizes access safety, current step, verified state and next action. Authentication prioritizes credentials, requirements and recovery. Dashboards orient the actual role and lead to real tasks. Account settings group profile, password and appearance without mixing their submissions. These screen responsibilities are specified in their feature owners.

## Forms and feedback

Links navigate; buttons mutate. Give every field a visible label, appropriate autocomplete/type, concise requirements and associated errors. Allow paste/password managers; avoid repeated entry when safe to reuse. Preserve non-sensitive values after validation and leave secrets blank. Focus a useful error summary or first invalid field without stealing focus on ordinary updates; summary links target exact controls. Disclosures with errors open automatically.

Submission feedback distinguishes idle, submitting, confirmed saved and failed. Prevent accidental duplicates in the UI while preserving form payload; server idempotence/concurrency handles repeated requests. Restore usable state after network errors or history return. Never say saved/sent/installed before the appropriate confirmed result; accepted email transport is not confirmed inbox delivery. Use persistent near-task outcomes; optional toast must not be the sole error/success record. Status announcements are concise and non-repetitive.

Native navigation/forms are the baseline. Alpine enhances visibility, appearance, mobile navigation and relevant focus behavior. Do not require JS just to reveal login, installer or settings forms. No generic fetch router or modal framework. Password visibility controls preserve caret/value/focus and clearly state Show/Hide password.

## Navigation, recovery and state

Keep headings/actions aligned and current navigation clear. Use real URL subnavigation for settings. Mobile navigation must preserve action order, keyboard access and close/focus return without trapping focus in non-modal layouts. Dialogs need an accessible name, initial focus, constrained modal focus, Escape/cancel and return to trigger.

For the planned installer, explain database verification expiry and the required re-verification; never silently change target or claim progress survived a failed verification. Preserve safe inputs and provide a retry path for delivery/connection failures. Distinguish validation failure from unavailable service without revealing secrets.

Warn about meaningful unsaved settings edits when leaving if appropriate; avoid intrusive prompts for theme changes, search or untouched forms. Browser unload protection cannot guarantee crash/mobile recovery. Never persist credentials or sensitive drafts in browser storage. Independent forms must not erase each other's safe state.

Destructive future actions require consequence, affected scope, Cancel and server revalidation; prefer reversible workflows when applicable. Future record collections use server search/sort/pagination, preserved query context, explicit page-local selection and genuine-empty versus no-match feedback. Do not build those controls before a module is approved.

## Acceptance

Walk through a representative primary task and failure/retry on the actual rendered interface. Check retained context, next-action clarity and mobile/keyboard operation. Visual review and task-flow review are separate from backend correctness and do not equal a user study. [Testing](testing.md) records evidence; [Accessibility](accessibility.md) owns WCAG criteria.
