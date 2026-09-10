You are an expert Laravel developer. Build a production-ready "Order & Inventory Management System" backend API and Blade + Tailwind CSS UI. Treat this as a real product you are shipping — not a demo or exercise. Use the LATEST stable versions of PHP (8.2+) and Laravel (12.x) with modern syntax (constructor property promotion, enums, typed properties, PHP 8 match expressions, readonly properties, Attribute closures, modern casts, etc.).

=====================================================
1. ARCHITECTURE — STRICT LAYERED PATTERN
=====================================================
Every feature MUST follow this exact 3-layer flow:

Controller -> Service -> Repository

CONTROLLER
- Keep API controllers thin (no business logic, no DB queries) — Form Requests handle validation.
- This project has BOTH an API layer (JSON, consumed by external consumers or AJAX) AND a Blade+Tailwind UI layer. Use separate controllers for each concern:
  - app/Http/Controllers/Api/*Controller.php — JSON API, uses API Resources, RESTful methods: index, store, show, update, destroy.
  - app/Http/Controllers/*Controller.php (web) — renders Blade views, uses standard RESTful web controller methods: index, create, store, show, edit, update, destroy.
- Both web and API controllers call the SAME Service layer — no duplicated business logic between them.
- Abstract parent class app/Http/Controllers/Controller.php MUST exist as the base class for inheritance across all web and API controllers.

SERVICE (app/Services)
- Contains ALL business logic (business-rule validation, orchestration, DB transactions, dispatching jobs/events).
- Calls the Repository layer for persistence — NEVER touches Eloquent models directly for database queries.
- Method naming convention: verb + Entity, e.g.:
  - storeOrder(), storeCustomer(), fetchCustomerOrderHistory(), fetchLowStockProducts(), deductStock()

REPOSITORY (app/Repositories)
- Contains ALL DB operations (Eloquent queries, transactions at query level, eager loading, pessimistic locking).
- Method naming convention: MUST start with "op", e.g.:
  - opCreate(), opFindByEmail(), opUpdateStock(), opGetOrderHistory(), opGetLowStockProducts(), opDecrementStockForUpdate()
- Repository concrete classes live in app/Repositories/ and auto-wire cleanly into Services.

=====================================================
2. DOMAIN & SCHEMA
=====================================================
Design a normalized schema via migrations. Required tables:

- products: id, name, code (unique), price_per_unit (decimal 12,2), tax_percentage (decimal 5,2), stock_on_hand (integer), timestamps, soft deletes
- customers: id, name, email (unique), timestamps, soft deletes
- orders: id, customer_id (FK), subtotal, tax_amount, grand_total, status (enum: pending/confirmed/cancelled cast to OrderStatus backed enum), timestamps
- order_items: id, order_id (FK), product_id (FK), quantity, unit_price (snapshot at order time), line_total, timestamps

Use foreign keys with explicit onDelete behavior, indexes on frequently queried columns (email, code, stock_on_hand), and Eloquent relationships:
- Customer hasMany Order
- Order belongsTo Customer, Order hasMany OrderItem
- OrderItem belongsTo Order, OrderItem belongsTo Product
- Product hasMany OrderItem

Use eager loading (with()) everywhere to avoid N+1 queries.

=====================================================
3. FUNCTIONAL REQUIREMENTS
=====================================================

A) POST /api/order — Create Order
   - Request: customer { name, email }, items: [{ product_id, quantity }, ...]
   - Form Request validates structure, types, and required fields.
   - Service (storeOrder):
     - Find-or-create customer by email.
     - Validate stock availability for every line item BEFORE committing.
     - Compute subtotal, tax (per product tax_percentage), grand_total — use consistent decimal rounding (PHP_ROUND_HALF_UP).
     - Wrap operation in a DB transaction with pessimistic row locking (SELECT ... FOR UPDATE).
     - Deduct stock safely with atomic decrements.
     - Persist order + order_items via Repository.
     - Dispatch a queued Job (SendOrderConfirmationJob) only after successful commit (DB::afterCommit()).
     - Fire an OrderCreated event for peripheral audit logging.
   - Return created order via OrderResource.
   - On insufficient stock: return clean 422 JSON error with transaction rollback verified.

B) GET /api/customer/{email}/orders — Customer order history
   - Repository: opGetOrderHistoryByEmail() with eager-loaded items.product and customer.

C) GET /api/product/low-stock?threshold={n} — Low stock products
   - threshold query param with default sourced from config('inventory.low_stock_threshold').
   - Repository: opGetLowStockProducts(int $threshold).

D) Queued Job — Order Confirmation (SendOrderConfirmationJob)
   - php artisan make:job SendOrderConfirmationJob, implements ShouldQueue.
   - Uses traits: use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;.
   - SerializesModels ensures only model ID is stored in Redis queue payload.
   - Mail transport uses configured SMTP (MAIL_MAILER=smtp, MAIL_HOST=smtp.gmail.com, MAIL_PORT=587).

=====================================================
4. ROUTING, SECURITY & CONFIG
=====================================================
- Group routes cleanly inside routes/api.php and routes/web.php.
- Resource route prefixes: /api/order, /api/product, /api/customer.
- Tunable config options stored in config/inventory.php.
- Web server port: 8000 (http://127.0.0.1:8000).
- MySQL DB port: 3306.
- Redis port: 6379 via WSL (CACHE_STORE=redis, QUEUE_CONNECTION=redis, SESSION_DRIVER=redis).

=====================================================
4A. FRONTEND UI — TAILWIND CSS + BLADE
=====================================================
- Layout shell (<x-app-layout>) wrapping resources/views/layouts/app.blade.php with sticky header (layouts/header.blade.php), sticky footer (layouts/footer.blade.php), and scrollable main content area (main.overflow-y-auto).
- Vanilla JS + jQuery $.ajax for frontend interactivity (Zero Alpine.js, Zero Axios dependencies).
- Screens:
  - orders/create.blade.php — New Order counter-staff billing screen with email auto-lookup via $.ajax, live tax/total calculations, low-stock sidebar card, and cash change return denomination breakdown calculator.
  - orders/show.blade.php — Printable bill receipt view formatted with @media print and print:hidden rules so window.print() prints only the invoice card.
  - orders/index.blade.php — Order history view with search and status badges.

=====================================================
5. README.md — MANDATORY TWO PHASES
=====================================================
PHASE 1 — "How This Application Was Built From Scratch"
- Detailed narrative of architecture decisions, DB migrations, 3-layer pattern, Controller.php inheritance explanation, concurrency safety, queued jobs with Redis via WSL, SMTP mail driver config, Blade + Tailwind UI, and documented assumptions.

PHASE 2 — "How to Run This Application"
- Prerequisites listed FIRST with official download links (XAMPP / PHP 8.2+, Composer, MySQL, Redis via WSL, Node.js + npm, Git, Postman).
- Server ports (Web: 8000, MySQL: 3306, Redis WSL: 6379, SMTP: 587/1025).
- Step-by-step setup (.env, composer install, npm install, npm run build, migrate --seed, serve --port=8000, wsl redis-server start, queue:listen redis).
- Complete cURL / Postman requests and sample JSON responses for all endpoints.
- UI walkthrough and troubleshooting guide.
