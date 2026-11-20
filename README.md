# MarketNest

**Discover locally. Buy confidently. Sell easily.**

MarketNest is a location-based marketplace for local product and service listings. It helps people find items and services by keyword, category, and location, while giving sellers a protected area to manage their own listings.
## Problem statement

Local listings can be difficult to discover when they are scattered across broad marketplaces and social platforms. MarketNest organizes listings by type, category, and city, provides shareable search filters, and lets interested visitors contact sellers without publishing their email addresses or phone numbers.

## Features

- Registration, login, logout, password reset and account profile management.
- Public homepage, category and subcategory browsing, city pages, and city/category pages.
- Listing search by title and description, with type, category, location, price, sorting, and pagination filters.
- Seller owned product and service listing creation and management.
- Multiple listing photos, image previews, primary photo selection, ordering, and removal.
- Active only public listings, seller favorites, and rate limited enquiries.
- Seller dashboard with listing counts, recent listings, a paginated management page, and a private enquiry inbox.
- A session cart for products and services, with checkout creating separate unpaid order requests for each seller.
- Buyer order history and seller request management, including pending, accepted, declined, completed, and cancelled states.
- Responsive Blade interface with reusable components and Alpine.js interactions.

Cart checkout records a request only: it does not collect payment, reserve inventory, or guarantee availability. Listings with contact pricing remain quote requests, and sellers confirm the final price and fulfillment details.

## Technology stack

- PHP 8.2 or newer in the PHP 8.x series; Laravel 12.
- MySQL-compatible database (MariaDB 10.11 is used for local development).
- Laravel Blade, Eloquent ORM, Form Requests, policies, and session authentication.
- Tailwind CSS 4, Alpine.js 3, and Vite 7.
- Pest/PHPUnit feature tests and Laravel Pint.

## Architecture

- `routes/web.php` defines public marketplace routes and authentication-protected seller routes. `routes/auth.php` contains the Laravel authentication routes.
- Controllers handle HTTP requests and return Blade views. Public listing search/details are in `PublicListingController`, cart and order requests are handled by `CartController`, `CheckoutController`, `BuyerOrderController`, and `SellerOrderController`, and city/category pages are in `BrowseController`.
- Form Request classes validate listing, search, image, enquiry, cart, checkout, and order status input. `ListingPolicy` and `OrderPolicy` enforce server-side visibility, ownership, and allowed order transitions.
- `PublicListingQuery` contains reusable active-listing filters. `ListingImageManager` handles image uploads, ordering, primary selection, and removal.
- `resources/views` contains the responsive Blade templates and reusable components. Tailwind and Vite build the frontend assets.

## Database design and Eloquent relationships

| Table | Purpose |
| --- | --- |
| `users` | Seller and buyer accounts, including profile fields. |
| `categories` | Self-referencing category groups, categories, and subcategories. |
| `listings` | Seller-owned product/service listings, prices, location, and status. |
| `listing_images` | Listing image paths, primary-image flags, and display order. |
| `favorites` | User-to-listing favorites, unique per user/listing pair. |
| `enquiries` | A visitor's name, email, message, and the associated listing. |
| `orders` | A buyer's unpaid request to one seller, including status and optional note. |
| `order_items` | Listing title/type/price snapshots preserved with each request. |

Important relationships:

- `User` has many listings, buyer orders, seller orders, and favorite listings.
- `Category` belongs to a parent category, has children, and has listings.
- `Listing` belongs to a seller and category, may belong to a subcategory, and has images, favorites, enquiries, and order items.
- `Order` belongs to a buyer and seller and has order items. `OrderItem` belongs to an order and retains a nullable link to its original listing.
- `ListingImage` and `Enquiry` belong to a listing. `Favorite` belongs to a user and listing.

Foreign keys, unique constraints, and indexes protect common relationships and filters. Listing records store only the seller's user ID; public listing pages display the seller's name, not their contact details.

## Requirements

- PHP `^8.2` and Composer 2.
- Laravel's required PHP extensions: BCMath, Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, and XML. Enable `pdo_mysql` for the configured database driver.
- MySQL or MariaDB. The checked-in environment template is configured for the `mysql` PDO driver; MariaDB 10.11 is the local target.
- Node.js `^20.19.0` or `>=22.12.0` and npm (Vite 7 requirement).
- Git.

## Installation

1. Clone the repository and enter the project directory.
2. Create a dedicated MySQL/MariaDB database and application user. For example:

   ```sql
   CREATE DATABASE marketnest_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'marketnest_user'@'127.0.0.1' IDENTIFIED BY 'choose-a-local-password';
   GRANT ALL PRIVILEGES ON marketnest_app.* TO 'marketnest_user'@'127.0.0.1';
   ```

3. Copy the environment template:

   ```powershell
   Copy-Item .env.example .env
   ```

   Set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` to match the database you created. Do not commit `.env`.

4. Install dependencies, generate the application key, migrate and seed the database, and build the frontend:

   ```powershell
   composer install
   npm ci
   php artisan key:generate
   php artisan migrate --seed
   npm run build
   ```

   The seeder creates fictional listings and local demo accounts. Use a disposable development database when seeding; never use the demo accounts or sample data in a live deployment.

## Storage linking and listing photos

Listing images are written through Laravel's `public` disk and served by a controller route that checks the listing policy. **Do not run `php artisan storage:link` with the current configuration:** the conventional link would expose every stored listing image as a static file, bypassing active/inactive listing authorization. A symlink is not needed for the app's current image route. Before a public deployment, move listing images to a private disk or otherwise isolate public assets and enforce access rules.

## Running the application

Start the Laravel development server:

```powershell
php artisan serve
```

Open the URL printed by Artisan (typically `http://127.0.0.1:8000`). For frontend hot reload during development, run `npm run dev` in a second terminal. Sellers can read enquiries from the authenticated **Messages** inbox and manage order requests from **Sales requests**. The local `.env.example` uses `MAIL_MAILER=log`; enquiry email notifications are written to the Laravel log unless a mail transport is configured. Order requests currently appear in the seller dashboard and do not send email or collect payment.

## Tests and code style

```powershell
php artisan test
vendor/bin/pint --test
npm run build
```

Feature tests cover authentication, ownership, listing lifecycle and images, public browsing, search filters, favorites, enquiries, cart checkout, order status transitions, and the seller dashboard.

## Local demo accounts

The database seeder creates these local-only accounts:

| Role | Email | Password |
| --- | --- | --- |
| Seller | `ananya.seller@marketnest.test` | `MarketNestDemo!2026` |
| Seller | `rahul.seller@marketnest.test` | `MarketNestDemo!2026` |
| Buyer | `buyer@marketnest.test` | `MarketNestDemo!2026` |

These are sample credentials for a local seeded database, not production credentials. Replace or remove them before any live deployment.


