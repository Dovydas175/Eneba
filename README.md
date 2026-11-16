# PlayVault marketplace

A compact digital games marketplace inspired by Eneba's visual language. Built with React, TypeScript, PHP 8.3, MySQL 8, and Apache. All payment is simulated. Seeded key strings are deliberately marked as demo inventory and do not redeem on game platforms.

The full visible user-assistant development conversation is recorded in [`AI_CONVERSATION.md`](AI_CONVERSATION.md).

## Features

- Homepage hero, best sellers, and deals are fetched from MySQL-backed API data blocks.
- Store catalog with platform, region, and price filters, sorting, and search from the global header.
- PHP search includes substring matching plus similarity and edit-distance matching for common typos.
- Product pages show seller ratings, delivery details, and multiple offers.
- Registration, login, PHP sessions, password hashing, session regeneration, and CSRF checks for checkout/logout.
- Cart persists in the browser. Checkout is simulated and atomically reserves an unused digital key and decrements offer stock.
- Signed-in order history shows completed orders and reveals delivered demo keys.
- Responsive desktop and mobile UI.

## Run locally (Docker)

Requirements: Docker Desktop with Compose.

From the repository root:

```sh
docker compose up --build
```

Open [http://localhost:8080](http://localhost:8080). Compose starts MySQL, imports `backend/database/schema.sql` on first database initialization, builds the React app, and serves the frontend and `/api` from one PHP/Apache origin. To reset the seeded database, stop the app and remove its Compose volume with `docker compose down -v` (this deletes local account/order data).

The compose credentials are for local development only. For deployment, provide a managed MySQL instance and set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, and `APP_ORIGIN` on the PHP web service. `APP_ORIGIN` must exactly match the public site origin. `DB_SSL_CA` is optional for local MySQL and should point to your CA bundle for managed databases that require TLS. Set a strong database password. Serve the app over HTTPS so session cookies use the Secure flag.

## Free-tier deployment option

`render.yaml` defines a free Render Docker web service. It serves the built React app and PHP API from the same origin. The Docker entrypoint reads Render's `PORT` value so Apache listens on the platform-assigned port. Render's free web services sleep after 15 minutes without traffic and can take about a minute to wake. [Render free-service limits](https://render.com/docs/free).

Render does not offer a free managed MySQL service. One free MySQL-compatible option is TiDB Cloud Starter. It supports the MySQL protocol and provides a free MySQL-compatible quota, but it is not Oracle MySQL. [TiDB MySQL compatibility](https://docs.pingcap.com/tidbcloud/mysql-compatibility/) [TiDB Starter quotas](https://docs.pingcap.com/tidbcloud/serverless-limitations/).

1. Create a MySQL-compatible TiDB Cloud Starter instance and retrieve its host, prefixed username, password, and port (`4000`). Starter requires TLS; the Render service config points PDO at the Debian CA bundle.
2. Import `backend/database/schema.sql` into that instance with a MySQL client using TLS, or use TiDB's SQL editor.
3. Create a Render web service from this repository using the included `render.yaml` Blueprint. Set the database values and set `APP_ORIGIN` to the service's full `https://…onrender.com` URL.
4. Wait for the `/api/health` check and test registration, search, cart, simulated checkout, and order history on the public URL.

These providers can change their free-tier limits. The quoted limits were checked on 2026-11-16. The deployment still needs an authenticated Render workspace and a configured TiDB instance.

## Frontend-only development

Requires Node.js 20+ and a PHP/MySQL API at `http://localhost:8080`:

```sh
cd frontend
npm ci
npm run dev
```

Vite proxies `/api` requests to the PHP app at port 8080. For a production frontend bundle, run `npm run build` in `frontend/`.

## API overview

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/api/health` | Health check |
| GET | `/api/home` | Homepage content blocks and product shelves |
| GET | `/api/products` | Catalog, with `q`, `platform`, `region`, `min`, `max`, and `sort` query parameters |
| GET | `/api/products/{id}` | Product and available seller offers |
| GET | `/api/me` | Current signed-in account and CSRF token |
| POST | `/api/auth/register` | Create account and start a session |
| POST | `/api/auth/login` | Sign in |
| POST | `/api/auth/logout` | End session (CSRF token required) |
| POST | `/api/checkout` | Simulated payment and digital-key delivery (signed in; CSRF token required) |
| GET | `/api/orders` | Signed-in order history and delivered keys |

## Scope and demo data

The brief leaves architecture and exact feature scope open. This implementation uses a small seeded catalog and seller set so the full buyer journey can be exercised. It demonstrates an atomic simulated checkout but does not contact a payment processor. The demo keys are generated placeholders. Registration and order records are persisted to MySQL.

## Verification status

- Frontend production build: passed with `npm run build` on 2026-11-16.
- Backend PHP syntax and fuzzy-search checks: passed with the local XAMPP PHP runtime (`php backend/tests/search_test.php`).
- MySQL-compatible seed schema import: passed against isolated MariaDB 10.11, creating 12 products, 27 offers, and 270 demo keys.
- HTTP API integration: passed on 2026-11-16 against isolated PHP 8.5 + MariaDB 10.11. Checked health/home/catalog filters, typo search, product offers, registration, login, simulated checkout, and retrieval of the delivered key in order history.
- Browser UI flow: passed on 2026-11-16, including fuzzy search, product offer selection, persistent cart, registration, checkout, order history, and key reveal.
- Deployment: public Render web service at https://playvault-internship-app.onrender.com, connected to TiDB Cloud. The API health endpoint returned `{"status":"ok"}`. The service is configured by the root `render.yaml` Blueprint; verify the homepage and buyer flow after deploying.

## License and imagery

This is a non-commercial internship assignment demo, not affiliated with Eneba or the game publishers. Product artwork is loaded from IGDB image URLs; remove/replace those URLs if you do not have rights for your intended distribution.
