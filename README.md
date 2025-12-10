# CRM App (Web)

PHP/MySQL CRM with Tailwind UI, AI helpers, profile management, and password reset.

## Overview
- Small, single-repo CRM focused on real-estate style pipelines (leads → clients → deals → tasks).
- Backend: vanilla PHP 8.x with simple routing/controllers (`src/`), MySQL via PDO, JSON APIs.
- Frontend: Tailwind CSS, minimal JS, server-rendered views in `public/views` (no heavy framework).
- Files and uploads stored under `storage/` (with `storage/uploads` for client/deal documents).
- AI helper endpoints wrap an OpenAI-compatible LLM for summaries and follow-up suggestions.

## Tech stack
- PHP 8.x, MySQL 5.7+ or MariaDB 10+.
- Tailwind CSS (built with `npm`), PostCSS.
- PHPMailer for SMTP email (password reset, notifications).
- Simple MVC-ish structure: `models`, `controllers`, `routes`, `services`, `middleware`.

## Folder structure (high level)
- `public/` – front controller (`index.php`), `api.php`, public assets, and Blade-like views.
- `src/` – application code:
  - `config/` – database, app, and mail configuration helpers.
  - `controllers/` – auth, leads, clients, deals, tasks, AI helper controllers.
  - `models/` – database models / repositories.
  - `middleware/` – auth/session middleware and helpers.
  - `routes/` – API route map (`api_routes.php`).
  - `services/` – reusable services (mail, AI client, file storage, etc.).
- `sql/` – schema and migration helpers (`schema.sql`, upgrade snippets).
- `storage/` – logs, cache, and file uploads (make sure it is not web-accessible in production).
- `docs/` – extra notes, diagrams, or future documentation.

## Environment variables (reference)
These live in `.env` (never commit real secrets):

- Core app:
  - `APP_URL` – base URL used in links and emails.
  - `APP_TIMEZONE` – PHP timezone identifier (e.g. `Asia/Dubai`, `Europe/London`).
- Database (MySQL/MariaDB):
  - `DB_HOST`, `DB_PORT` – host and port for the DB server.
  - `DB_NAME` – database name (e.g. `crm_app`).
  - `DB_USER`, `DB_PASS` – credentials with permissions to create/alter tables.
- LLM (AI helpers):
  - `LLM_API_URL` – chat completions endpoint (OpenAI-compatible).
  - `LLM_API_KEY` – API key/token.
  - `LLM_MODEL` – model name (e.g. `gpt-4o-mini`).
- SMTP (email/password reset):
  - `SMTP_HOST`, `SMTP_PORT` – mail server and port.
  - `SMTP_USER`, `SMTP_PASS` – mailbox username/password.
  - `SMTP_FROM` – email shown as the sender.
  - `SMTP_SECURE` – `tls` or `ssl` depending on your provider.

## Quick start
1) Copy `.env.example` to `.env` and set:
```
APP_URL=http://127.0.0.1:8765
APP_TIMEZONE=Asia/Dubai       # or your timezone

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=crm_app
DB_USER=your_db_user
DB_PASS=your_db_password

LLM_API_URL=https://api.openai.com/v1/chat/completions
LLM_API_KEY=your_api_key
LLM_MODEL=gpt-4o-mini

# Optional: SMTP for password reset
SMTP_HOST=smtp.yourprovider.com
SMTP_PORT=587
SMTP_USER=your_mailbox@example.com
SMTP_PASS=your_smtp_password
SMTP_FROM=your_mailbox@example.com
SMTP_SECURE=tls
```
2) Import `sql/schema.sql` into MySQL (includes `password_resets`).
3) Run the PHP server:
```
php -S 127.0.0.1:8765 -t public
```
4) (Optional) Rebuild Tailwind after UI changes:
```
npm install
npm run build:css   # or npm run watch:css
```
5) Open `http://127.0.0.1:8765/index.php?page=login`.

For API-only usage (headless), point your frontend or API client at `http://127.0.0.1:8765/api.php` with the paths listed below.

## Deploy to Hostinger (summary)
1) Build assets and deps locally:
   - `npm run build:css`
   - `composer install --no-dev` (PHPMailer required)
2) Upload files:
   - Put the contents of `public/` into `public_html` (`api.php`, `index.php`, `.htaccess`, `assets/`, `views/`).
   - Put `src/`, `vendor/`, `storage/`, `.env`, `sql/`, `composer.*` above `public_html` if allowed. If they must be in `public_html`, protect `storage` and `.env` via `.htaccess`.
