# Deployment

Use PHP 8.5 and Composer 2. Check CLI and web PHP extensions independently, including PDO MySQL and the Laravel platform requirements. For local Phase 0 checks, use the private local configuration, composer install, php artisan test, and optionally php artisan serve. Avoid composer setup in Phase 0 because it runs migrations.

Compile assets locally or in CI and deploy public/build with its Vite manifest. The scaffold declares Vite ^8.0.0 and Laravel Vite plugin ^3.1. Registry metadata for Vite 8.3.8 and the plugin 3.2.1 declares Node ^20.19.0 or >=22.12.0. The plugin accepts Vite ^8.0.0. Node 26.13.0 satisfies these declarations. Prefer Node 24 LTS for reproducible builds, pin the build runtime in CI, and install from the committed lockfile with npm ci. Run npm run build followed by node --test tests/js/design-system.test.mjs. Deploy the entire public/build directory, including manifest.json, hashed CSS and JavaScript, and both WOFF2 files. Include public/licenses and resources/js/appearance.js with the application. Verify CSS, JavaScript, fonts and licenses load from the deployment origin.

For cPanel or equivalent hosting, set the HTTPS document root to public/ and keep .env, Composer metadata, vendor/, application files, and logs outside the public root. Install locked production PHP dependencies with composer install --no-dev --optimize-autoloader. Give the web account write access to storage/ and bootstrap/cache/ with least privilege. Set APP_ENV=production and APP_DEBUG=false and keep keys and database secrets private. Verify redirects, TLS, the health endpoint, and that private paths are inaccessible. Cache configuration only after provisioning it. No production database work is authorized in Phase 0.

Keep the runtime independent of Node.js, Redis, Docker, Supervisor and persistent workers. Review the host's limits before introducing scheduled jobs or asynchronous work in later phases.

## Windows development

Use PHP 8.5 CLI, Composer 2 and the required extensions, including PDO MySQL for database work. From the project root run php artisan serve --host=127.0.0.1 --port=8000. Open http://localhost:8000 or http://127.0.0.1:8000. Keep one local APP_URL such as http://localhost:8000 for URL generation outside requests. Keep session cookies host-only and use the same hostname throughout any individual browser session. Use relative or request-aware links and environment-specific proxy and host configuration. Keep local configuration private. Both HTTP URLs are local development endpoints.

## Linux cPanel production

Use a host with PHP 8.5 for web and CLI, required extensions, MySQL or MariaDB, HTTPS, a configurable document root and access to cPanel Cron Jobs. Expose only Laravel's public/ directory. Keep private storage, credentials, source and configuration above the document root. Place built public/build assets and their manifest in the release. Exclude public/hot. Use a canonical HTTPS APP_URL, validate the production host, and trust forwarded headers only from configured proxy addresses when the hosting topology requires them.

Use Laravel's local disk for private files. File-backed sessions and cache are viable on a single writable host. Database-backed sessions and cache need their schema and verified database connectivity. Use the sync queue for short inline work when appropriate. Future asynchronous work must use an explicitly designed bounded drain, such as a cron-triggered database queue worker with stop and time limits. Avoid overlap and account for the host's minimum cron interval, PHP binary and resource limits. Future Laravel schedules can invoke artisan schedule:run through cPanel Cron Jobs with the correct PHP binary and project path. No production dependency on Node.js, npm, Redis, Supervisor, Docker, WebSockets or permanent workers.

## Deployment readiness checklist

- [ ] Verify PHP 8.5 on Windows and on cPanel CLI and web SAPI, required extensions and locked Composer platform requirements.
- [ ] Verify both Windows loopback URLs with one APP_URL, including links, forms and sessions as those features become available.
- [ ] Build with the pinned frontend toolchain locally or in CI. Verify the release contains the asset manifest and referenced files and excludes public/hot.
- [ ] On the target cPanel host, verify the document root points to public/ and HTTPS uses the canonical host. Verify rejection of unexpected hosts and untrusted forwarded headers.
- [ ] Verify direct HTTP requests cannot read .env, source files, logs, backups or private storage. Confirm least-privilege write access to storage/ and bootstrap/cache/.
- [ ] Verify the health endpoint, asset delivery, filesystem, sessions, cache and any enabled queues on the actual host. Check cPanel cron execution and overlap controls before enabling scheduled work.
- [ ] When the installer is implemented, verify all three steps, operator authorization, concurrent requests, interrupted installation, and durable locking in both environments.

Shared-hosting deployment remains unverified until these checks run on the target server.
