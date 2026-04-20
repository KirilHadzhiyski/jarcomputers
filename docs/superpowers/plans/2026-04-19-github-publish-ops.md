# GitHub Publish Ops Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add GitHub issue/PR templates, a CI workflow, a manual SuperHosting deploy workflow, and clearer repository documentation so the project is easier to operate and release safely.

**Architecture:** Keep repository operations under `.github/`, use GitHub Actions for validation and manually triggered deployment, and keep deployment assumptions documented instead of hardcoding hosting-specific credentials. CI will validate the Laravel + Vue app with SQLite and a production frontend build; deploy will package built assets locally in Actions and publish them over SSH.

**Tech Stack:** GitHub Actions, Laravel 12, PHPUnit, Node/Vite, SQLite (CI only), SSH-based shared hosting deploy.

---

### Task 1: Add GitHub repository templates

**Files:**
- Create: `.github/ISSUE_TEMPLATE/bug_report.yml`
- Create: `.github/ISSUE_TEMPLATE/feature_request.yml`
- Create: `.github/ISSUE_TEMPLATE/config.yml`
- Create: `.github/pull_request_template.md`

- [ ] **Step 1: Create the bug report issue form**

```yaml
name: Bug report
description: Report a reproducible problem in the site or admin area.
title: "[Bug]: "
labels:
  - bug
body:
  - type: input
    id: page
    attributes:
      label: Page or area
      placeholder: /kontakti, admin tickets, registration form
    validations:
      required: true
  - type: textarea
    id: steps
    attributes:
      label: Steps to reproduce
      placeholder: |
        1. Open ...
        2. Click ...
        3. See error ...
    validations:
      required: true
  - type: textarea
    id: expected
    attributes:
      label: Expected result
    validations:
      required: true
  - type: textarea
    id: actual
    attributes:
      label: Actual result
    validations:
      required: true
  - type: dropdown
    id: environment
    attributes:
      label: Environment
      options:
        - Local
        - Staging
        - Production
    validations:
      required: true
  - type: textarea
    id: evidence
    attributes:
      label: Screenshots or logs
      placeholder: Paste screenshots, console errors, or stack traces here.
```

- [ ] **Step 2: Create the feature request issue form**

```yaml
name: Feature request
description: Request a product or operations improvement.
title: "[Feature]: "
labels:
  - enhancement
body:
  - type: textarea
    id: business_need
    attributes:
      label: Business need
      placeholder: What problem are we solving and for whom?
    validations:
      required: true
  - type: textarea
    id: user_flow
    attributes:
      label: User flow
      placeholder: Describe the desired flow from start to finish.
    validations:
      required: true
  - type: textarea
    id: outcome
    attributes:
      label: Expected outcome
      placeholder: What should be true after this is delivered?
    validations:
      required: true
  - type: textarea
    id: constraints
    attributes:
      label: Constraints
      placeholder: Deadlines, integrations, legal, hosting, content, or design constraints.
```

- [ ] **Step 3: Create the issue template config**

```yaml
blank_issues_enabled: false
contact_links:
  - name: Operations and deployment notes
    url: ../../docs/publish-checklist.md
    about: Review the publish checklist before opening hosting or deployment tasks.
```

- [ ] **Step 4: Create the pull request template**

```md
## Summary

- What changed?
- Why was it changed?

## Testing

- [ ] `php artisan test`
- [ ] `npm run build`
- [ ] Manual smoke test completed

## Risk

- Affected areas:
- Rollback plan:

## Deploy Notes

- Required env changes:
- Required migrations:
- Required post-deploy checks:
```

- [ ] **Step 5: Verify the template files are present**

Run: `Get-ChildItem .github -Recurse`
Expected: The `.github/ISSUE_TEMPLATE` directory and `pull_request_template.md` are listed.

- [ ] **Step 6: Commit**

```bash
git add .github/ISSUE_TEMPLATE .github/pull_request_template.md
git commit -m "chore: add GitHub issue and PR templates"
```

### Task 2: Add CI workflow for Laravel + Vue validation

**Files:**
- Create: `.github/workflows/ci.yml`
- Modify: `README.md`

- [ ] **Step 1: Write the CI workflow**

```yaml
name: CI

on:
  push:
  pull_request:

jobs:
  test-and-build:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo, pdo_sqlite, sqlite3
          coverage: none

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: npm

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: vendor
          key: ${{ runner.os }}-php-${{ hashFiles('composer.lock') }}
          restore-keys: ${{ runner.os }}-php-

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Install Node dependencies
        run: npm ci

      - name: Prepare environment
        run: |
          cp .env.example .env
          php artisan key:generate
          touch database/database.sqlite

      - name: Run migrations
        env:
          DB_CONNECTION: sqlite
          DB_DATABASE: database/database.sqlite
          SESSION_DRIVER: database
          QUEUE_CONNECTION: sync
        run: php artisan migrate --force

      - name: Run test suite
        env:
          DB_CONNECTION: sqlite
          DB_DATABASE: database/database.sqlite
          SESSION_DRIVER: database
          QUEUE_CONNECTION: sync
          MAIL_MAILER: array
        run: php artisan test

      - name: Build frontend assets
        run: npm run build
```

- [ ] **Step 2: Add CI details to the README**

```md
## GitHub workflows

- `CI` runs on every push and pull request.
- It installs PHP and Node dependencies, runs database migrations against SQLite, executes `php artisan test`, and builds the Vite frontend.
- Production publishing is intentionally manual and documented in the deployment docs.
```

- [ ] **Step 3: Run the local verification commands for parity with CI**

Run: `php artisan test`
Expected: Test suite passes locally.

Run: `npm run build`
Expected: Vite production build completes successfully.

- [ ] **Step 4: Commit**

