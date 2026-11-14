# Deployment

Own shared-hosting compatibility. Build assets locally or in CI, expose only public/, and protect secrets with least privilege. Keep the runtime independent of persistent workers and Node.js.

Verify both Windows loopback URLs under one APP_URL. Use the deployment readiness checklist for Linux cPanel, including PHP CLI and web runtimes, public document root, compiled asset delivery, writable directories, private-path denial and cron support. Exclude public/hot from release artifacts.

Read [deployment](../docs/deployment.md) and [architecture](../docs/architecture.md) before changes.

Ship the complete `public/build/` output, `public/licenses/` notices and the application source for the inline appearance engine. Run the frontend manifest test before packaging.
