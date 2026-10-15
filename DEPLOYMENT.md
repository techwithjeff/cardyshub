# Deploying Cardyshub to shared cPanel/LAMP hosting

## 1. Prepare locally before upload
- `npm run build` — compiles CSS/JS into `public/build` (no Node needed on the server).
- Double-check `.env` is **not** uploaded as-is; you'll create a fresh one on the server (see below).

## 2. Upload
Upload the whole project **except** `node_modules/`, `tools/`, and `.env` to a folder alongside (not inside) `public_html`, e.g. `~/cardyshub_app`.

## 3. Document root
In cPanel → **Domains**, set the document root for cardyshub.uk to `~/cardyshub_app/public` (not `public_html`). This is the standard, secure way to serve a Laravel app — it keeps `app/`, `.env`, and everything else outside the web-servable directory.

If your host doesn't allow a custom document root, an alternative is to upload `public/`'s contents into `public_html` and add an `index.php` there that requires the app from its real location — ask if you need this variant.

## 4. Database
Create a MySQL database + user in cPanel → **MySQL Databases**, and note the credentials.

## 5. `.env` on the server
Create `.env` in the app root with production values:
```
APP_NAME=Cardyshub
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cardyshub.uk

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=...        # your mail provider
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=hello@cardyshub.uk
MAIL_FROM_NAME=Cardyshub

STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

ADMIN_EMAIL=you@cardyshub.uk
ADMIN_PASSWORD=set-a-strong-password-then-remove-this-line-after-first-seed
```

## 6. Via SSH (or cPanel Terminal)
```bash
cd ~/cardyshub_app
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force   # creates your admin login
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
No SSH access? Most cPanel hosts offer a "Setup PHP App" or Softaculous-style Composer runner — use that to run the same commands, or ask your host's support to run them once.

## 7. Cron (for scheduled tasks / queued emails)
In cPanel → **Cron Jobs**, add one job running every minute:
```
* * * * * php /home/youruser/cardyshub_app/artisan schedule:run >> /dev/null 2>&1
```
This drives Laravel's scheduler, which processes the database queue (order confirmation emails) without needing a persistent worker process — safe for shared hosting.

## 8. Stripe webhook
In the Stripe Dashboard → **Developers → Webhooks**, add an endpoint:
```
https://cardyshub.uk/stripe/webhook
```
Subscribe to `payment_intent.succeeded`. Copy the signing secret into `STRIPE_WEBHOOK_SECRET` in `.env`.

Also enable **Link**, **Google Pay**, and **Apple Pay** under Stripe Dashboard → **Settings → Payment methods** (Apple Pay additionally requires domain verification there).

## 9. Final checks
- Visit the site over `https://` and confirm the padlock is valid (cPanel's free AutoSSL usually covers this automatically).
- Place a real test order with a Stripe test card first if you're still finishing setup — switch `STRIPE_KEY`/`STRIPE_SECRET` to test keys temporarily if needed.
- Log into `/admin` and change the seeded admin password immediately via the Customers area (or ask your developer to update it via `php artisan tinker`).
- Remove `ADMIN_PASSWORD` from `.env` after the first successful seed, so it isn't sitting in a config file long-term.
