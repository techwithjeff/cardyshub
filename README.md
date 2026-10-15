# Cardyshub

A full-stack e-commerce storefront for a UK greeting-card and gift retailer, built with Laravel 12.
It covers the whole path from browsing a catalogue to a paid, confirmed order — storefront, cart,
Stripe checkout, customer accounts, and an admin back office.

## What it does

**Storefront**
- Product catalogue with categories, brands, variants and multiple images
- Search and filtering, brand pages, and curated homepage rails
- Customer reviews on product pages
- Content pages for delivery, returns, FAQs, terms, privacy and cookies

**Cart and checkout**
- Guest carts that merge into the user's cart on login
- Delivery pricing rules and a pickup-address option
- Stripe payment with a webhook listener so orders still settle if the
  customer closes the tab before redirect
- Order confirmation email to the customer, new-order alert to the shop

**Customer accounts**
- Registration and login via Laravel Breeze
- Order history, saved addresses and a wishlist

**Admin back office** (Filament 5)
- Resources for products, variants, images, categories, brands, orders,
  discounts, reviews, contact submissions, wholesale enquiries and newsletter signups
- Dashboard widgets and an editable site-settings page

**Trade**
- A wholesale enquiry form with rate limiting, feeding the admin panel

## Stack

| Layer    | Choice                                          |
|----------|-------------------------------------------------|
| Backend  | PHP 8.2, Laravel 12                              |
| Admin    | Filament 5                                       |
| Auth     | Laravel Breeze                                   |
| Payments | Stripe (`stripe-php`), webhook-confirmed         |
| Frontend | Blade, Tailwind CSS, Alpine.js, Vite             |
| Database | MySQL                                            |
| Testing  | PHPUnit — 29 feature and unit test files         |

## Notable bits

- **Webhook-confirmed orders.** Checkout does not trust the browser redirect. The
  Stripe webhook is the source of truth, so an order still completes if the customer
  disappears mid-payment. `OrderConfirmationResilienceTest` covers this.
- **Cart merging.** An anonymous visitor can fill a cart and then log in without losing it.
- **Service layer.** `CartService` and `OrderService` keep pricing and order
  construction out of the controllers.
- **Rate limiting** on every public form — contact, wholesale, newsletter, checkout.

## Running it locally

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set your database credentials and Stripe test keys in `.env`, then:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

The storefront is at `http://localhost:8000` and the admin panel at `/admin`.

Run the tests with:

```bash
php artisan test
```

## Notes

Deployment scripts, live environment files and database dumps are deliberately
kept out of this repository.
