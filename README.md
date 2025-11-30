# CRM App (PHP + Tailwind)

Lightweight CRM for real estate leads/deals/tasks with a PHP REST API, vanilla JS frontend, and optional LLM integration.

## Stack
- PHP 8.x (no framework), PDO/MySQL
- Tailwind CSS (static build)
- Vanilla JS (fetch API)

## Setup
1) Copy `.env` from sample and set DB credentials & LLM keys.
2) Create database and load schema:
   ```bash
   mysql -u YOUR_USER -pYOUR_PASS -e "CREATE DATABASE crm_app CHARACTER SET utf8mb4"
   mysql -u YOUR_USER -pYOUR_PASS crm_app < sql/schema.sql
   ```
3) Install composer autoload (optional but recommended):
   ```bash
   composer install
   composer dump-autoload
   ```
4) Tailwind CSS (if customizing styles):
   - Ensure `public/assets/css/tailwind.css` is built (configure Tailwind CLI if desired).

## Run
```bash
php -S localhost:8000 -t public
```
Then open `http://localhost:8000/index.php?page=login`.

## API routes (major)
- Auth: `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`
- Leads: `GET/POST /leads`, `PUT/DELETE /leads/{id}`
- Contacts: `GET/POST /contacts`, `PUT/DELETE /contacts/{id}`
- Deals: `GET/POST /deals`, `PUT/DELETE /deals/{id}`
- Tasks: `GET/POST /tasks`, `PUT/DELETE /tasks/{id}`
- (AI) `POST /ai/summarize`, `POST /ai/suggest-followup`

All protected endpoints require `Authorization: Bearer <token>`.

## Logging
- File: `storage/logs/app.log`
- Errors >= 500 auto-log via `Response::error` (uses `Logger` class).

## Deployment (Apache/shared hosting)
- Ensure `.htaccess` in `public/` is active for rewrites to `api.php` and `index.php`.
- Deny web access to `/storage` and `/sql` (place outside web root if possible).
- Set proper file permissions for `storage/logs/app.log`.

## Notes
- Frontend is minimal; extend Tailwind build and UI as needed.
- AI endpoints depend on valid `LLM_API_URL` and `LLM_API_KEY` in `.env`.***
