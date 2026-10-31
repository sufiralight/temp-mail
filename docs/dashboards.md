# Dashboard specification

Status: planned minimal Phase 2 role destinations; admin functionality remains Phase 4 and mailbox dashboard Phase 8. No dashboard or product installer route exists in inspected source. Verification gate is proposed under D-06. [Identity](authentication-authorization.md) exclusively owns route/role access. [UX](ux.md) owns general interactions and [UI system](ui-system.md) owns shared presentation.

## User dashboard

At `/dashboard`, a verified User can recognize their identity, review real account state and reach account settings or sign out. Provide a clear page heading, concise welcome using escaped name and genuine next steps. Do not show a fake inbox, generated email address, subscription status, billing balance, mail volume or unexplained zero metrics. If no product activity exists, use an honest account-focused introduction rather than disabled future-module navigation.

## Admin dashboard

At `/admin/dashboard`, verified Admin and Super Admin see the administration shell and real identity/role, account settings and logout. Distinguish administration from the user workspace through heading/navigation context while sharing brand/components. No CMS counts, recent posts, analytics, user-management links or operational health claims without approved implemented data sources. Do not display sensitive DB/server details. The initial Super Admin label is not permission for a new management module.

Keep only working destinations. Both shells need a skip link, one h1, current navigation state, responsive keyboard-operable navigation and the shared appearance control. Collapse mobile navigation with Escape/focus return where needed; avoid trapping focus in non-modal desktop sidebars. Account settings are common to both shells.

## Acceptance

Test guest/unverified/wrong-role direct requests, post-login destinations and escaped identity. Walk through dashboard → settings → save → return and POST logout on desktop/mobile with keyboard and without JS. Verify actual data, theme consistency and no dead controls. Passing old CMS dashboard tests is irrelevant.
