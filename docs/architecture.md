# Architecture

Keep Laravel's entry point in public/, routing in routes/, configuration in config/, and request middleware and exception configuration in bootstrap/app.php. Put controllers and Form Requests under app/Http, models under app/Models, Blade templates under resources/views, migrations and factories under database/, and PHPUnit suites under tests/.

Controllers coordinate HTTP requests. Form Requests validate input and policies enforce access. Use Eloquent and database constraints for persistence. Extract actions for workflows that span multiple writes or integrations. Group future code by feature within Laravel's existing directories once a feature grows. Keep queries, authorization decisions, and side effects out of Blade.

Use MySQL or MariaDB for deployed application data. Keep configuration private, isolate test databases, and account for DDL commits when designing workflows. Conventional shared hosting must support execution without Redis, Docker, Supervisor, persistent workers, or Node.js. See [deployment](deployment.md) and [installation flow](installation-flow.md).
