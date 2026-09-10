# Order & Inventory Management System (RetailerPOS)

A production-ready Order and Inventory Management System backend API and Blade + Tailwind CSS UI built with **Laravel 12** and **PHP 8.2+**, adhering strictly to the **Controller ➔ Service ➔ Repository** architectural pattern.

---

# PHASE 1 — How This Application Was Built From Scratch

## 1. System Architecture & Core Design Decisions

This application was engineered from the ground up to support high-concurrency retail point-of-sale (POS) operations, automated inventory management, and background order processing.

### The 3-Tier Layered Architecture

Every feature in the system adheres strictly to the **Controller ➔ Service ➔ Repository** flow:

```
[ HTTP Request (Web Blade / JSON API) ]
                 │
                 ▼
 [ Controller Layer (Api/* & Web) ]   ── Thin controllers (Form Request validation, HTTP responses)
                 │
                 ▼
 [ Service Layer (app/Services/*) ]    ── Business rules, DB transactions, pessimistic locking, queue/event dispatching
                 │
                 ▼
 [ Repository Layer (app/Repositories) ] ── Pure DB queries, all persistence methods prefixed with 'op'
                 │
                 ▼
 [ Database (MySQL 8.0+ / Redis WSL) ]
```

1. **Controller Layer (`app/Http/Controllers/`)**:
   - Web Controllers (`OrderController.php`): Handle Blade view rendering (`index`, `create`, `show`, `store`).
   - API Controllers (`Api/OrderController.php`, `Api/ProductController.php`, `Api/CustomerController.php`): Return structured JSON responses formatted with Laravel API Resources (`OrderResource`, `ProductResource`, `CustomerResource`).
   - Both Web and API controllers delegate all business logic to the **SAME** Service layer, ensuring zero duplicate logic.

2. **Service Layer (`app/Services/`)**:
   - Enforces business rules: customer lookup/auto-registration, stock availability checks, tax calculation (`PHP_ROUND_HALF_UP`), and atomic stock deductions.
   - Encapsulates database transactions (`DB::transaction()`) with pessimistic row locking (`SELECT FOR UPDATE`) to prevent overselling.
   - Dispatches background queue jobs (`SendOrderConfirmationJob`) safely after commit via `DB::afterCommit()`.

3. **Repository Layer (`app/Repositories/`)**:
   - Houses all database queries using Eloquent and Query Builder.
   - Every repository method strictly uses the **`op` method naming convention** (`opFindById`, `opGetLowStockProducts`, `opDecrementStockForUpdate`, `opGetOrderHistoryByEmail`, `opCreateMany`, `opFindOrCreateByEmail`).
   - Concrete repository classes (`ProductRepository`, `CustomerRepository`, `OrderRepository`, `OrderItemRepository`) are registered under `namespace App\Repositories;` and auto-wired directly into Services.

---

## 2. Explanation of `app/Http/Controllers/Controller.php`