```bash
git add .github/workflows/ci.yml README.md
git commit -m "ci: add GitHub validation workflow"
```

### Task 3: Add manual SuperHosting deploy workflow

**Files:**
- Create: `.github/workflows/deploy-superhosting.yml`
- Modify: `docs/deploy-production.md`
- Modify: `docs/publish-checklist.md`

- [ ] **Step 1: Create the manual deploy workflow**

```yaml
name: Deploy to SuperHosting

on:
  workflow_dispatch:
    inputs:
      php_bin:
        description: PHP binary on the server
        required: true
        default: php
      composer_bin:
        description: Composer binary on the server
        required: true
        default: composer

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: npm

      - name: Install dependencies
        run: |
          composer install --no-interaction --prefer-dist
          npm ci
          npm run build

      - name: Prepare release archive
        run: |
          mkdir -p release
          rsync -av --delete \
            --exclude ".git" \
            --exclude ".github" \
            --exclude "node_modules" \
            --exclude "tests" \
            --exclude ".env" \
            --exclude "storage/logs" \
            --exclude "storage/framework/cache/*" \
            --exclude "storage/framework/sessions/*" \
            --exclude "storage/framework/testing/*" \
            --exclude "storage/framework/views/*" \
            ./ release/
          tar -czf release.tar.gz -C release .

      - name: Upload release archive
        uses: appleboy/scp-action@v0.1.7
        with:
          host: ${{ secrets.DEPLOY_HOST }}
          username: ${{ secrets.DEPLOY_USER }}
          key: ${{ secrets.DEPLOY_SSH_KEY }}
          port: ${{ secrets.DEPLOY_PORT }}
          source: release.tar.gz
          target: ${{ secrets.DEPLOY_PATH }}/_incoming

      - name: Deploy release on server
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.DEPLOY_HOST }}
          username: ${{ secrets.DEPLOY_USER }}
          key: ${{ secrets.DEPLOY_SSH_KEY }}
          port: ${{ secrets.DEPLOY_PORT }}
          script: |
            set -e
            cd "${{ secrets.DEPLOY_PATH }}"
            mkdir -p current
            tar -xzf _incoming/release.tar.gz -C current
            cd current
            ${{ inputs.composer_bin }} install --no-dev --optimize-autoloader --no-interaction
            ${{ inputs.php_bin }} artisan migrate --force
            ${{ inputs.php_bin }} artisan config:cache
            ${{ inputs.php_bin }} artisan route:cache
            ${{ inputs.php_bin }} artisan view:cache
```

- [ ] **Step 2: Document required GitHub Secrets and manual workflow usage**

```md
## GitHub Actions deploy secrets

Set these repository secrets before using the manual deploy workflow:

- `DEPLOY_HOST`
- `DEPLOY_PORT`
- `DEPLOY_USER`
- `DEPLOY_PATH`
- `DEPLOY_SSH_KEY`

The workflow is triggered manually from GitHub Actions and can override `php_bin` and `composer_bin` per run if the hosting account uses non-default paths.
```

- [ ] **Step 3: Add rollback and first-release notes to the production docs**

```md
## Rollback expectation

- Keep the previous release archive or previous application snapshot on the server.
- If a release fails after upload, restore the previous application directory and rerun cache warmup.
- Never overwrite the production `.env` with repository data.

## First production deploy checks

- Confirm SSH access works.
- Confirm the document root points to `public/`.
- Confirm the production `.env` exists on the server.
- Confirm writable permissions for `storage/` and `bootstrap/cache/`.
- Run the manual deploy workflow from GitHub Actions.
```

- [ ] **Step 4: Review the generated workflow for secret names and manual trigger**

Run: `Get-Content .github/workflows/deploy-superhosting.yml`
Expected: `workflow_dispatch` exists and the workflow references only `DEPLOY_*` secrets for connection details.

- [ ] **Step 5: Commit**

```bash
git add .github/workflows/deploy-superhosting.yml docs/deploy-production.md docs/publish-checklist.md
git commit -m "ci: add manual SuperHosting deploy workflow"
```

### Task 4: Final repository docs and operational verification

**Files:**
- Modify: `README.md`
- Modify: `docs/publish-checklist.md`

- [ ] **Step 1: Add a short operations section to the README**

```md
## Release flow

1. Push changes to GitHub.
2. Confirm the `CI` workflow passes.
3. Trigger `Deploy to SuperHosting` manually from the Actions tab.
4. Run the post-deploy smoke test from `docs/publish-checklist.md`.
```

- [ ] **Step 2: Add a manual post-deploy smoke test checklist**

```md
## 9. Manual smoke test after deploy

- Open `/`
- Open `/kontakti`
- Register a user and verify the email code flow
- Log in as admin and open the dashboard
- Submit a repair request
- Create or update a ticket
- Confirm outbound email delivery
- Confirm no new fatal errors in `storage/logs/laravel.log`
```

- [ ] **Step 3: Run final verification commands**

Run: `php artisan test`
Expected: PASS

Run: `npm run build`
Expected: PASS

Run: `git status -sb`
Expected: clean working tree on the current branch after commits.

- [ ] **Step 4: Commit**

```bash
git add README.md docs/publish-checklist.md
git commit -m "docs: document GitHub release flow"
```

## Self-review

- **Spec coverage:** The plan covers issue templates, PR template, CI, manual deploy, secrets documentation, rollback notes, README updates, and post-deploy smoke testing.
- **Placeholder scan:** No `TODO`, `TBD`, or deferred implementation wording remains; all files, commands, and commit messages are concrete.
- **Type consistency:** Workflow names, file paths, and secret names are consistent across tasks: `CI`, `Deploy to SuperHosting`, and `DEPLOY_*` connection secrets.
