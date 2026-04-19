# Laravel + Vue Rewrite

This project was rewritten from the original React/Vite app into Laravel + Vue.

The original app is preserved in `legacy-react/`.

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

## GitHub workflows

- `CI` runs on every push and pull request.
- It installs PHP and Node dependencies, runs database migrations against SQLite, executes `php artisan test`, and builds the Vite frontend.
- Production publishing is intentionally manual and documented in the deployment docs.

## Useful notes

- The app uses SQLite at `database/database.sqlite`.
- For GitHub/public repo use, local caches, generated assets, local database files, and bundled runtime downloads are excluded via `.gitignore`.
- Local helper scripts are:
  - `setup-local.ps1`
  - `run-app.ps1`
  - `run-dev.ps1`
