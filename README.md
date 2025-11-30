# CRM App (PHP + Tailwind + Chart.js)

Lightweight CRM for real estate/sales teams with leads, contacts, deals, tasks, AI assistant, and dashboards. Backend is PHP (no framework) with MySQL/PDO; frontend uses Tailwind CSS + vanilla JS; charts via Chart.js.

## Features
- Auth (register/login/logout) with token/session
- Leads/Contacts/Deals/Tasks CRUD; filters, bulk status (leads), table/Kanban (leads), list/calendar (tasks)
- AI Assistant: summarize notes, suggest follow-up (configurable LLM API)
- Dashboard & Reports with inline/Chart.js charts
- Client Profile page (stubbed timeline/files/notes)

## Stack
- PHP 8.x, PDO/MySQL
- Tailwind CSS (build via CLI), Chart.js (CDN)
- Vanilla JS for UI, fetch API calls to `/api.php`

## Setup
1) Copy `.env` and set DB creds, APP_URL, LLM vars:
   ```
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=crm_app
   DB_USER=your_user
   DB_PASS=your_pass
   LLM_API_URL=https://api.openai.com/v1/chat/completions
   LLM_API_KEY=sk-...
   LLM_MODEL=gpt-4o
   ```
2) Install PHP deps (optional autoload): `composer install`
3) Build Tailwind: `npm install` then `npm run build:css`
4) Import DB schema: `mysql -u user -p dbname < sql/schema.sql`
5) Run locally: `php -S localhost:8000 -t public`

## Deployment
- Web root should point to `public/`; protect `storage`, `.env`, `sql`
- Ensure PHP extensions: pdo_mysql, curl
- Set file perms for `storage/logs/app.log`
- For Apache use `.htaccess` provided; Nginx use `try_files` to `api.php`/`index.php`

## API (main endpoints)
- Auth: `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`
- Leads: `GET/POST /leads`, `PUT/DELETE /leads/{id}`, `PATCH /leads/bulk`
- Contacts: `GET/POST /contacts`, `PUT/DELETE /contacts/{id}`
- Deals: `GET/POST /deals`, `PUT/DELETE /deals/{id}`
- Tasks: `GET/POST /tasks`, `PUT/DELETE /tasks/{id}`
- AI: `POST /ai/summarize`, `POST /ai/suggest-followup`

Pagination/sort: `page`, `per_page`, `sort`, `direction` supported on list endpoints.

## Frontend notes
- Pages in `public/views/`; JS in `public/assets/js/`
- Styles from built `public/assets/css/tailwind.css`
- Charts via Chart.js CDN; falls back to inline renderers if unavailable

## Development scripts
- Build CSS: `npm run build:css`
- Watch CSS: `npm run watch:css`

## Security
- SSL verification enforced for AI calls; set proper CA bundle on the server
- Keep `.env` out of VCS and web root
- Use HTTPS in production

## Known gaps
- Client Profile data is stubbed (timeline/files/notes)
- Charts use live endpoints but minimal filtering
- Drag/drop Kanban updates lead status; other entities don’t have bulk actions yet
