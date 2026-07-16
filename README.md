# Resume Positions CRUD

PHP/PDO resume database with Profile and Position CRUD, jQuery-powered position fields, flash messages, and POST-Redirect-GET.

## Database

Create a hosted MySQL database (for example, Aiven, PlanetScale, Railway, or DigitalOcean Managed MySQL), then run `schema.sql` against it. Vercel serverless functions cannot connect to a MySQL server running only on your computer.

## Vercel environment variables

In the Vercel project settings, add either:

- `DB_DSN` — e.g. `mysql:host=YOUR_HOST;port=3306;dbname=misc;charset=utf8mb4`
- `DB_USER`
- `DB_PASSWORD`

Or add the individual variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`.

For local development the default connection is `mysql:host=127.0.0.1;port=3306;dbname=misc;charset=utf8mb4`, user `fred`, password `zap`.

## Deploy

1. Import this repository into Vercel.
2. Add the database environment variables.
3. Deploy. The Vercel routes expose `/`, `/login.php`, `/add.php`, `/edit.php`, `/view.php`, and `/delete.php`.
4. Log in with `csev@umich.edu` and password `php123` (the seeded MD5 hash).

The app uses the `vercel-php` runtime defined in `vercel.json`.
