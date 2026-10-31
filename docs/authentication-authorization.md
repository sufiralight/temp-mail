# Authentication and authorization specification

Status: planned Phase 2; Phase 1 now has a role enum/default User and installer-only Super Admin creation, but no full identity implementation. All behavior below is planned. Mandatory dashboard verification is a recommendation pending D-06, not a newly approved product requirement. Use Laravel session identity, password broker and signed verification primitives; Blade owns screens. [Decisions](decisions.md) records choices; [Security](security.md) owns common controls.

## Authentication lifecycle

- Registration is public only after installation. Accept name, email, password and confirmation; assign User on the server, never a submitted role/verified flag. Enforce database email uniqueness under races. Define consistent email normalization and duplicate behavior before implementation (D-05). Do not copy the old six-digit email-change flow into registration verification.
- Passwords: minimum 12 characters; permit at least 64 characters, spaces, Unicode, paste, password managers and autofill. Do not impose arbitrary composition rules or silently trim/truncate. Choose configured hasher and safe maximum together before implementation (D-05); the historical 1024-character maximum must not be copied blindly.
- Login uses validated credentials, generic failure feedback and throttling. Regenerate session on success. Only internal authorized intended destinations are eligible; ignore external/privilege-inappropriate redirects. Unverified users reach the verification notice, not a protected dashboard.
- Logout is CSRF-protected POST; invalidate session and regenerate CSRF token. A GET link must not log out. Back navigation cannot reveal cached sensitive pages.
- Forgot password always presents a neutral response regardless of account existence. Use Laravel broker token expiry/throttling; deliver through the selected mail transport. Reset validates token/current address, password and confirmation, consumes token, rotates persistent-login credentials and invalidates existing sessions. Require fresh login after reset; no implicit login.
- Email verification uses authenticated, expiring signed links bound to the current account/email, plus throttled resend. Links cannot verify another logged-in identity. Invalid/expired links offer safe recovery; already verified responses are idempotent. Email send failure must not mark an address verified or display false delivery success. Transport acceptance is not proof of inbox delivery.

Proposed verification policy, pending D-06: initial Super Admin is created only by the future installer and starts unverified. All roles pass the same verification gate; there is no installer email shortcut. Users can access the verification notice, resend, logout and authenticated self-account recovery routes without verification; dashboards require verification. Registration does not provision another Super Admin.

## Role and capability matrix

Roles are explicit Super Admin, Admin and User; unknown/missing roles deny protected capability. Do not use a boolean administrator flag or a blanket before-Gate bypass. Role persistence/enum details are chosen during implementation without a permissions package by default.

| Capability after installation | Guest | Unverified signed-in user, any role | Verified User | Verified Admin | Verified Super Admin |
| --- | --- | --- | --- | --- | --- |
| Register / login / request reset | Yes | Redirect to safe signed-in entry | Redirect | Redirect | Redirect |
| Verify own email / request resend | No (login first) | Yes | Already verified | Already verified | Already verified |
| Logout / own account settings | No | Yes | Yes | Yes | Yes |
| User dashboard | No | No | Yes | No | No |
| Admin dashboard | No | No | No | Yes | Yes |
| Edit another account or roles through web UI | No | No | No | No | No |
| Repeat installer | No | No | No | No | No |

The two dashboard destinations are `/dashboard` (User) and `/admin/dashboard` (Admin/Super Admin), planned named routes. An optional dispatcher must apply this matrix rather than introduce a third dashboard. Both privileged roles currently share only the approved admin workspace; future Super Admin capabilities require explicit scope/permission entries. Do not invent CMS or user management permissions.

Check auth, valid installation state, email verification and role on every dashboard endpoint. Account actions resolve the authenticated owner and ignore arbitrary user IDs. Unauthenticated access redirects to login; authenticated wrong-role access returns safe 403 without data; unverified dashboard access goes to verification notice. Policies/Gates must be tested for direct requests as well as links.

No web role editing is approved. Admin creation, privilege promotion/demotion and preservation of the final Super Admin require a reviewed operator procedure before they are offered (D-07). Never accept role changes through registration/account updates or silently default privileged roles.

## Session and acceptance requirements

Password changes follow [Account](account.md); reset invalidates all existing sessions while self-service password change retains only the newly regenerated current session. Select an enforceable revocation strategy independent of stale session claims and test multiple browsers/remember-me behavior. Sensitive actions require current password confirmation.

Cover registration role tampering, duplicate races, every role/verification combination, intended redirect abuse, CSRF, throttles, enumeration behavior, session fixation/logout/revocation, reset expiry/replay and signed verification tampering/cross-user/expiry. Exercise delivery failure without real messages. Framework reference review is recorded in Decisions; no new identity implementation or tests are claimed in Phase 0.
