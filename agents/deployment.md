# Deployment

Own shared-hosting compatibility. Build assets locally or in CI, expose only public/, and protect secrets with least privilege. Keep the runtime independent of persistent workers and Node.js.

Verify both Windows loopback URLs under one APP_URL. Use the deployment readiness checklist for Linux cPanel, including PHP CLI and web runtimes, public document root, compiled asset delivery, writable directories, private-path denial and cron support. Exclude public/hot from release artifacts.

Read [deployment](../docs/deployment.md) and [architecture](../docs/architecture.md) before changes.