The file [`app/Http/Controllers/Controller.php`](file:///c:/Users/shifa/Documents/RetailerPOS_Assessement/app/Http/Controllers/Controller.php) defines the abstract parent class `App\Http\Controllers\Controller`:

```php
namespace App\Http\Controllers;

abstract class Controller
{
    // Base controller class
}
```

### Why it is mandatory in the codebase:
1. **Object-Oriented Inheritance**: All application controllers (`OrderController`, `Api\OrderController`, `Api\ProductController`, `Api\CustomerController`) extend this base class (`class OrderController extends Controller`).
2. **PHP Class Autoloading**: When PHP processes an HTTP request or runs PHPUnit tests, Composer's autoloader resolves the controller class hierarchy. If `Controller.php` is missing, PHP throws a fatal error: `Class "App\Http\Controllers\Controller" not found`.
3. **Central Extension Point**: Serves as the central location for base controller functionality, global middleware, and authorization helpers.

---

## 3. Database Schema & Migration Design

The database schema is fully normalized and built using Laravel migrations:

- **`products`**: `id`, `name`, `code` (unique index), `price_per_unit` (decimal 12,2), `tax_percentage` (decimal 5,2), `stock_on_hand` (integer, index), `timestamps`, `softDeletes`.
- **`customers`**: `id`, `name`, `email` (unique index), `timestamps`, `softDeletes`.
- **`orders`**: `id`, `customer_id` (FK to `customers`, `onDelete('cascade')`), `subtotal` (decimal 12,2), `tax_amount` (decimal 12,2), `grand_total` (decimal 12,2), `status` (enum: `pending`, `confirmed`, `cancelled` cast to `OrderStatus` backed enum), `timestamps`.
- **`order_items`**: `id`, `order_id` (FK to `orders`, `onDelete('cascade')`), `product_id` (FK to `products`, `onDelete('restrict')`), `quantity` (integer), `unit_price` (decimal 12,2 snapshot at purchase time), `line_total` (decimal 12,2), `timestamps`.

---

## 4. Concurrency-Safe Stock Deduction & Background Queue

### Pessimistic Stock Locking
To prevent race conditions when two customers attempt to purchase the last available unit simultaneously:
1. `OrderService` opens a DB transaction (`DB::transaction()`).
2. `ProductRepository::opFindByIdForUpdate($id)` issues `SELECT * FROM products WHERE id = ? FOR UPDATE`.
3. `ProductRepository::opDecrementStockForUpdate($id, $qty)` executes an atomic update:
   ```sql
   UPDATE products SET stock_on_hand = stock_on_hand - ? WHERE id = ? AND stock_on_hand >= ?
   ```
4. If affected rows equal `0`, an `InsufficientStockException` is thrown, triggering an automatic transaction rollback.

### Background Mail Job (`SendOrderConfirmationJob`)
- Dispatched via `SendOrderConfirmationJob::dispatch($order)` upon successful order commit.
- Uses `ShouldQueue` with traits: `use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;`.
- `SerializesModels` serializes only the model ID (`App\Models\Order` + `id`), re-hydrating the model instance from the database when processed by the Redis queue worker (`php artisan queue:listen`).
- Mail delivery is executed via configured SMTP (`MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`).

---

## 5. Frontend Architecture (Blade + Tailwind CSS + Vanilla JS + jQuery)

- **Layout Structure**: `<x-app-layout>` component wrapping `resources/views/layouts/app.blade.php`, incorporating a fixed header (`layouts.header.blade.php`), a fixed footer (`layouts.footer.blade.php`), and a scrollable content area (`main.overflow-y-auto`).
- **Interactive Billing Form (`orders/create.blade.php`)**:
  - Live customer lookup via debounced `oninput` calling jQuery `$.ajax({ url: '/api/customer/' + email })`.
  - Dynamic line item table: adding/removing products, recalculating subtotal, tax, and grand total in real-time.
  - Cash Change Return Calculator: calculates exact change return with bill denomination breakdowns (`1x$100 + 1x$50 + 1x$20...`).
- **Printable Receipt View (`orders/show.blade.php`)**:
  - Features `@media print` rules and `print:hidden` classes to print only the receipt card when clicking **"Print Bill"** (`window.print()`), hiding browser headers, footers, and action buttons.
- **Zero Unused Dependencies**: Alpine.js and Axios were removed to maintain a lightweight, fast frontend bundle compiled via Vite (`@vite(['resources/css/app.css', 'resources/js/app.js'])`).

---

## 6. Documented Technical Assumptions

1. **Tax & Rounding Rules**: Tax is computed per product item based on `tax_percentage` and rounded using standard half-up rounding (`PHP_ROUND_HALF_UP`) to 2 decimal places (`${{ number_format($value, 2) }}`).
2. **Low-Stock Threshold**: Default threshold is set to `5` in `config/inventory.php` (`config('inventory.low_stock_threshold')`) and can be overridden via `?threshold={n}` query parameter.
3. **Redis & WSL Infrastructure**: Redis Server is installed and managed via Windows Subsystem for Linux (WSL), listening on `127.0.0.1:6379` (`CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`, `REDIS_CLIENT=predis`).
4. **SMTP Mail Transport**: Production SMTP mailing is configured via `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_ENCRYPTION=tls`.

---

# PHASE 2 — How to Run This Application

## 1. Prerequisites (Mandatory Setup)

Before running the application, ensure the following software tools are installed on your machine:

| Requirement | Description & Recommended Tool | Official Download Link |
| :--- | :--- | :--- |
| **Local Server Stack** | XAMPP (Apache + MySQL) / Laragon / Herd | [Download XAMPP](https://www.apachefriends.org/download.html) |
| **PHP Runtime** | PHP 8.2+ or 8.3+ (bundled with XAMPP or standalone) | [Download PHP](https://www.php.net/downloads) |
| **PHP Dependency Manager** | Composer 2.x | [Download Composer](https://getcomposer.org/download/) |
| **Database Server** | MySQL 8.0+ or MariaDB 10.4+ (Port `3306`) | [Download MySQL](https://dev.mysql.com/downloads/installer/) |
| **Redis Server (WSL)** | Redis Server installed via WSL (Windows Subsystem for Linux, Port `6379`) | [Download WSL / Redis](https://redis.io/docs/latest/operate/oss_and_stack/install/install-redis/install-redis-on-windows/) |
| **Node.js & npm** | Node.js LTS (v18+ or v20+) and npm for Vite compilation | [Download Node.js](https://nodejs.org/en/download) |
| **Version Control** | Git | [Download Git](https://git-scm.com/downloads) |
| **API Testing Client** | Postman or Insomnia | [Download Postman](https://www.postman.com/downloads/) |

### Verify Installations

Run the following commands in your terminal to verify that all prerequisites are installed correctly:

```bash
php -v          # Must be PHP 8.2.0 or higher
composer -V     # Must be Composer 2.x
mysql --version # Must be MySQL 8.0+ or MariaDB 10.4+
wsl redis-cli ping # Should return PONG (Redis via WSL on port 6379)
node -v         # Must be Node v18+
npm -v          # Must be npm v9+
```

---

## 2. Server Ports & Configuration Summary

| Service | Host / IP | Port | Config Variable |
| :--- | :--- | :--- | :--- |
| **Laravel Web App** | `http://127.0.0.1` | **`8000`** | `APP_URL=http://127.0.0.1:8000` |
| **MySQL Database** | `127.0.0.1` | **`3306`** | `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=retailer_pos` |
| **Redis Cache / Queue (WSL)** | `127.0.0.1` | **`6379`** | `REDIS_HOST=127.0.0.1`, `REDIS_PORT=6379`, `REDIS_CLIENT=predis` |
| **SMTP Mail Server** | `smtp.gmail.com` / `127.0.0.1` | **`587`** / **`1025`** | `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587` |

---

## 3. Step-by-Step Local Setup Guide

### Step 1: Clone Repository & Create `.env`
```bash
git clone <repository-url>
cd RetailerPOS_Assessement
cp .env.example .env
```

### Step 2: Install PHP & Node Dependencies
```bash
composer install
npm install
npm run build
```

### Step 3: Configure Environment Variables (.env)
Verify that `.env` is configured with your database, WSL Redis, and SMTP credentials:
```ini
APP_NAME=RetailerPOS
APP_ENV=local
APP_KEY=base64:cyEFJitsUAo34Cc6ksaBIcUSCzfcGnEQ3zoHR6h2tqM=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=retailer_pos
DB_USERNAME=root
DB_PASSWORD=

# Core Drivers & Redis via WSL
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# SMTP Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 4: Generate Application Key & Run Database Migrations
```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

---

## 4. Running the Application

Open separate terminal windows to run the required background processes:

### Terminal 1: Start Redis Server inside WSL (Linux Subsystem)
```bash
wsl sudo service redis-server start
```

### Terminal 2: Start Laravel Development Server (Port `8000`)
```bash
php artisan serve --port=8000
```
> App URL: **`http://127.0.0.1:8000`**

### Terminal 3: Start Vite Frontend Asset Server (Optional for hot reload)
```bash
npm run dev
```

### Terminal 4: Start Redis Queue Worker for Asynchronous Mail Jobs
```bash
php artisan queue:listen redis --tries=3 --backoff=10
```

### Terminal 5: Run Automated Test Suite
```bash
php artisan test
```

---

## 5. Web UI Walkthrough

Access the web application in your browser at **`http://127.0.0.1:8000`**:

1. **New Order Billing Screen (`http://127.0.0.1:8000/orders/create`)**:
   - Type a customer email (e.g., `john@example.com`) to trigger the live `$.ajax` lookup.
   - Select products from the dropdown, enter quantities, and click **"+ Add Product"**.
   - Review live computed Subtotal, Tax, and Grand Total.
   - View the yellow **Low Stock Products** sidebar alert card.
   - Enter **Amount Given by Customer** to view the live change return denomination breakdown.
   - Click **"⚡ Generate Bill"** to submit.

2. **Printable Bill / Invoice View (`http://127.0.0.1:8000/orders/{id}`)**:
   - Displays invoice number, customer details, line items snapshot, and grand total.
   - Click **"Print Bill"** (`window.print()`) to view the print-formatted receipt (all headers/footers automatically hidden).

3. **Order History Screen (`http://127.0.0.1:8000/orders`)**:
   - Displays all historical orders with order IDs, customer details, line item count, status badges (`CONFIRMED`, `PENDING`, `CANCELLED`), and quick links to view bills.

---

## 6. Complete API Documentation & Endpoints

### 1. Create Order (`POST /api/order`)
Creates an order with pessimistic stock locking and queued confirmation mail.

- **URL**: `http://127.0.0.1:8000/api/order`
- **Method**: `POST`
- **Headers**: `Content-Type: application/json`, `Accept: application/json`

**Sample Request Body**:
```json
{
  "customer": {
    "name": "John Doe",
    "email": "john@example.com"
  },
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 1
    }
  ]
}
```

**cURL Request**:
```bash
curl -X POST http://127.0.0.1:8000/api/order \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "customer": {
      "name": "John Doe",
      "email": "john@example.com"
    },
    "items": [
      { "product_id": 1, "quantity": 2 },
      { "product_id": 2, "quantity": 1 }
    ]
  }'
```

**Sample 201 Created Response**:
```json
{
  "data": {
    "id": 1,
    "status": "CONFIRMED",
    "subtotal": 150.00,
    "tax_amount": 15.00,
    "grand_total": 165.00,
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Wireless POS Scanner",
        "quantity": 2,
        "unit_price": 50.00,
        "line_total": 100.00
      }
    ],
    "created_at": "2026-09-10T14:00:00.000000Z"
  }
}
```

**Sample 422 Insufficient Stock Error**:
```json
{
  "message": "Insufficient stock for product: Wireless POS Scanner (Requested: 50, Available: 5)",
  "code": "INSUFFICIENT_STOCK"
}
```

---

### 2. Fetch Low Stock Products (`GET /api/product/low-stock`)
- **URL**: `http://127.0.0.1:8000/api/product/low-stock?threshold=5`
- **Method**: `GET`

**cURL Request**:
```bash
curl -X GET "http://127.0.0.1:8000/api/product/low-stock?threshold=5" \
  -H "Accept: application/json"
```

**Sample 200 OK Response**:
```json
{
  "data": [
    {
      "id": 3,
      "name": "Thermal Receipt Paper Roll 80mm",
      "code": "PRD-PAPER-003",
      "price_per_unit": 3.50,
      "stock_on_hand": 2,
      "is_low_stock": true
    }
  ]
}
```

---

### 3. Customer Lookup by Email (`GET /api/customer/{email}`)
- **URL**: `http://127.0.0.1:8000/api/customer/john@example.com`
- **Method**: `GET`

**cURL Request**:
```bash
curl -X GET "http://127.0.0.1:8000/api/customer/john@example.com" \
  -H "Accept: application/json"
```

**Sample 200 OK Response**:
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

### 4. Customer Order History (`GET /api/customer/{email}/orders`)
- **URL**: `http://127.0.0.1:8000/api/customer/john@example.com/orders`
- **Method**: `GET`

**cURL Request**:
```bash
curl -X GET "http://127.0.0.1:8000/api/customer/john@example.com/orders" \
  -H "Accept: application/json"
```

---

## 7. Troubleshooting & FAQ

1. **Database Connection Error**:
   - Ensure MySQL is running on port `3306` (start MySQL in XAMPP control panel).
   - Create database manually if not auto-created: `CREATE DATABASE retailer_pos;`.

2. **Redis Connection Error (WSL)**:
   - Ensure Redis service is started inside WSL: `wsl sudo service redis-server start`.
   - Test connectivity with `wsl redis-cli ping` (should return `PONG`).

3. **SMTP Email Errors**:
   - Ensure `MAIL_HOST=smtp.gmail.com` and `MAIL_PORT=587` with valid app credentials.
