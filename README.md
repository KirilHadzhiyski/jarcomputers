# Laravel + Vue Rewrite

For cPanel, use the Bulgarian [upload guide](docs/cpanel-upload.md). Run `build-cpanel.cmd` to create a production ZIP with separate `jar-app` and `public_html` folders, compiled assets, dependencies, and bounded Cron workers.

This project was rewritten from the original React/Vite app into Laravel + Vue.

The original app is preserved in `legacy-react/`. The `/admin` workspace now reuses its React interface, backed by authenticated Laravel endpoints and database storage.

The Bulgarian [admin automation guide](docs/admin-automation.md) covers JAR, mailbox imports, ECB rates, authorized price feeds, Google reviews, and preparation for a later Skroutz account connection. Open `/admin/integrations` for connection settings and `/admin/skroutz` for catalog readiness. Start the local workers with `run-automation.cmd`.

## First-time setup

From PowerShell in the project root:

```powershell
.\setup-local.cmd
```

This uses the local helper scripts to install dependencies, create the SQLite database, run migrations, and build the frontend.
The scripts can use system `PHP` / `Node.js` if they are installed, or fall back to bundled tools when available.

## Run the app

For a normal local run:

```powershell
.\run-app.cmd
```

Then open:

```text
http://127.0.0.1:8000
```

## Run in development mode

If you want Laravel plus the Vite dev server for frontend changes:

```powershell
.\run-dev.cmd
```

This starts:

- Laravel at `http://127.0.0.1:8000`
- Vite at `http://127.0.0.1:5173`

## Backend capabilities

- Repair requests now store email, consent, metadata, and a conversation history.
- Website form submissions create a `repair_requests` row plus a `conversations` thread and first inbound message.
- Incoming webhooks can be received at:
  - `/webhooks/whatsapp`
  - `/webhooks/facebook-messenger`
  - `/webhooks/viber`
- New repair requests and inbound chat messages trigger email notifications and are logged in `notification_deliveries`.

## Domain, email, and channel setup

The project is prepared for a custom domain and production email. Use `.env` / `.env.example` and set:

- `APP_URL`, `PRIMARY_DOMAIN`, `CANONICAL_HOST`, `ASSET_URL`, `FORCE_HTTPS`
- `MAIL_*`, `MAIL_REPLY_TO_*`, `CONTACT_NOTIFICATION_EMAILS`
- `WHATSAPP_*`, `FACEBOOK_*`, `VIBER_*`

Detailed setup steps are in `docs/publish-checklist.md`.

## Useful notes

- The app uses SQLite at `database/database.sqlite`.
- For GitHub/public repo use, local caches, generated assets, local database files, and bundled runtime downloads are excluded via `.gitignore`.
- Local helper scripts are:
  - `setup-local.ps1`
  - `run-app.ps1`
  - `run-dev.ps1`
