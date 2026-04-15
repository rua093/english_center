# Architecture Rules (Mandatory)

These rules are mandatory for all new code and refactors.

## 1) Pages Naming Convention

Use feature-first slugs for all new links and redirects:

- `dashboard-student`, `dashboard-teacher`, `dashboard-admin`
- `classes-academic`, `assignments-academic`, `materials-academic`
- `tuition-finance`, `payments-finance`
- `feedbacks-manage`, `approvals-manage`, `activities-manage`, `bank-manage`

Legacy slugs are alias-only and must not be introduced in new code.

## 2) API Layer Convention

- Canonical business flow goes in `api/canonical/<resource>.php`.
- Legacy endpoints in `api/<resource>/<method>.php` are wrappers only.
- Do not duplicate business logic in legacy endpoint files.
- `api/index.php` resolves canonical actions first.

## 3) Core Module Boundaries

- `core/file_storage.php`: upload/file storage operations.
- `core/page_actions.php`: approval and page action helpers only.
- `core/response.php`: flash/redirect/html escaping and UI class helpers.
- `core/security.php`: CSRF and request safety.
- `core/validation.php`: input helpers and basic validation.
- `core/logger.php`: app logging.

## 4) Config by Domain

Use domain files under `config/`:

- `config/app.php`
- `config/database.php`
- `config/security.php`
- `config/logging.php`

`config.php` is a compatibility loader only.

## 5) Storage Policy

Runtime files must stay under `storage/`:

- `storage/logs`
- `storage/cache`
- `storage/tmp`
- `storage/exports`

No runtime data should be written to project root folders.

## 6) Backward Compatibility Policy

- Do not remove legacy routes/endpoints without migration plan.
- Keep alias mapping in `core/page_routes.php` until migration is approved.
- Any URL changes must provide fallback mapping.
