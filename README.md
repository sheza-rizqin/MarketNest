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

- PHP `8.2` and Composer 2.
- Laravel's required PHP extensions: BCMath, Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, and XML. Enable `pdo_mysql` for the configured database driver.
- MySQL or MariaDB. The checked-in environment template is configured for the `mysql` PDO driver; MariaDB 10.11 is the local target.
- Node.js `20.19.0` or `>=22.12.0` and npm (Vite 7 requirement).
- Git.

## Sharing the project through Google Drive

1. Prepare a ZIP of the project source. It should extract to one `MarketNEST-app` folder with `artisan`, `composer.json`, and `package.json` at its root.
2. Include the source folders, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `.gitignore`, `.env.example`, migrations, seeders, and tests. Make sure hidden files are visible when selecting files so `.env.example` is included.
3. Do **not** upload `.env`, a database export, `.git`, `vendor`, `node_modules`, `public/build`, `public/hot`, logs, or local uploaded files from `storage/app/public/listings`. The local `.env` can contain database credentials, and uploaded files or a database export can contain private user data. The recipient can recreate the schema and sample listings with migrations and seeders, and rebuild frontend assets.
4. Do not share real passwords or credentials in the README or Drive. If a real `.env` was uploaded accidentally, remove it from Drive and rotate its credentials.
5. In Google Drive, choose **New → File upload**, select the ZIP, and upload it. Upload this README separately if you want the setup steps easy to preview. Share the file with the intended reviewer rather than making it public unless public link access is required.

The project’s `.gitignore` protects several of these files when using Git, but Google Drive ZIP creation does not automatically apply `.gitignore`; check the ZIP contents before uploading. The sample seeder creates fictional listings and demo accounts, but it does not recreate locally uploaded listing photos. On a clean setup, listings without photos use the built-in placeholder until a seller uploads images.

## Installation

1. Install PHP 8.2, Composer 2, Node.js and npm, and MySQL or MariaDB. XAMPP can provide PHP and MySQL locally; Apache is not required when using `php artisan serve`. Start MySQL before continuing.
2. Download the project ZIP from Drive and extract it. Open PowerShell in the extracted `MarketNEST-app` folder (the folder containing `artisan` and `composer.json`). If using Git instead, clone the repository and enter its folder.
3. Create a dedicated MySQL/MariaDB database and application user. For example:

   ```sql
   CREATE DATABASE marketnest_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'marketnest_user'@'127.0.0.1' IDENTIFIED BY 'choose-a-local-password';
   GRANT ALL PRIVILEGES ON marketnest_app.* TO 'marketnest_user'@'127.0.0.1';
   ```

4. Copy the environment template:

   ```powershell
   Copy-Item .env.example .env
   ```

   Set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` to match the database you created. Keep `APP_ENV=local`, `APP_DEBUG=true`, and `APP_URL=http://127.0.0.1:8000` for local development. Never upload or share `.env`.

5. Install dependencies, generate the application key, migrate and seed the database, and build the frontend:

   ```powershell
   composer install
   npm ci
   php artisan key:generate
   php artisan migrate --seed
   npm run build
   ```

   The seeder creates fictional listings and local demo accounts. Use a new, empty development database when seeding; do not use the demo accounts or sample data in a live deployment. Do not run `migrate:fresh` on a database containing data you need.

## Storage linking and listing photos

Listing images are written through Laravel's `public` disk and served by a controller route that checks the listing policy. **Do not run `php artisan storage:link` with the current configuration:** the conventional link would expose every stored listing image as a static file, bypassing active/inactive listing authorization. A symlink is not needed for the app's current image route. Before a public deployment, move listing images to a private disk or otherwise isolate public assets and enforce access rules.

## Running the application

Start the Laravel server from the project folder:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000`. For frontend hot reload while developing on the same PC, run `npm run dev` in a second terminal. Before using a phone, stop that Vite dev server with Ctrl+C and run `npm run build`; otherwise Laravel may point the phone at a development server bound to the PC's loopback address.

To view the app on a phone on the same trusted Wi-Fi, run:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Find the PC's Wi-Fi IPv4 address with `ipconfig`, then open `http://<PC-IPv4>:8000` on the phone. Allow PHP through Windows Firewall on private networks if prompted. Do not expose this development server to the public internet.

Sellers can read enquiries from the authenticated **Messages** inbox and manage order requests from **Sales requests**. The local `.env.example` uses `MAIL_MAILER=log`; enquiry email notifications are written to the Laravel log unless a mail transport is configured. Order requests appear in the seller dashboard and do not send email or collect payment.

## Tests and code style

```powershell
php artisan test
vendor/bin/pint --test
npm run build
```

Feature tests cover authentication, ownership, listing lifecycle and images, public browsing, search filters, favorites, enquiries, cart checkout, order status transitions, and the seller dashboard.

## Common setup issues

- **MySQL connection refused:** start MySQL/MariaDB and confirm the `.env` database name, username, password, host, and port.
- **`could not find driver`:** enable PHP's `pdo_mysql` extension and restart the terminal or PHP server.
- **Missing tables:** from the project root, run `php artisan migrate --seed` against the intended empty development database.
- **Unstyled page on a phone:** stop `npm run dev`, run `npm run build`, ensure `public/hot` is absent, and reload the page using the PC's Wi-Fi IPv4 address.
- **Photos missing after downloading from Drive:** the clean setup seeds listing records but not locally uploaded images. Upload photos again through the listing form. Do not run `php artisan storage:link`; the app serves images through an authorization-checked route.

## Local demo accounts

The database seeder creates these local-only accounts:

| Role | Email | Password |
| --- | --- | --- |
| Seller | `ananya.seller@marketnest.test` | `MarketNestDemo!2026` |
| Seller | `rahul.seller@marketnest.test` | `MarketNestDemo!2026` |
| Buyer | `buyer@marketnest.test` | `MarketNestDemo!2026` |



