# Linkshort API

Laravel backend for a URL shortener. Create a short slug for any URL, hand
it out, and every hit gets logged (referrer, user agent, rough location)
so the dashboard can show where clicks are coming from.

Companion frontend: `linkshort-web` (React + Vite), separate repo.

## Click data doesn't stay forever, and it isn't stored precisely

Analytics tools default to hoarding raw visitor IPs indefinitely - that's
more liability than most side projects need. This one truncates the IP
before it's written (last octet zeroed for IPv4, trailing groups zeroed for
IPv6), so a click record is good enough for coarse geo/analytics but isn't
a precise fingerprint. A daily scheduled command (`clicks:prune`, wired up
in `routes/console.php`) then deletes click rows older than 90 days. Retention
is a command-line flag (`--days=`), not a magic number buried in a query.

Link management (listing links, deleting them) requires a Sanctum token,
and is scoped to the token holder - `GET /api/links` only ever returns
links owned by the authenticated user, and deleting someone else's link
gets a 403. Creating a link and viewing its stats stay open, since that's
the actual product - anyone with a URL can shorten it, same as bit.ly or
tinyurl; a link created while logged out has no owner and won't show up
in anyone's list. The redirect itself (`GET /{slug}`) was always public
and still is; that's the one endpoint the whole app exists to serve.

## Auth

Bearer tokens via Laravel Sanctum, not sessions - `POST /api/register` or
`POST /api/login` returns a `token`, send it back as
`Authorization: Bearer <token>`.

## Endpoints

Public:

```
POST   /api/links            create a link       { url, slug? }
GET    /api/links/{slug}     link + click stats
GET    /{slug}                the actual redirect (302, logs a click)
POST   /api/register          { name, email, password }
POST   /api/login             { email, password }
```

Requires a bearer token:

```
GET    /api/links             the caller's own links, with click counts
DELETE /api/links/{slug}      remove a link you own (403 if you don't)
POST   /api/logout
GET    /api/me
```

## Data model

`Link` belongs to a `User` (nullable - anonymous creation is still allowed)
and has many `Click`. A click row is written on every redirect: truncated
IP, referrer header, user agent, timestamp. `clicks.created_at` is indexed -
the stats endpoint does a 30-day range scan grouped by day on every request,
so that index isn't optional.

## Running it

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

SQLite, no external database needed. To exercise the retention job locally:

```bash
php artisan clicks:prune --days=90
```

## Tests

```bash
php artisan test
```

Covers the auth boundary added above: guests get 401 on the list/delete
routes, a token holder only sees and can delete their own links (403 on
someone else's), and the redirect route keeps working with no auth at all.

## Deploying

The included `Dockerfile` builds a PHP 8.3 image with `pdo_pgsql` and runs
migrations on boot, so any host that accepts a Dockerfile will do.

Click data is the whole point of this app, so it needs a database that
survives a redeploy: a container filesystem does not, and neither does
SQLite sitting on one. Point `DB_URL` at managed Postgres and set
`DB_CONNECTION=pgsql`.

Environment variables to set on the host:

| Variable | Value |
|---|---|
| `APP_KEY` | output of `php artisan key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | the provider's connection string, `sslmode=require` included |

Then set the frontend's `VITE_API_URL` to the deployed URL, with no
trailing slash.
