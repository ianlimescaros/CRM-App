# CRM App (Web + Tauri)

PHP/MySQL CRM with Tailwind UI, AI helpers, profile management, password reset, and a Tauri desktop bundle.

## Quick start (web)
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

## Tauri desktop bundle
Build output: `src-tauri/target/release/bundle/msi`
```
$env:PATH += ";$env:USERPROFILE\.cargo\bin"
cd "C:\Users\stanl\OneDrive\Desktop\Web App\CRM-Tauri\crm-tauri"
npm run tauri build
```
- Ensure `src-tauri/icons/icon1.ico` exists.
- Identifier: `com.crmapp.desktop`; window points to `http://127.0.0.1:8765`.
- Bundled PHP runtime: `src-tauri/bin/php/php.exe`.

## Install on another PC (Tauri bundle)
1) Run the MSI from `src-tauri/target/release/bundle/msi/` (e.g., `CRM App_0.1.0_x64_en-US.msi`).
2) Place a real `.env` alongside `public/` and `src/` inside the installed `app` folder (same structure as here) with the keys above.
3) Ensure the machine can reach your DB (or use a local DB). PHP is bundled; no extra install needed.

## Notes
- Keep secrets out of version control; use `.env.example` as your template.
- If you change icons, update `src-tauri/tauri.conf.json` `bundle.icon` to match.

## Recent changes (summary)
- Added profile page (`?page=profile`) with avatar/status card and editable name/email/password; backend endpoints `/auth/me` and `/auth/profile`.
- Moved profile access to a round avatar in the top bar; removed logout from the sidebar (logout now in the top bar).
- Refreshed dashboard styling to a card-based layout with soft shadows and reorganized chart/summary panels.
- Login/forgot/reset screens restyled with gradient background and pill inputs/buttons.
- Password reset flow: `/auth/forgot` logs reset URL to `storage/logs/app.log` in dev; `/auth/reset` consumes token + new password. Tokens expire after 1 hour.
- Tailwind rebuild recommended after UI tweaks: `npm run build:css` (or `npm run watch:css`).
