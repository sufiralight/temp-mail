# Account settings specification

Status: proposed Phase 2 brief; no account settings exist; the installer foundation is Phase 1. Detailed field limits and verification policy need Phase 2 review. [Identity](authentication-authorization.md) owns authentication/roles; [Security](security.md) owns secret/session controls.

All authenticated roles, including unverified users, can access their own `/account`. Provide real URL subnavigation for Profile, Security and Appearance, server-allowlisted section values and current-page semantics. Render affected sections/errors predictably after validation and success. Independent mutations use independent forms/error bags; one submit cannot accidentally change unrelated security state.

## Profile

Allow updating the current user's display name: required Unicode text, maximum 255 characters, escaped on output. Server selects the owner; no user-ID/role/verified field from the client. Display current email and real verification status. Unverified users receive the verification notice/resend link. Email address is read-only in the baseline; a future verified address-change workflow needs explicit approval and its own threat model. The previous six-digit code feature is archived, not required for this phase.

Use initials as the simple avatar presentation. Uploaded photos and ten preset SVG galleries are deferred enhancements, not dependencies for account settings or dashboards. Do not install image tooling or create upload routes/tables.

## Security

Password change requires current password, a different new password, confirmation and Identity's password policy. Never flash/repopulate passwords. Hash using the configured framework hasher. After confirmed persistence, rotate remember credentials, revoke other sessions and regenerate the retained current session. Failed validation leaves the old password and sessions intact. Return persistent success feedback and blank secret fields. Rate-limit sensitive changes without preventing recovery.

No session-management UI, MFA, account deletion or role editor is implied. Do not add dead controls for them.

## Appearance

Reuse the single Light/Dark/System owner from [UI system](ui-system.md), including pre-paint behavior, live System changes and blocked-storage fallback. Initial preference is device/browser-local, not database account synchronization; clearly avoid promising cross-device persistence. Theme selection is the only allowlisted non-sensitive browser preference. Profile/security forms remain usable without JS; theme falls back to device preference with an explanation.

## Acceptance

Test self-only mutations, spoofed IDs/roles, Unicode/length validation, verification status/resend access, independent error routing, password/session revocation and native form submission. Inspect all sections in both palettes at mobile/desktop with keyboard and errors. Account changes must never require CMS, Images or Languages management.
