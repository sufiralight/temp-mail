# Installer UX

The first impression should feel like a polished SaaS onboarding flow. Use Vercel as a reference for visual restraint, Stripe for form clarity and feedback, and Vite for a small color accent. Use the product's own copy and visual identity.

## Layout and theme

- Place the product logo at the left of the header and a sun or moon theme control at the right. Give the control an accessible name and remember the user's preference.
- Use a narrow, centered form on desktop and a single column with comfortable gutters on mobile. Place **Database Host** and **Port** side by side when space allows and stack them on small screens. Keep the primary action at the end of the form, where the mobile keyboard cannot turn it into a fixed overlay.
- Use a light zinc canvas and a near-white form surface for light mode, with dark zinc surfaces for dark mode. Keep borders subtle and text and controls legible in both themes. Reserve violet for small accents such as the active step and focus indicator.
- Choose one Google Fonts family with a system fallback. Verify that setup remains readable when the font cannot load.

## Screen hierarchy and feedback

| Screen | Header and context | Primary action |
| --- | --- | --- |
| Database | **Connect your database** with the current step in **Database → Admin** and a short note about finding connection details in the hosting panel | **Test Connection & Continue** |
| Admin | **Create your admin account** with **Database connected** and an **Edit** link | **Finish Installation** |
| Recovery | **Restore configuration** with an explanation that the operator is reconnecting an existing workspace | **Restore Configuration** |
| New installation | **Start a New Installation** with an explanation that the site will use a new database and the previous database will be preserved | **Continue to Database Setup** |

Keep the form in a consistent position across the two installation steps. Use an expandable help note for hosting-panel instructions. Put password requirements next to the password field before the user submits. During a connection test, show **Testing connection…**. During installation, name the work underway when its status is known. If work stops, explain how the user can retry or resume safely.

If recovery cannot continue, stay on **Restore configuration** and show the next action. For example, **We couldn't verify this installation. Check that the database details match your existing installation, then try again.** Keep the recovery screen focused on restoring the existing workspace.

After operator verification, show **Start a New Installation** as a secondary action outside the recovery form. Open a separate confirmation screen and then reuse the two installation steps. Keep the database switch visible in the confirmation copy.

## Accessibility and reusable components

- Use semantic headings, visible field labels, native controls, and a logical Tab order. Keep keyboard focus visible in both themes. Use ARIA where it adds state or relationships and expose the active step to assistive technology.
- Move focus to the new step heading after a successful transition. Associate inline errors with their fields and announce form-level errors and status changes. Pair color cues with text or another perceivable indicator.
- Use an accessible **Show password** / **Hide password** button that communicates its state and stays keyboard operable.
- Render setup, recovery, new installation, and error screens through Laravel Blade layouts and reusable Blade components. Keep markup in Blade templates. Share fields, password controls, status messages, and theme controls across the installer flows. Verify them with a keyboard, a screen reader, and a narrow viewport with the software keyboard open.

## Later milestones

Use the same design system for `/login`, `/register`, `/dashboard`, the inbox, and English error pages. Leave billing, plan selection, and inbound mail delivery decisions for their respective milestones.
