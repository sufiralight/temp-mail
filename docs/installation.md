# Installation and Configuration Recovery

`/install` is the first milestone for Temp Mail. The installer must work in a browser on localhost and shared hosting. This document describes planned behavior.

## Installation states

| State | Browser behavior |
| --- | --- |
| Fresh deployment | Application entry points lead to `/install`. The first screen is **Connect your database**. The installer determines readiness from completed setup, even if a starter `.env` already exists. |
| Installation complete and `.env` present | Setup actions are locked, including configuration writes from the web UI. A visit to `/install` leads to `/login` or `/dashboard`, according to the visitor's session. |
| Installation complete and `.env` missing | Application entry points automatically lead to `/install` in **Restore configuration** mode. The screen loads before the application attempts to use the missing database configuration. |
| Authorized operator chooses a new installation | From the missing `.env` state, the operator explicitly opens **Start a New Installation** in a separate protected flow. |

Keep evidence of completed setup outside `.env` so deleting the file preserves the distinction between a fresh installation and recovery. If the installer cannot establish the installation state with confidence, require operator intervention before allowing configuration writes or admin creation. A deployment's public web root must keep `.env` and private installation state outside public access.

### Prompt 01 handoff

The read-only entry point recognizes `storage/app/private/installation.state` containing `complete` as completion evidence. Setup does not write this marker yet. An invalid marker, unavailable private storage, or cached configuration with both `.env` and the marker missing leads to **Installation status unavailable**. Confirm the database engine, durable state and installation identity, and operator authorization before enabling setup actions.

## First installation

1. **Connect your database.** Show **Database Host**, **Port**, **Database Name**, **Username**, and **Password** from the start. **Test Connection & Continue** verifies the submitted connection. It leaves the installed configuration and database data untouched. On failure, give actionable feedback and keep the user on the form. Keep safe field values available for correction and handle passwords as secrets throughout the flow.
2. **Create your admin account.** Show **Database connected** with an **Edit** action. Changing the connection details requires a fresh test. Collect **Full Name**, **Email Address**, **Password**, and **Confirm Password**. **Finish Installation** prepares the database, creates the initial admin, and saves the configuration. Lock installation once these operations succeed, then redirect to `/login` with **Installation complete. Sign in to continue.**

Protect setup actions with CSRF protection, rate limiting, and server-side checks. Only an authorized operator should be able to run a web installation on a public deployment. Handle retries and concurrent submissions so they cannot create duplicate administrators or mark a partial installation complete. Keep credentials and internal exceptions out of user-facing errors and logs.

## Restore configuration

Reuse the database form, connection validation, and configuration writer. Show recovery as a single task. Before writing configuration, verify the operator's authority independently of the missing `.env`, confirm that the target database belongs to the existing installation, and verify that the required configuration is available.

If any check fails, keep `/install` in recovery mode and stop before writing configuration or changing the database. Explain which prerequisite the authorized operator needs to restore or verify before retrying. Keep credentials and internal diagnostics out of the message. Keep administrator creation and database or credential resets out of the recovery flow. A failed recovery never starts a new installation.

After a successful restore, lock configuration writes again and send the operator to `/login` to use their existing account. Preserve existing accounts, inboxes, and messages throughout recovery.

## Start a new installation

Show **Start a New Installation** only after the operator has verified their authority independently of `.env`. Open a separate screen that explains the site will use a new database and the previous database will be preserved. Require explicit confirmation before starting setup.

Reuse the first-installation forms and application services. Verify that the target is a different, empty database before creating any tables or the new initial administrator. Leave the previous database, accounts, and messages intact. Activate the new configuration only after setup succeeds, then lock installation and redirect to `/login`. An interrupted attempt may resume its own work on the new database and must preserve the existing installation's recovery path until activation succeeds.

## Acceptance criteria

- A failed test can be corrected and retried. Admin creation requires a validated connection and revalidates it after edits.
- An interrupted or repeated submission cannot create a second administrator or leave a completed marker for an incomplete setup.
- A completed installation rejects direct setup submissions as well as hiding the setup form.
- Deleting `.env` opens recovery automatically, including on deployments that used cached configuration. Recovery preserves the existing database and rejects unauthorized submissions.
- If the existing installation cannot be verified or required configuration is missing, recovery makes no writes, identifies the next safe operator action, and stays in recovery mode.
- Starting a new installation requires verified operator authority and explicit confirmation. It rejects the previous database and any other nonempty database at the start of a new attempt. The previous database remains unchanged on both success and failure.
- The browser flow can be completed on localhost and a supported shared host without terminal access during setup.

## Open decisions

- Select the supported database engines and minimum hosting requirements.
- Choose how operators establish authority on public deployments and how setup state and installation identity are stored.
- Define how operators back up and restore sensitive configuration, including the existing application key, before finalizing the recovery procedure.
