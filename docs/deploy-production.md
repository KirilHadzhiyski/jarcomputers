# Production Deployment

This file describes the minimum production setup for this Laravel project.

## 1. Environment file

- Start from [`.env.production.example`](/C:/Users/PC/Desktop/New%20folder%20(7)/project/.env.production.example)
- Do not reuse the local `.env`
- Set a real `APP_KEY`
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`

## 2. Database

- Use MySQL or PostgreSQL in production
- Do not use the local SQLite file for launch
- Run:

```bash
php artisan migrate --force
```

## 3. Mail

- Use a real transactional provider
- Recommended for this project: Resend SMTP
- Verify the sending domain before launch

## 4. Queue worker

The project sends emails and notifications. Do not keep `QUEUE_CONNECTION=sync` in production.

- Set `QUEUE_CONNECTION=database`
- Run a persistent worker:

```bash
php artisan queue:work --queue=default --tries=3 --timeout=120
```

- Ensure the worker restarts on deploy

## 5. Build and cache

Run the production build and warm Laravel caches:

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6. Web server

- Point the document root to the deployed `current/public/` directory
- Enable HTTPS
- Redirect HTTP to HTTPS
- Pass requests to PHP-FPM

## 7. Reverse proxy

- Set `TRUSTED_PROXIES=*` only if you are behind a trusted load balancer / reverse proxy
- Keep `FORCE_HTTPS=true` for production

## 8. Demo accounts

- Do not run local demo seeders in production
- The seeders are now restricted to `local` and `testing`
- Create your real admin account manually after deploy

## 9. Monitoring

- Watch `storage/logs/laravel.log`
- Track `failed_jobs`
- Add uptime monitoring for `/up`

## 10. Post-deploy checks

- Register a new account and verify the code arrives
- Confirm login works
- Submit a repair request
- Create a ticket as user and update it as admin
- Confirm the customer receives the update email

## 11. GitHub Actions deploy secrets

Set these repository secrets before using the manual deploy workflow:

- `DEPLOY_HOST`
- `DEPLOY_PORT`
- `DEPLOY_USER`
- `DEPLOY_PATH`
- `DEPLOY_SSH_KEY`

The workflow is triggered manually from GitHub Actions. If your hosting account uses non-default binaries, override `php_bin` and `composer_bin` when you start the workflow.

## 12. Rollback expectation

- Keep the previous release snapshot in the server-side `_backups/` directory.
- If a release fails after upload, restore the previous application directory and rerun Laravel cache warmup.
- Never overwrite the production `.env` with repository data.

## 13. First production deploy checks

- Confirm SSH access works before triggering the workflow.
- Confirm the document root points to `current/public/`.
- Confirm the production `.env` already exists on the server.
- Confirm writable permissions for `storage/` and `bootstrap/cache/`.
- Trigger the `Deploy to SuperHosting` workflow manually from GitHub Actions.
