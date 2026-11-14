# Decisions

| Date | Status | Decision |
|---|---|---|
| 2026-11-14 | Approved | Adopt the existing Laravel 13.42.0 starter for Temp Mail SaaS. Target Laravel 13, PHP 8.5, Composer 2 and MySQL or MariaDB. |
| 2026-11-14 | Approved | Use Blade Components, Tailwind CSS 4, Vite 8 and vanilla JavaScript. Support localhost and conventional shared hosting. |
| 2026-11-14 | Approved | Adopt [Pure Zinc 16 v1.0](color-system.md), Inter, JetBrains Mono, Lucide SVG, Light/Dark/System and WCAG 2.2 AA. |
| 2026-11-14 | Approved | Use the three-step [installation flow](installation-flow.md) and the [phase roadmap](implementation-plan.md). |
| 2026-11-14 | Approved | Treat Windows development on localhost:8000 and 127.0.0.1:8000 and conventional Linux cPanel production as first-class targets. Use one local APP_URL, a private project root, compiled production assets and cron-compatible scheduling. Keep host validation and proxy trust environment-specific. |
| 2026-11-14 | Implemented | Use Tailwind 4 semantic aliases, self-hosted Latin variable WOFF2 fonts and one early appearance engine with browser-local preference storage. |
| 2026-11-14 | Implemented | Commit the npm dependency lockfile. Node 24 LTS remains the recommended build baseline. Phase 1.1 was verified with Node 26.13.0. |
| 2026-11-14 | Open | Choose production operator authorization, durable installation state storage and recovery mechanics in Phase 2. Finalize component APIs and token scale details in Phase 1. |
