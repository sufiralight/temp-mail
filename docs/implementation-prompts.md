# Temp Mail Implementation Prompts

Run active prompts in order, one at a time, from the project root. Prompts 01–06 are the active `/install` milestone. Prompts 07–13 are drafts for later milestones and require a separate scope decision before execution. Copy the shared brief with each selected prompt when starting a new chat.

## Shared brief

> Read `AGENTS.md`, `docs/installation.md`, `docs/ux.md`, and the selected milestone before making changes. Inspect the existing application and verify the previous milestone's handoff. Implement only the selected milestone and reuse existing application services, Laravel Blade layouts, and Blade components. Keep HTML markup in Blade templates. Use English for UI copy, identifiers, and documentation. Preserve localhost and shared-hosting compatibility, responsive layouts, light and dark zinc themes, Google Fonts with a system fallback, and keyboard and screen-reader access. If an unresolved product or hosting decision blocks part of the work, complete the independent work and ask for the specific decision. Run targeted verification and report the changes, checks, remaining decisions, and next prompt number.

### Active scope · Installer

> Keep normal registration, sign-in functionality, the dashboard, and all mail features for later milestones. Installer verification may assert the redirect to `/login`; testing real sign-in belongs to the authentication milestone. Use disposable configurations and databases for installation, recovery, and replacement tests.

## 01 · Installation state and access

> Establish the `/install` entry point and a shared installation-state check. Distinguish a fresh deployment, a completed installation, and a completed installation with a missing `.env`. Keep completion evidence independent of `.env`. Preserve recovery access when configuration was cached and avoid requiring the application database to render the entry screen. Verify routing and the behavior when installation state is ambiguous. Record unresolved decisions about the database engine, persistent state, and operator authorization as handoff items. Complete only the work that does not depend on those decisions.

## 02 · Shared Blade installer interface

> Build the installer layout and reusable Blade components for labels, inputs, errors, status messages, step indicators, password visibility, and theme switching. Use Vercel for visual restraint, Stripe for form clarity, and Vite for a restrained accent. Keep **Database Host** and **Port** visible from the start, side by side where space allows and stacked on mobile. Create the screen structure for database setup, admin setup, and recovery. Verify both themes, narrow screens, keyboard order, visible focus, announcements, and the mobile form with the software keyboard open.

## 03 · First installation

> Resolve any outstanding database engine, persistent state, and operator authorization decisions before enabling setup actions. Implement **Test Connection & Continue** followed by **Create your admin account**. Require operator authorization before running connection tests or accepting setup submissions on a public deployment. Test the connection without changing the database or installed configuration. Require another test after connection details change. Validate the administrator form, prepare the database, create the first administrator, save configuration, and mark setup complete only after success. Keep credentials out of errors and logs and make retries and concurrent requests safe. Lock setup actions and verify the redirect to `/login` with the completion message from the installer specification. Verify unauthorized submissions, connection failure, interrupted setup, successful setup, and direct submission after the lock.

## 04 · Restore configuration

> Connect the recovery entry point from prompt 01 to **Restore configuration**. Reuse the existing database form and validation. Verify operator authority independently of `.env` before testing the submitted connection. Before writing, verify the existing installation and its database and require the configuration necessary for recovery. If any prerequisite fails, stay in recovery, make no writes, and explain the next safe operator action. Preserve existing data and keep administrator creation and database resets out of recovery. On success, relock configuration and hand off to `/login`. Verify cached configuration, unauthorized requests, missing or incorrect recovery inputs, and successful restoration.

## 05 · Start a new installation

> Add **Start a New Installation** as a separate action in the missing-`.env` state, available after operator verification. Use a confirmation screen to explain that the site will switch to a different database and the previous database will be preserved. Require a different, empty target before beginning a new attempt. Reuse the two installation steps and shared services. Activate the replacement only after successful setup and preserve the original recovery path until then. Verify that cancellation, failure, retries, and a successful switch preserve the old database and that the old database and other nonempty targets are rejected.

## 06 · Installer acceptance and hosting verification

> Exercise the complete installer on localhost and on a representative shared-hosting environment when one is available. Verify setup, configuration locking, automatic recovery, the recovery stop rule, and authorized replacement installation. Check application asset loading, protected configuration paths, and the browser flow without terminal access during setup. Test mobile and desktop, both themes, keyboard navigation, and screen-reader feedback. Fix in-scope defects and document any environment or device checks that could not be performed.

### Deferred scope · Drafts for later milestones

Membership, dashboard, temporary addresses, and mail are deferred. The inbound delivery draft requires a later scope decision. Sending or replying from temporary addresses and sending account emails are outside this plan.

## 07 · Registration, sign-in, and session access

> Implement ordinary member registration at `/register` and sign-in at `/login`, including sign-out and authenticated route protection. Use the shared Blade form components and English validation feedback. A public registration always creates a normal member. Keep administrator privileges server-controlled and retain the administrator created by the installer. Redirect successful sign-ins to `/dashboard` and preserve the installation-complete message. Verify the redirect as a handoff to prompt 08, along with registration, login, logout, guest access, and attempts to grant administrator privileges through a public form.

## 08 · Member dashboard and responsive shell

> Build the authenticated `/dashboard` and shared Blade navigation. Reuse the zinc themes, accessible theme control, and status components. Verify access for ordinary members and the redirect from sign-in. Add address and inbox entry points when the corresponding features are implemented in later prompts.

## 09 · Temporary addresses and lifecycle

> Confirm the supported mail domains, address allocation rules, and expiration policy before implementing behavior that depends on them. Implement address creation and display for authenticated members, a **Copy Address** control with accessible feedback, and the approved expiration behavior. Enforce address ownership and uniqueness server-side. Verify that another account cannot access an address and that expired addresses follow the agreed policy.

## 10 · Inbound mail delivery

> Confirm how mail reaches the application in local development and on the target shared host. Compare the host's supported inbound options with a provider webhook if needed, and get the deployment choice before adding a provider dependency or changing DNS. Implement the selected integration and a repeatable local verification path. Authenticate inbound requests or handoffs, resolve active recipient addresses, handle duplicate deliveries safely, and define message size and attachment handling before accepting those payloads. Verify valid, unauthorized, duplicate, expired-recipient, and oversized deliveries using test messages.

## 11 · Inbox and message view

> Connect stored mail to the member dashboard. Implement the message list and detail view with loading, empty, and error states. Enforce ownership on every message request. Present untrusted message content safely and preserve a usable plain-text view. On desktop, show the list and detail side by side where space allows. On mobile, use a separate message view with **Back to Inbox**. Verify access isolation, keyboard navigation, and handling of untrusted content.

## 12 · English error pages and UI consistency

> Build shared Blade error screens for `403`, `404`, `419`, `429`, `500`, and `503`. Preserve the correct status codes and show context-appropriate next actions. Keep internal diagnostics out of user-facing pages and make errors render even when data needed for regular navigation is unavailable. Review the installer, registration, login, dashboard, and inbox for consistent English copy, responsive behavior, both themes, focus, and form feedback.

## 13 · Release verification and deployment notes

> Verify the first-release journey from fresh installation to normal member registration, sign-in, address creation, real inbound delivery, and reading an inbox message. Recheck isolation between accounts, installer lockout, recovery, and separate replacement installation. Document the verified localhost and shared-hosting requirements, build and deploy steps, inbound mail setup, and configuration backup and recovery. Run the applicable automated and manual checks. Report any unverified hosting or mail-provider assumptions and the issues that block release.
