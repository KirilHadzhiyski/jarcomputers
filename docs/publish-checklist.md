# Publish Checklist

## 1. Point the domain to the server

- Point the web root to the Laravel `public/` directory.
- Use a production `.env` instead of the local one.
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set `APP_URL=https://your-domain.tld`
- Set `PRIMARY_DOMAIN=your-domain.tld`
- Set `CANONICAL_HOST=your-domain.tld`
- Set `ASSET_URL=https://your-domain.tld`
- Set `FORCE_HTTPS=true`
- Set `TRUSTED_PROXIES` if the app is behind Nginx / Cloudflare / a load balancer.
- Set secure cookies:
  - `SESSION_SECURE_COOKIE=true`
  - `SESSION_DOMAIN=.your-domain.tld`

## 2. Configure production email

- Choose the mailbox/service that will send mail for the domain.
- Recommended for this project: Resend over SMTP.
- Update:
  - `MAIL_MAILER`
  - `MAIL_HOST`
  - `MAIL_PORT`
  - `MAIL_USERNAME`
  - `MAIL_PASSWORD`
  - `MAIL_SCHEME`
  - `MAIL_FROM_ADDRESS`
  - `MAIL_REPLY_TO_ADDRESS`
  - `CONTACT_NOTIFICATION_EMAILS`
- Add the required DNS records for the mail provider:
  - `SPF` (`TXT`)
  - `DKIM` (`CNAME` or `TXT`, depending on provider)
  - `DMARC` (`TXT`)

## 3. Activate the messaging channels

### WhatsApp

- Create or connect the Meta Business / WhatsApp Cloud API app.
- Set:
  - `WHATSAPP_ENABLED=true`
  - `WHATSAPP_PHONE_NUMBER_ID`
  - `WHATSAPP_ACCESS_TOKEN`
  - `WHATSAPP_VERIFY_TOKEN`
  - `WHATSAPP_APP_SECRET`
- Register webhook URL:
  - `https://your-domain.tld/webhooks/whatsapp`

### Facebook Messenger

- Connect the Facebook page and Messenger app in Meta.
- Set:
  - `FACEBOOK_MESSENGER_ENABLED=true`
  - `FACEBOOK_PAGE_ID`
  - `FACEBOOK_PAGE_ACCESS_TOKEN`
  - `FACEBOOK_VERIFY_TOKEN`
  - `FACEBOOK_APP_SECRET`
- Register webhook URL:
  - `https://your-domain.tld/webhooks/facebook-messenger`

### Viber

- Create the Viber bot and get its production token.
- Set:
  - `VIBER_ENABLED=true`
  - `VIBER_BOT_NAME`
  - `VIBER_BOT_TOKEN`
  - `VIBER_WEBHOOK_SECRET`
- Register webhook URL:
  - `https://your-domain.tld/webhooks/viber`

## 4. Runtime infrastructure

- Use MySQL or PostgreSQL in production.
- Set `QUEUE_CONNECTION=database` or `redis`.
- Run a persistent queue worker:
  - `php artisan queue:work --queue=default --tries=3 --timeout=120`
- Monitor `failed_jobs`.

## 5. Deploy and warm the app

- Run `php artisan migrate --force`
- Run `php artisan config:cache`
- Run `php artisan route:cache`
- Run `php artisan view:cache`
- Run `npm run build`

## 6. Security checks before publish

- Confirm demo seeders are not used in production.
- Remove any local/test accounts from the production database.
- Confirm login, registration, verification, and repair request throttling behave correctly.
- Confirm HTTPS redirect and secure cookies are active.

## 7. Smoke test after publish

- Open `/`
- Open `/kontakti`
- Open `/sitemap.xml`
- Open `/robots.txt`
- Submit a repair request and confirm:
  - a `repair_requests` row is created
  - a `conversations` row is created
  - email notification is sent
- Send a test webhook message from each enabled channel and confirm:
  - a `conversation_messages` row is created
  - email alert is sent

## 8. Notes

- The public site already includes privacy policy, terms, sitemap, canonical URLs, and LocalBusiness schema.
- Review stats are stored as a snapshot in `config/reviews.php`; update them when you want to refresh the public numbers.
- Meta and Viber can require separate business verification, page/app review, or production approval outside the codebase.
