# CRM App (Web)

PHP/MySQL CRM with Tailwind UI, AI helpers, profile management, and password reset.

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

## Key features/endpoints
- Auth: `/auth/register`, `/auth/login`, `/auth/logout`
- Password reset: `/auth/forgot` (logs reset link in `storage/logs/app.log` for dev) and `/auth/reset` (token + new password)
- Profile: `/auth/me`, `/auth/profile` (update name/email/password) and UI at `?page=profile`
- CRM APIs: leads, contacts, deals, tasks (see `src/routes/api_routes.php`)

## UI notes
- Login/reset: gradient background, pill inputs/buttons
- Dashboard: card-based layout with charts, quick stats
- Profile: two-column card (avatar/status + editable details)
- Sidebar: icon nav; profile avatar + logout live in the top bar

## Notes
- Keep secrets out of version control; use `.env.example` as your template.
- Tailwind rebuild recommended after UI tweaks: `npm run build:css`.

## Recent changes (summary)
- Added profile page (`?page=profile`) with avatar/status card and editable name/email/password; backend endpoints `/auth/me` and `/auth/profile`.
- Moved profile access to a round avatar in the top bar; removed logout from the sidebar (logout now in the top bar).
- Refreshed dashboard styling to a card-based layout with soft shadows and reorganized chart/summary panels.
- Login/forgot/reset screens restyled with gradient background and pill inputs/buttons.
- Password reset flow: `/auth/forgot` logs reset URL to `storage/logs/app.log` in dev; `/auth/reset` consumes token + new password. Tokens expire after 1 hour.
- Tailwind rebuild recommended after UI tweaks: `npm run build:css` (or `npm run watch:css`).
