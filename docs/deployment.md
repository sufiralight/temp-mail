# Deployment

Use PHP 8.5 and Composer 2. Check CLI and web PHP extensions independently, including PDO MySQL and the Laravel platform requirements. For local Phase 0 checks, use the private local configuration, composer install, php artisan test, and optionally php artisan serve. Avoid composer setup in Phase 0 because it runs migrations.

Compile assets locally or in CI and deploy public/build with its Vite manifest. The scaffold declares Vite ^8.0.0 and Laravel Vite plugin ^3.1. Registry metadata for Vite 8.3.8 and the plugin 3.2.1 declares Node ^20.19.0 or >=22.12.0. The plugin accepts Vite ^8.0.0. Node 26.13.0 satisfies these declarations. Prefer Node 24 LTS for reproducible builds, pin the build runtime, and use a committed lockfile with npm ci once frontend work begins. Phase 0 validates metadata only.

For cPanel or equivalent hosting, set the HTTPS document root to public/ and keep .env, Composer metadata, vendor/, application files, and logs outside the public root. Install locked production PHP dependencies with composer install --no-dev --optimize-autoloader. Give the web account write access to storage/ and bootstrap/cache/ with least privilege. Set APP_ENV=production and APP_DEBUG=false and keep keys and database secrets private. Verify redirects, TLS, the health endpoint, and that private paths are inaccessible. Cache configuration only after provisioning it. No production database work is authorized in Phase 0.

Keep the runtime independent of Node.js, Redis, Docker, Supervisor and persistent workers. Review the host's limits before introducing scheduled jobs or asynchronous work in later phases.