3) Set `.env` on the server (not tracked) with your domain, DB creds, SMTP mailbox (full email in `SMTP_USER`/`SMTP_FROM`), etc. Ensure `storage/uploads` is writable.
4) Import `sql/schema.sql` into your MySQL DB via phpMyAdmin.
5) PHP config: PHP 8.x with `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `openssl`; `upload_max_filesize`/`post_max_size` >= 5MB.
6) Test live: login, Kanban drag, client file upload/download, password reset (6-digit code via email).

## Key features/endpoints
- Auth: `/auth/register`, `/auth/login`, `/auth/logout`
- Password reset: `/auth/forgot` (logs reset link in `storage/logs/app.log` for dev) and `/auth/reset` (token + new password)
- Profile: `/auth/me`, `/auth/profile` (update name/email/password) and UI at `?page=profile`
- CRM APIs: leads, clients, deals, tasks (see `src/routes/api_routes.php`)

### Leads
- `GET /leads` – list leads (filter/search via query params).
- `POST /leads` – create a new lead.
- `PUT /leads/{id}` – update an existing lead (status, budget, notes, etc.).
- `DELETE /leads/{id}` – soft-delete or remove a lead.
- `PATCH /leads/bulk` – bulk status/owner updates for multiple leads.

### Clients
- `GET /clients` – list clients.
- `POST /clients` – create client records (usually from converted leads).
- `GET /clients/{id}` – client details.
- `GET /clients/{id}/timeline` – combined view of notes, tasks, activities.
- `GET|POST|DELETE /clients/{id}/files` – upload/list/delete client files.
- `GET /clients/{id}/files/{file_id}/download` – download a specific file.
- `GET|POST /clients/{id}/notes` – list/add notes.
- `POST /clients/{id}/tasks` – attach a task to a client.
- `POST /clients/{id}/deals` – attach a deal to a client.
- `PUT /clients/{id}` / `DELETE /clients/{id}` – update or archive/delete client.

### Deals
- `GET /deals` / `POST /deals` / `PUT /deals/{id}` / `DELETE /deals/{id}` – CRUD for deals.
- `GET|POST|DELETE /deals/{id}/files` – upload/list/delete files attached to a deal.
- `GET /deals/{id}/files/{file_id}/download` – download a deal file.

### Tasks
- `GET /tasks` / `POST /tasks` / `PUT /tasks/{id}` / `DELETE /tasks/{id}` – CRUD for tasks (list/calendar views in UI).

### AI helpers
- `POST /ai/summarize` – send client/lead context, receive text summary.
- `POST /ai/suggest-followup` – get suggested follow-up messages/tasks.

## UI notes
- Login/reset: gradient background, pill inputs/buttons
- Dashboard: card-based layout with charts, quick stats
- Profile: two-column card (avatar/status + editable details)
- Sidebar: icon nav; profile avatar + logout live in the top bar

## Notes
- Keep secrets out of version control; use `.env.example` as your template.
- Tailwind rebuild recommended after UI tweaks: `npm run build:css`.
- PHPMailer is installed via Composer for SMTP sending.
 - Protect `storage/` and `.env` in production (e.g. via `.htaccess` or web server rules) so uploads and secrets are not directly accessible.

## Password reset
- `/auth/forgot` sends a 6-digit code + reset link via SMTP (configure `SMTP_*` in `.env`).
- `/auth/reset` accepts the 6-digit code and new password.
- Tokens expire after 1 hour.

## Recent changes (summary)
- Added profile page (`?page=profile`) with avatar/status card and editable name/email/password; backend endpoints `/auth/me` and `/auth/profile`.
- Moved profile access to a round avatar in the top bar; removed logout from the sidebar (logout now in the top bar).
- Refreshed dashboard styling to a card-based layout with soft shadows and reorganized chart/summary panels.
- Login/forgot/reset screens restyled with gradient background and pill inputs/buttons; reset now uses a 6-digit code UI.
- Password reset flow: `/auth/forgot` emails a 6-digit code + link via SMTP; `/auth/reset` consumes the code + new password. Tokens expire after 1 hour.
- Tailwind rebuild recommended after UI tweaks: `npm run build:css` (or `npm run watch:css`).


## Data model highlights / required columns
- Leads: property_for, interested_property, area, currency, budget, status (new/contacted/qualified/not_qualified), source dropdown values (Bayut, Property Finder, Dubizzel, Reference/Random, Social Media), last_contact_at.
- Deals: stages initiated/processing/finished, amount (commas allowed; normalized), currency (AED/USD required), optional lead_id/client_id.
- Tasks: link to leads/clients; list/calendar views.
- Clients: optional document uploads (up to 10 MB) with files modal.
- Coming soon placeholders: Tenancy Contract/Record, Rental Agreements pages (no backend yet).


## Existing DB upgrade (incremental)
If you already have a database, add missing columns instead of reimporting the whole schema:
```sql
-- Deals currency
ALTER TABLE deals ADD COLUMN currency VARCHAR(3) NULL AFTER amount;

-- Leads new fields (skip columns you already added)
ALTER TABLE leads
  ADD COLUMN property_for VARCHAR(20) NULL AFTER owner_id,
  ADD COLUMN interested_property VARCHAR(100) NULL AFTER property_for,
  ADD COLUMN area VARCHAR(100) NULL AFTER interested_property,
  ADD COLUMN currency VARCHAR(3) NULL AFTER budget;
```
If you previously had legacy deal stages (prospecting/closed_won/etc.), map them to initiated/processing/finished before editing.

### Rename contacts -> clients (legacy upgrade)
If you were on the older schema that used `contacts`, run these once to align with the new client naming:
```sql
RENAME TABLE contacts TO clients;
RENAME TABLE contact_files TO client_files;
RENAME TABLE contact_notes TO client_notes;
RENAME TABLE contact_activities TO client_activities;
ALTER TABLE deals CHANGE contact_id client_id INT UNSIGNED NULL;
ALTER TABLE tasks CHANGE contact_id client_id INT UNSIGNED NULL;
```


## Keyboard shortcuts
- Esc closes open modals/forms (leads, clients, deals, tasks).
- `n` or `/` opens ?New? modals on those pages when not typing in a field.

## Development tips
- Run `npm run watch:css` while working on the UI to auto-rebuild Tailwind.
- Tailwind config is in `tailwind.config.js`; PostCSS plugins in `postcss.config.js`.
- For debugging, check `storage/logs/app.log` and your PHP error log.
- If something breaks after schema changes, verify that your DB columns match `sql/schema.sql` and the upgrade snippets above.
