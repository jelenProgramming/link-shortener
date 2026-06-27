# Linkshort — API

A URL shortener with click analytics. Laravel REST API: create short links,
redirect visitors, and record every click (referrer, time) for per-link stats.

The React dashboard that consumes this API lives in a separate repo (`linkshort-web`).

## What it shows

- REST API design with resource routes
- Eloquent models and a one-to-many relation (`Link` → `Click`)
- Request validation (valid URL, unique custom slug)
- Aggregation queries (clicks per day, top referrers) for analytics
- A redirect endpoint that logs each visit, then 302s to the target
- CORS configured for a separate frontend origin

## Endpoints

| Method | Path                | Purpose                                  |
|--------|---------------------|------------------------------------------|
| GET    | `/api/links`        | List all links with click counts         |
| POST   | `/api/links`        | Create a link `{ url, slug? }`           |
| GET    | `/api/links/{slug}` | Link details + stats (daily, referrers)  |
| DELETE | `/api/links/{slug}` | Delete a link and its clicks             |
| GET    | `/{slug}`           | Redirect to the original URL (logs click)|

## Run locally

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve            # http://127.0.0.1:8000
```

Uses SQLite by default, so there's no database to set up.

## Deploy to Railway

1. Push this folder to its own GitHub repo.
2. On Railway: New Project → Deploy from GitHub repo.
3. Set environment variables:
   - `APP_KEY` — run `php artisan key:generate --show` locally and paste the value
   - `APP_ENV=production`, `APP_DEBUG=false`
4. Railway builds with the included `nixpacks.toml`.

SQLite works for a demo, but Railway's filesystem is ephemeral so data resets on
redeploy. For persistent stats, add a Railway Postgres and set `DB_CONNECTION=pgsql`
plus the `DB_*` vars Railway gives you.

After deploy, point the frontend's `VITE_API_URL` at your Railway URL.
