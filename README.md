# ROSSA Project

The Rodent Open Science Skeletal Archive, designed and developed by Internal Insights & Innovation and the Center for Regenerative Medicine and Skeletal Development at the University of Connecticut.

## Requirements

- PHP 8.2+ (8.3 is what Composer and the Sail image target)
- Composer
- Node.js 20+
- Postgres 17+ (Sail and production)
- Mailpit (or another SMTP catcher) for local email
- Python 3 with numpy and scipy (statistical analysis)

Laravel documents ways to install most of these: [Laravel 12 installation](https://laravel.com/docs/12.x/installation).

If you are not using Laravel Sail or Herd, [nvm](https://github.com/nvm-sh/nvm) is a convenient way to keep Node on version 20.

Python packages used by the stats scripts:

```bash
pip3 install -r scripts/requirements.txt
# Seeds user roles, countries, specimen types, measurements, genetic methods, animal strains, and intervention categories/types.

php artisan db:seed

# Seeds cell types and populates tags
php artisan db:seed --class=CellOntologySeeder


# For the below steps, they are documented in more detail in the docs/ directory.
# To populate molecules (& drugs)
# See docs/MOLECULES.md for instructions on downloading and processing molecule data from ChEMBL.  Data retrieval requires a manual step to obtain the download URL.


# Populate genes/gene types from alliance:
php artisan rossa:load-genes

# Populate diseases from MONDO:
php artisan rossa:load-diseases
```

Sail’s PHP image already includes Python 3, numpy, and scipy.

## Environment variables

Copy `.env.example` to `.env` and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

`.env.example` still has some Laravel skeleton defaults (notably `DB_CONNECTION=mysql`). Change at least the values below for a working local install.

### App

| Variable | Local suggestion | Notes |
| --- | --- | --- |
| `APP_NAME` | `ROSSA` | Shown in mail from-name and similar UI. |
| `APP_ENV` | `local` | |
| `APP_KEY` | generated | Required. `php artisan key:generate` fills this. |
| `APP_DEBUG` | `true` | Turn off in production. |
| `APP_URL` | `http://localhost` | Must match the URL you use in the browser (include the port if not 80). |
| `APP_TRUSTED_HOST` | `localhost` | Comma-separated hosts allowed in the `Host` header. Add extras if you use a custom hostname (e.g. `rossa.test`). |
| `APP_ENABLE_REGISTRATION` | `true` | Set `false` to hide/disable public registration. |
| `APP_ALLOW_INDEXING` | `false` | Set `true` only when search engines should index the site. |

### Database

Production and Sail use Postgres. `.env.example` currently defaults to MySQL — switch it.

**Sail** (the `pgsql` service hostname is the Compose service name):

```
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=rossa
DB_USERNAME=sail
DB_PASSWORD=password
```

**Herd / native PHP** (Postgres on the host):

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rossa
DB_USERNAME=
DB_PASSWORD=
```

`DB_*_impc` is a second Postgres connection used only by the IMPC DEXA screens. Leave it empty unless you have that dataset.

### Mail

Sail includes [Mailpit](https://github.com/axllent/mailpit). Inbox UI: http://localhost:8025

```
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

On Herd/native PHP, set `MAIL_HOST=127.0.0.1` (or your Mailpit/Mailhog host) instead of `mailpit`.

ROSSA-specific addresses:

| Variable | Purpose |
| --- | --- |
| `ROSSA_CONTACT_EMAIL` | Destination for public contact-request mail. |
| `ROSSA_ADMIN_MASS_EMAIL` | Destination for admin notices (core facility submissions, office hours, study-related mail). |

### Queues, cache, files

| Variable | Local suggestion | Notes |
| --- | --- | --- |
| `QUEUE_CONNECTION` | `sync` **or** `database` | `sync` runs jobs inline (no worker). Use `database` if you run `php artisan queue:work` or Sail’s `queue` service — needed for background stats, embargo reminders, and similar jobs. |
| `FILESYSTEM_DISK` | `local` | Use `s3` in production with the `AWS_*` vars. |
| `CACHE_STORE` | `file` | Laravel 12 reads `CACHE_STORE`, not `CACHE_DRIVER`. `.env.example` still has `CACHE_DRIVER`; set `CACHE_STORE=file` locally. |
| `SESSION_DRIVER` | `file` | Fine for local. |

### API

| Variable | Local suggestion | Notes |
| --- | --- | --- |
| `API_ENABLED` | `false` | When false, `/api/*` returns 503 and API pages show an unavailable notice. Set `true` to exercise the API. |
| `API_VERSION` | `0.1.0` | Shown in API docs (Scramble). |

### Other

| Variable | Notes |
| --- | --- |
| `PYTHON_BINARY` | Defaults to `python3`. Point at a venv binary if needed. |
| `SLACK_WEBHOOK_URL` | Optional. Used for feedback notifications. |
| `GOOGLE_TAG_MANAGER_ID` | Optional analytics. Leave empty locally. |
| `AWS_*` | Only needed when `FILESYSTEM_DISK=s3`. |

Sail-only (used by `docker-compose.yml`; `./vendor/bin/sail` sets user/group if missing):

```
APP_PORT=80
VITE_PORT=5173
FORWARD_DB_PORT=5432
FORWARD_MAILPIT_PORT=1025
FORWARD_MAILPIT_DASHBOARD_PORT=8025
```

## Running the project locally

ROSSA can run in any PHP + Postgres environment. The repo ships a Sail Compose file (`docker-compose.yml`) with Postgres 17, Mailpit, Vite, and a queue worker.

### 1. Install PHP dependencies

```bash
composer install
```

### 2. Configure `.env`

Follow [Environment variables](#environment-variables) above. At minimum: `APP_KEY`, Postgres `DB_*`, and mail.

### 3a. Laravel Sail (Compose)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan db:seed --class=CellOntologySeeder
./vendor/bin/sail npm install
```

App: http://localhost (or `http://localhost:${APP_PORT}`). Mailpit: http://localhost:8025.

Prefix later Artisan/npm commands with `./vendor/bin/sail`. Compose already runs `npm run dev` in the `vite` service, so you usually do not need a second Vite process.

### 3b. Herd / native PHP

Create the Postgres database, then:

```bash
php artisan migrate
php artisan db:seed
php artisan db:seed --class=CellOntologySeeder
npm install
npm run dev
```

Serve the app with Herd, `php artisan serve`, or your usual PHP built-in/nginx setup. Keep `npm run dev` running while you work on front-end files.

`php artisan db:seed` calls the OLS API for mouse/rat strains, so that step needs outbound network access.

### Production-like front-end build

```bash
npm run build
```

## Required seeders and reference data

A usable local app needs the default seeders plus cell types. Genes, diseases, and molecules are separate loads used when creating studies.

```bash
# Roles, countries, specimen types, measurements, genetic methods,
# OLS animal strains, subscription types, vehicles
php artisan db:seed

# Cell types and tags (CSV under database/data/cell_types/)
php artisan db:seed --class=CellOntologySeeder
```

Optional, but needed for gene / disease / molecule pickers:

```bash
# Alliance of Genome Resources — see docs/GENES.md
php artisan rossa:load-genes

# MONDO — see docs/DISEASE_LINES.md
php artisan rossa:load-diseases

# ChEMBL molecules — download URL is manual; see docs/MOLECULES.md
php artisan rossa:download-molecules [URL]
php artisan rossa:process-molecules
```

## Email, registration, and password reset

Locally, point SMTP at Mailpit (or equivalent) as in the mail vars above. Production uses the configured mass-mail credentials.

Registration visibility is controlled by `APP_ENABLE_REGISTRATION`.

## Running statistical analysis and other background jobs

Jobs include inferential statistics (Python/numpy/scipy), embargo reminders, and core-facility checks.

- `QUEUE_CONNECTION=sync`: jobs run during the request; no worker required.
- `QUEUE_CONNECTION=database`: start a worker (Sail’s `queue` service does this):

```bash
php artisan queue:work
```

Production uses Supervisor to keep the worker running.

## Managing users

## Large Study Media (Scan Datasets)

µCT source files (`.isq`, `.dcm`, `.aim`) can be attached to a study via **Phenotype Analysis → Scan Datasets**. Uploads are chunked and resumable; completed files live on the local private disk under `storage/app/private/media_uploads/{study_id}/...`.

- Back up / include that directory in ops procedures; files are not on the public disk.
- Defaults (override via env): chunk size `MEDIA_UPLOAD_CHUNK_SIZE` (5 MiB), max file size `MEDIA_UPLOAD_MAX_FILE_SIZE` (20 GiB).
- PHP `upload_max_filesize` / `post_max_size` do not need to cover the full file size—only an individual chunk.
- Completed uploads remain private and unavailable for download until a queued ClamAV scan finishes.
- Files up to 2 GiB are scanned once in place. Larger files use overlapping 2 GiB windows with 512 MiB overlap. 
- Run a dedicated worker with `php artisan queue:work --queue=malware-scans --timeout=1440`. The database queue `retry_after` must be greater than the worker timeout.
- For local development, set `MEDIA_UPLOAD_SKIP_MALWARE_SCAN=true` to bypass ClamAV and mark uploads complete immediately (no `malware-scans` worker required).

## Managing users

Preset roles from the seeder include `admin`, `researcher`, and `facility-manager`.

```bash
php artisan rossa:role {email} {role} {--remove}
```

Example:

```bash
php artisan rossa:role you@example.com admin
```
