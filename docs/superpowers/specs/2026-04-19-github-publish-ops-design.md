# GitHub Publish Ops Design

**Date:** 2026-04-19

## Goal

Prepare the repository for ongoing team use and controlled production releases by adding:

- GitHub issue templates
- a pull request template
- a CI workflow for validation
- a manual deploy workflow for SuperHosting via GitHub Actions
- clearer repository and production documentation

## Scope

This design covers repository operations and deployment hygiene only. It does not change application features, database schema, or public UI behavior.

## Selected Approach

Option `2`: keep automatic checks in GitHub, but require a manual deploy trigger for production publishing.

This is the safest balance for the current project state:

- every push can be validated automatically
- production deploys stay deliberate
- SuperHosting-specific credentials can live in GitHub Secrets
- the repository becomes ready for repeatable releases without forcing auto-publish on every merge

## Deliverables

### 1. GitHub repository hygiene

Add a `.github/` structure with:

- `ISSUE_TEMPLATE/bug_report.yml`
- `ISSUE_TEMPLATE/feature_request.yml`
- `ISSUE_TEMPLATE/config.yml`
- `pull_request_template.md`

The templates should be short and operational:

- bug report asks for page, steps, expected result, actual result, environment, screenshots
- feature request asks for business need, user flow, proposed outcome, constraints
- PR template asks for change summary, risk, test evidence, deploy notes

### 2. Continuous Integration workflow

Add a GitHub Actions workflow that runs on:

- `push`
- `pull_request`

The workflow should validate the Laravel/Vue application with the lowest-friction setup possible:

- checkout repository
- set up PHP
- install Composer dependencies
- set up Node
- install npm dependencies
- prepare an `.env`
- generate app key
- create SQLite database file for CI
- run migrations
- run PHP feature/unit tests
- run frontend production build

The CI workflow should avoid external services and use SQLite so it stays portable and fast.

### 3. Manual deploy workflow for SuperHosting

Add a `workflow_dispatch` deploy action that does not run automatically on push.

It should:

- build the frontend assets in GitHub Actions
- package the deployable application
- connect through SSH using GitHub Secrets
- upload the release to the target hosting path
- run Laravel production commands remotely

Expected secrets:

- `DEPLOY_HOST`
- `DEPLOY_PORT`
- `DEPLOY_USER`
- `DEPLOY_PATH`
- `DEPLOY_SSH_KEY`

Expected remote commands:

- extract uploaded release
- preserve server-side `.env`
- preserve writable `storage/`
- run `composer install --no-dev --optimize-autoloader`
- run `php artisan migrate --force`
- run `php artisan config:cache`
- run `php artisan route:cache`
- run `php artisan view:cache`

The workflow should be written as a template for shared hosting with SSH access, not as a zero-config guarantee. SuperHosting plans differ, so the final remote path and PHP/composer binary availability remain environment-specific.

### 4. Repository documentation updates

Update `README.md` so a collaborator can understand:

- what the project is
- how to run it locally
- where deployment docs live
- what CI validates
- how production deploys are triggered

Add or update docs for:

- GitHub Actions secrets required for deploy
- first production deploy checklist
- rollback expectations
- manual post-deploy smoke test

## Constraints

- No automatic production deploy on `push` to `main`
- No dependency on the broken GitHub connector for deploy logic
- No hardcoded production credentials in repository files
- No assumption that the hosting server can run Node during deploy

## Risks

### Shared hosting command differences

SuperHosting environments may differ on:

- PHP binary path
- Composer availability
- tar/unzip availability
- release directory permissions

Mitigation: keep the workflow manual and parameterize only the SSH connection in GitHub Secrets. Document the expected remote environment clearly.

### CI drift from production

CI will use SQLite while production should use MySQL/PostgreSQL.

Mitigation: use CI only for code validation and keep production migration/deploy steps documented separately.

## Success Criteria

The work is complete when:

- contributors can open structured issues and PRs
- every push/PR gets automated validation
- a maintainer can manually trigger a deploy workflow from GitHub
- the deploy workflow is documented with the exact required secrets
- the repository is easier to hand off and operate
