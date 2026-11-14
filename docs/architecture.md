# Architecture

Keep Laravel's entry point in public/, routing in routes/, configuration in config/, and request middleware and exception configuration in bootstrap/app.php. Put controllers and Form Requests under app/Http, models under app/Models, Blade templates under resources/views, migrations and factories under database/, and PHPUnit suites under tests/.

Controllers coordinate HTTP requests. Form Requests validate input and policies enforce access. Use Eloquent and database constraints for persistence. Extract actions for workflows that span multiple writes or integrations. Group future code by feature within Laravel's existing directories once a feature grows. Keep queries, authorization decisions, and side effects out of Blade.

Use MySQL or MariaDB for deployed application data. Keep configuration private, isolate test databases, and account for DDL commits when designing workflows. Windows development must serve http://localhost:8000 and http://127.0.0.1:8000 using one APP_URL. Use request-aware or relative links for in-request navigation and a canonical configured URL for out-of-request generation. Browser cookies are scoped to the hostname, so each loopback name has its own session. Keep production host and proxy trust configuration independent of local allowances.

Production targets conventional Linux shared hosting with cPanel. Use configuration and Laravel path helpers for portable filesystem paths. Shared hosting must support execution without Node.js, npm, Redis, Docker, Supervisor, WebSockets or persistent workers. Design future scheduled work for cPanel cron with bounded execution. See [deployment](deployment.md) and [installation flow](installation-flow.md).
