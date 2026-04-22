# Publish Checklist

## 1. Domains and redirect

- Point both `jarbl.com` and `jarbl.bg` to the same hosting account and document root `public/`.
- Use `jarbl.com` as the canonical public domain.
- Keep `jarbl.bg` only as an alternate domain that redirects to `jarbl.com`.
- In production `.env` set:
  - `APP_URL=https://jarbl.com`
  - `ASSET_URL=https://jarbl.com`
  - `PRIMARY_DOMAIN=jarbl.com`
  - `CANONICAL_HOST=jarbl.com`
  - `REDIRECT_HOSTS=jarbl.bg,www.jarbl.bg,www.jarbl.com`
  - `FORCE_HTTPS=true`
  - `TRUSTED_PROXIES=*` if the site is behind Cloudflare or a reverse proxy
- Set secure cookies:
  - `SESSION_SECURE_COOKIE=true`
  - `SESSION_DOMAIN=.jarbl.com`

## 2. Mailbox and forwarding

- Prepare the public service mailbox `office_bl@jarcomputers.com`.
- Configure it as:
  - public email on the site
  - reply-to address
  - repair request notification mailbox
  - sender mailbox if the mail provider allows it
- In production `.env` set:
  - `MAIL_FROM_ADDRESS=office_bl@jarcomputers.com`
  - `MAIL_REPLY_TO_ADDRESS=office_bl@jarcomputers.com`
  - `CONTACT_NOTIFICATION_EMAIL=office_bl@jarcomputers.com`
  - `CONTACT_NOTIFICATION_EMAILS=office_bl@jarcomputers.com`
  - `SITE_PUBLIC_EMAIL=office_bl@jarcomputers.com`
  - `SITE_SUPPORT_EMAIL=office_bl@jarcomputers.com`
- Add the required DNS mail records for the provider you choose:
  - `SPF`
  - `DKIM`
  - `DMARC`
- If the hosting panel uses email forwarding, create the forwarder there. The codebase is prepared for the address, but the actual mailbox/forwarder must be created in hosting.

## 3. Database and admin access

- Use MySQL or PostgreSQL in production.
- Run:
  - `php artisan migrate --force`
- Prepare one admin account in `.env`:
  - `ADMIN_USER_NAME`
  - `ADMIN_USER_EMAIL`
  - `ADMIN_USER_PHONE`
  - `ADMIN_USER_PASSWORD`
- Create the admin in production with:
  - `php artisan db:seed --class=AdminUserSeeder --force`
- After seeding, confirm you can log in to `/admin`.

## 4. Runtime infrastructure

- Prefer `QUEUE_CONNECTION=database` or `redis`.
- Run a persistent queue worker:
  - `php artisan queue:work --queue=default --tries=3 --timeout=120`
- Monitor `failed_jobs`.

## 5. Build and cache

- Run:
  - `npm run build`
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`

## 6. Final smoke test

- Open `/`
- Open `/kontakti`
- Open `/za-nas`
- Open `/sitemap.xml`
- Open `/robots.txt`
- Submit a repair request and confirm:
  - a `repair_requests` row is created
  - a `conversations` row is created
  - an email notification is sent to `office_bl@jarcomputers.com`
- Open the site from `jarbl.bg` and confirm it redirects to `jarbl.com`
- Log in to `/admin` and confirm the launch readiness panel shows the production setup correctly

## 7. Notes

- The public site is already prepared to work with phone and email as the primary contact methods.
- WhatsApp, Viber, and Messenger backend hooks can still be activated later if needed, but they are no longer part of the public contact flow.
- Review stats are stored as a snapshot in `config/reviews.php`; refresh them when you want updated public numbers.
