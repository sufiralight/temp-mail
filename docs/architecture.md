# Architecture

Keep Laravel's entry point in public/, routing in routes/, configuration in config/, and request middleware and exception configuration in bootstrap/app.php. Put controllers and Form Requests under app/Http, models under app/Models, Blade templates under resources/views, migrations and factories under database/, and PHPUnit suites under tests/.

Controllers coordinate HTTP requests. Form Requests validate input and policies enforce access. Use Eloquent and database constraints for persistence. Extract actions for workflows that span multiple writes or integrations. Group future code by feature within Laravel's existing directories once a feature grows. Keep queries, authorization decisions, and side effects out of Blade.

Use MySQL or MariaDB for deployed application data. Keep configuration private, isolate test databases, and account for DDL commits when designing workflows. Windows development must serve http://localhost:8000 and http://127.0.0.1:8000 using one APP_URL. Use request-aware or relative links for in-request navigation and a canonical configured URL for out-of-request generation. Browser cookies are scoped to the hostname, so each loopback name has its own session. Keep production host and proxy trust configuration independent of local allowances.

Production targets conventional Linux shared hosting with cPanel. Use configuration and Laravel path helpers for portable filesystem paths. Shared hosting must support execution without Node.js, npm, Redis, Docker, Supervisor, WebSockets or persistent workers. Design future scheduled work for cPanel cron with bounded execution. See [deployment](deployment.md) and [installation flow](installation-flow.md).

The installer uses dedicated temporary database connections and separate private file sessions. Its authoritative installation marker is independent of the application database and .env. See [the installation runbook](installation-flow.md).

## Authentication

The installed application uses Laravel’s web session guard, users password broker, and signed email verification. The completion marker and private environment configuration gate all auth routes. Public registration accepts only name, email and password. The super-admin gate reads the existing is_super_admin flag. The protected account page requires verified email for ordinary users and permits the operator-provisioned Super Admin immediately. Browser login redirects to the named account route. Logout is a CSRF-protected POST.

## Application routes

Customer pages use the installation gate, authenticated sessions and the existing verified-email policy, including the provisioned Super Admin exception. Admin pages additionally require the super-admin gate. Account settings can change only name and email. Email changes require the current password, invalidate prior verification and reset tokens, rotate the remember token, and lead to the existing verification resend workflow.
