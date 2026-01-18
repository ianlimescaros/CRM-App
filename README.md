
# CRM App

A modern, secure PHP/MySQL CRM for real estate and client management, featuring Tailwind UI, AI helpers, profile management, and password reset.

---

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Folder Structure](#folder-structure)
- [Environment Variables](#environment-variables)
- [Quick Start](#quick-start)
- [Deployment](#deployment)
- [Key Features & API Endpoints](#key-features--api-endpoints)
- [UI Notes](#ui-notes)
- [PDF Templates](#pdf-templates)
- [Security Best Practices](#security-best-practices)
- [Password Reset](#password-reset)
- [Recent Changes](#recent-changes)
- [Data Model Highlights](#data-model-highlights)
- [DB Upgrade Guide](#db-upgrade-guide)
- [Keyboard Shortcuts](#keyboard-shortcuts)
- [Development Tips](#development-tips)

---

## Overview

- CRM focused on real-estate pipelines (leads → clients → deals → tasks).
- Backend: PHP 8.x, MySQL (PDO), JSON APIs, simple MVC structure.
- Frontend: Tailwind CSS, minimal JS, server-rendered views.
- File uploads stored in `storage/` (not web-accessible in production).
- AI endpoints for summaries and follow-up suggestions.

---

## Tech Stack

- PHP 8.x, MySQL 5.7+/MariaDB 10+
- Tailwind CSS, PostCSS, minimal JS
- PHPMailer for SMTP
- MVC-style structure

---

## Folder Structure

- `public/` – Entry points, assets, views
- `src/` – App code (config, controllers, models, middleware, routes, services)
- `sql/` – Schema and migrations
- `storage/` – Logs, cache, uploads (protect in production)
- `docs/` – Documentation

---

## Environment Variables

Copy `.env.example` to `.env` and set:

```env
APP_URL=http://127.0.0.1:8765
APP_TIMEZONE=Asia/Dubai
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=crm_app
DB_USER=your_db_user
DB_PASS=your_db_password
LLM_API_URL=https://api.openai.com/v1/chat/completions
LLM_API_KEY=your_api_key
LLM_MODEL=gpt-4o-mini
SMTP_HOST=smtp.yourprovider.com
SMTP_PORT=587
SMTP_USER=your_mailbox@example.com
SMTP_PASS=your_smtp_password
SMTP_FROM=your_mailbox@example.com
SMTP_SECURE=tls
```

---

## Quick Start

1. Copy `.env.example` to `.env` and configure.
2. Import `sql/schema.sql` into MySQL.
3. Start the PHP server:
   ```
   php -S 127.0.0.1:8765 -t public
   ```
4. (Optional) Build Tailwind CSS:
   ```
   npm install
   npm run build:css
   ```
5. (Optional) Run tests:
   ```
   composer install --dev
   composer test
   ```
6. Open [http://127.0.0.1:8765/index.php?page=login](http://127.0.0.1:8765/index.php?page=login)

---

## Deployment

1. Build assets and dependencies locally.
2. Upload `public/` to your web root, keep `src/`, `vendor/`, `storage/`, `.env`, and `sql/` above web root if possible.
3. Set up `.env` on the server.
4. Import schema via phpMyAdmin.
5. Ensure PHP 8.x and required extensions are enabled.
6. Test all features live.

---

## Key Features & API Endpoints

- **Auth:** `/auth/register`, `/auth/login`, `/auth/logout`
- **Password Reset:** `/auth/forgot`, `/auth/reset`
- **Profile:** `/auth/me`, `/auth/profile`
- **Leads, Clients, Deals, Tasks:** Full CRUD via RESTful endpoints (see `src/routes/api_routes.php`)
- **AI Helpers:** `/ai/summarize`, `/ai/suggest-followup`

---

## UI Notes

- Modern, responsive design.
- Dashboard with cards and charts.
- Profile management, file uploads, and PDF previews.

---

## PDF Templates

- Place tenancy and NOC templates in `storage/templates/`.
- Previews and overlays are managed in the corresponding JS and controller files.

---

## Security Best Practices

- Protect `storage/` and `.env` in production.
- Use strong passwords and secure SMTP credentials.
- Set cookies with `HttpOnly`, `Secure`, and `SameSite=Strict`.
- Always escape user-generated content in views.
- Keep dependencies up to date.

---

## Password Reset

- `/auth/forgot` sends a 6-digit code via SMTP.
- `/auth/reset` accepts the code and new password.
- Tokens expire after 1 hour.

---

## Recent Changes

- Profile page and avatar in top bar.
- Card-based dashboard.
- Improved password reset flow.
- UI/UX enhancements.

---

## Data Model Highlights

- See `sql/schema.sql` for all required columns and relationships.
- Upgrade scripts included for legacy support.

---

## DB Upgrade Guide

- See the SQL snippets in this README for incremental upgrades and renaming.

---

## Keyboard Shortcuts

- `Esc` closes modals.
- `n` or `/` opens "New" modals.

---

## Development Tips

- Use `npm run watch:css` for live Tailwind rebuilds.
- Debug with logs in `storage/logs/app.log`.
- Ensure your DB schema matches `sql/schema.sql`.

---

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
7) (Optional) Run automated tests (requires dev dependencies):
```
composer install --dev
composer test
```
8) Open `http://127.0.0.1:8765/index.php?page=login`.

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
 - Tenancy contracts: split layout with form on the left and PDF-style preview on the right (`?page=tenancy-contracts`).

## Tenancy contract PDF
- Template: upload the official tenancy form as a flattened PDF to `storage/templates/tenancy-contract-template.pdf` (A4 size).
- Preview: `public/views/tenancy-contracts.php` renders a fixed-size (800x1130px) preview with that PDF exported as an image (`public/assets/img/tenancy-contract-template_Page1.png`), and absolutely positioned overlay fields.
- Mapping: coordinates in the preview are derived from `SetXY` positions in `src/controllers/TenancyContractController.php` and should be adjusted there first; the preview mirrors those values so the PDF is the single source of truth. The comment block above `SetXY` in that controller documents the A4 page assumption and that the preview scales an 800x1130px container from these millimeter coordinates.
- JS: `public/assets/js/tenancyContracts.js` keeps the preview in sync with the left-side form, formats dates as `dd-mm-yyyy`, and builds the annual rent string in the format `AED 120000 /-- (One Hundred Twenty Thousand Dirham Only)`.
- Download: the "Download filled PDF" button posts the form to `/api.php/tenancy-contracts/pdf`, where `TenancyContractController::downloadPdf` fills the PDF template using the form data and returns a browser-viewable PDF.

## NOC / Leasing form PDF
- Page: `?page=noc-leasing` shows a similar two-column layout: form on the left, PDF-style preview on the right.
- Template: place your flattened NOC/leasing template PDF at `storage/templates/noc-template.pdf` (A4 size). Export the same design as an image to `public/assets/img/noc-template_Page1.png` for the on-screen preview.
- Preview: `public/views/noc-leasing.php` renders an 800x1130px preview using that image, with overlay fields whose positions mirror `SetXY` coordinates in `src/controllers/NocLeasingController.php`. Adjust the controller coordinates first, then tweak the preview to match.
- JS: `public/assets/js/nocLeasing.js` keeps the preview in sync with the form, formats dates as `dd-mm-yyyy`, and builds the same combined annual rent string (`AED 120000 /-- (One Hundred Twenty Thousand Dirham Only)`) used when filling the PDF.
- Download: the "Download NOC PDF" button posts to `/api.php/noc-leasing/pdf`, where `NocLeasingController::downloadPdf` fills the NOC template with the form data and streams it back to the browser.

## Notes
- Keep secrets out of version control; use `.env.example` as your template.
- Tailwind rebuild recommended after UI tweaks: `npm run build:css`.
- PHPMailer is installed via Composer for SMTP sending.
 - Protect `storage/` and `.env` in production (e.g. via `.htaccess` or web server rules) so uploads and secrets are not directly accessible.
   - A default `storage/.htaccess` is included in the repository to deny direct access; ensure equivalent NGINX rules are applied on your server.

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
- Removed dark mode support (CSS, JS toggles, Tailwind config, and tests).
- Centralized "Hard Reload" handling in `public/assets/js/reload.js` and global modal in `public/views/layout.php`.
- CSRF validation now applies to cookie-authenticated requests; file downloads no longer accept query-string tokens.
- Debug request logging is still enabled for testing; disable before production.
- Leads API supports `created_from` and `created_to` query filters for date ranges.
- Dashboard and Reports leads charts paginate all leads, support week/month/year/custom ranges, and show separate lines for `property_for`.
- Leads table auto-refresh removed; the list now refreshes on user actions only.


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
