# Backend Architecture & System Design: Ready eCommerce (Apolo)

A comprehensive deep-dive into the Laravel-based backend database structure, routing mechanics, architectural patterns, and business logic flows.

---

## 1. Complete Folder Structure

The backend application is built using Laravel 11.x, which has been modified to support a decoupled API layer, a Vue.js 3 Single Page Application (SPA), a multi-vendor admin/seller portal, and an automated Point of Sale (POS) accounting ledger.

### Comprehensive Directory Tree
```
Web/
├── app/                              # Core Application Logic
│   ├── Console/                      # Scheduler & Console commands
│   │   └── Kernel.php                # Event scheduling configurations
│   ├── Enums/                        # System Enums (OrderStatus, PaymentStatus, Roles)
│   │   ├── OrderStatus.php           # pending, confirm, processing, pickup, on_the_way, delivered, cancelled
│   │   ├── PaymentMethod.php         # cash, online, wallet
│   │   ├── PaymentStatus.php         # pending, paid, fail, refund
│   │   └── Roles.php                 # root, admin, shop, customer, visitor, driver
│   ├── Events/                       # Event triggers (real-time chat, mail notifications)
│   │   ├── AdminProductRequestEvent.php
│   │   ├── AdminSupportTicketMessageEvent.php
│   │   ├── OrderMailEvent.php        # Triggers order placement email
│   │   ├── ProductApproveEvent.php
│   │   ├── SendMessageToShop.php     # Real-time message broadcast to vendor channel
│   │   ├── SendMessageToUser.php     # Real-time message broadcast to customer channel
│   │   ├── SendOTPMail.php
│   │   ├── SendTestMailEvent.php
│   │   ├── SupportTicketEvent.php
│   │   └── SupportTicketMessageEvent.php
│   ├── Exceptions/                   # Custom Exception Handler
│   ├── Exports/                      # Excel sheet export templates
│   ├── Http/                         # Request Handlers
│   │   ├── Controllers/              # Controller Classes
│   │   │   ├── API/                  # Customer REST Endpoints
│   │   │   │   ├── Auth/             # Login, Registration, Password OTP controllers
│   │   │   │   ├── Rider/            # Rider app endpoints (Orders, Status, Profile)
│   │   │   │   └── Seller/           # Vendor app endpoints (Dashboard, Products, Wallet)
│   │   │   ├── Admin/                # Central admin portal controllers
│   │   │   ├── Gateway/              # Payment integrations (Stripe, Paypal, Paytabs, Bkash)
│   │   │   ├── Seller/               # Custom seller chat logic
│   │   │   └── Shop/                 # Vendor dashboard & POS controllers
│   │   ├── Middleware/               # Filters/Interceptors
│   │   │   ├── Authenticate.php
│   │   │   ├── CheckHasRootUser.php  # Redirects to setup if superadmin is missing
│   │   │   ├── CheckPermission.php   # Route-level RBAC enforcement
│   │   │   ├── CheckShopOwner.php    # Restricts access to shop-owner routes
│   │   │   ├── CheckSubscription.php # Validates vendor's active subscription tier
│   │   │   ├── DemoModeMiddleware.php# Disables POST/PUT/DELETE in demo systems
│   │   │   └── LocalizationManage.php# Localizes API responses
│   │   ├── Requests/                 # Request parameter validation classes
│   │   └── Resources/                # API Resources (JSON serialization formatting)
│   ├── Listeners/                    # Event Listeners
│   │   ├── OrderMailListener.php
│   │   ├── SendOTPMailNotification.php
│   │   └── TestMailListener.php
│   ├── Mail/                         # Mailable layout classes (OrderMail, SendOTP, TestMail)
│   ├── Models/                       # Eloquent Entities (User, Shop, Product, Voucher, AccountMaster)
│   ├── Providers/                    # Service Providers
│   │   ├── AppServiceProvider.php    # Views sharing, paginator setup, order stats caching
│   │   ├── AuthServiceProvider.php   # Model to policy mappings
│   │   ├── BroadcastServiceProvider.php # WebSockets broadcasting configurations
│   │   ├── EventServiceProvider.php  # Maps events to listeners
│   │   ├── PermissionServiceProvider.php # Dynamic Blade permission checking
│   │   ├── RouteServiceProvider.php  # Routing initializations
│   │   └── SmsServiceProvider.php    # SMS configuration setup
│   ├── Repositories/                 # Database Query Isolation classes (80+ entities)
│   ├── Rules/                        # Custom validation rules
│   ├── Services/                     # Internal & Third-Party Service integrations
│   │   ├── Accounting/               # Voucher entries, double-entry ledgers
│   │   │   └── VoucherService.php    # Debit/Credit adjustments & sequence numbering
│   │   ├── SMSGatewayService.php     # SMS integrations (Twilio, Nexmo, Telesign)
│   │   ├── ShiprocketService.php     # Logistics API
│   │   └── NotificationServices.php  # FCM multicast notifications
│   └── helpers.php                   # Global PHP functions (distance, currency, setting)
├── bootstrap/                        # Boot strap files & Autoloader config
├── config/                           # System configuration configurations (ACL, Sanctum, etc.)
├── database/                         # Database Migration & Seeders
│   ├── migrations/                   # Database schema definitions (217 files)
│   └── seeders/                      # Seeders (Role, User, Country, PaymentGateway, Wallet)
├── public/                           # Web assets & Compiled JS/CSS chunks (Vite build)
├── resources/                        # Blade templates & Vue.js source files
│   ├── js/                           # Vue.js 3 SPA files (Pages, Stores, Router)
│   └── views/                        # Blade views (Admin, Shop/Seller, Mail, PDF invoices)
└── routes/                           # Modular Route Definitions
    ├── admin.php                     # Central admin routes
    ├── api.php                       # Customer API endpoints
    ├── channels.php                  # Pusher broadcasting channels
    ├── console.php                   # Command-line router
    ├── rider.php                     # Rider API endpoints
    ├── seller.php                    # Seller API endpoints
    ├── shop.php                      # Vendor POS & Web dashboard routes
    └── web.php                       # Web wildcard routes (fallback Vue app loader)
```

---

## 2. API Architecture

The application implements a decoupled, stateless API architecture designed to handle requests from the customer Vue.js SPA storefront, the customer/vendor Flutter mobile applications, and the delivery riders' app.

```
                    ┌────────────────────────┐
                    │  Customer/Rider App    │
                    │  Vue.js SPA / Flutter  │
                    └───────────┬────────────┘
                                │ Bearer Sanctum Token
                                ▼
                    ┌────────────────────────┐
                    │  Route Middleware      │
                    │  auth:sanctum          │
                    └───────────┬────────────┘
                                │
                    ┌───────────┴────────────┐
                    │  RBAC Check            │
                    │  role:customer/driver  │
                    └───────────┬────────────┘
                                │
          ┌─────────────────────┼─────────────────────┐
          ▼                     ▼                     ▼
┌───────────────────┐ ┌───────────────────┐ ┌───────────────────┐
│  Customer Routes  │ │   Rider Routes    │ │   Seller Routes   │
│ (routes/api.php)  │ │(routes/rider.php) │ │(routes/seller.php)│
└───────────────────┘ └───────────────────┘ └───────────────────┘
```

### Key Elements of the API Layer
1. **Stateless Authorization**: Handled via **Laravel Sanctum**. Upon login, the app issues a token (`api_token`) using `UserRepository::getAccessToken($user)`, which the client includes in the `Authorization: Bearer <token>` header of subsequent requests.
2. **Modular Routes Configuration**:
   - `routes/api.php`: Customer storefront APIs (Cart, Checkout, Tickets, Address Management).
   - `routes/rider.php`: Delivery logistics, location coordinate reports, mapping coordinates, and status updates.
   - `routes/seller.php`: Shop setup, wallet payouts, banner management, products, and chat logs.
3. **Route Versioning**: The platform supports route versioning, seen in endpoints like `Route::prefix('/v1')->group(...)` for checkout.
4. **Broadcast Events Channel**: Channels (`routes/channels.php`) authorize and authenticate Pusher WebSocket sockets, establishing real-time chats between:
   - Customers and Shops: Channel `chat_user_{userId}` broadcasts messages instantly.
   - Support Agents: Channel `ticket_message_{ticketId}` supports real-time customer tickets.
5. **Fallback Wildcard Routing**: For web traffic, `routes/web.php` maps all routes except `/admin/*` and `/shop/*` to the fallback view `app.blade.php`, which boots the client-side Vue Router.

---

## 3. Business Logic

The backend follows the **Controller-Service-Repository** pattern. This separates database queries, service integrations, and controller request validations into independent, testable layers.

```
┌─────────────────┐      ┌──────────────────┐      ┌────────────────────┐      ┌───────────────┐
│ Http Controller │ ───> │  Service Class   │ ───> │ Repository Pattern │ ───> │ Database ORM  │
│ (Http/Requests) │      │  (app/Services)  │      │ (app/Repositories) │      │ (app/Models)  │
└─────────────────┘      └──────────────────┘      └────────────────────┘      └───────────────┘
```

- **Controller Layer**: Validates parameters using custom `FormRequest` rules. Handles redirect responses, JSON responses, and HTTP response codes.
- **Service Layer**: Manages business algorithms and runs third-party API integration scripts. E.g., `SMSGatewayService` hooks into Twilio, Nexmo, and Telesign; `ShiprocketService` runs logistics, shipping charges, and returns tracking numbers.
- **Repository Layer**: Encapsulates raw database queries. Extends a base class, isolating Eloquent model queries (like joins, updates, and aggregations) from the rest of the application.
- **Eloquent Models**: Map database tables to active records, define relationships (`hasMany`, `belongsTo`, `belongsToMany`), and format model attributes.

---

## 4. Authentication

The backend handles authentication differently depending on the user profile:

### Customer, Seller, and Rider REST Authentication
- Managed via `App\Http\Controllers\API\Auth\AuthController` and `App\Http\Controllers\API\Rider\LoginController`.
- **OTP Verification Flow**:
  - Requesting an OTP writes a record into the `verify_otps` table and triggers the `SendOTPMail` event, sending a 4-digit code.
  - The client submits the code to `verifyOtp()`, validating it against the database.
  - Once verified, the user is authenticated and is issued a Sanctum token.

### Administrative Web Authentication
- Traditional session-based authentication.
- Accessing the dashboard requires completing the `CheckHasRootUser` middleware. If no Superadmin exists, it redirects to the `/create-root` view to initialize the primary administrator account.

---

## 5. Roles & Permissions

System authorization is built on top of **Spatie Laravel-Permission**, configuring user permissions dynamically.

```
                                 ┌──────────────┐
                                 │ Spatie Role  │
                                 └──────┬───────┘
                                        │
                         ┌──────────────┴──────────────┐
                         ▼                             ▼
                 ┌──────────────┐              ┌──────────────┐
                 │ Role Perms   │              │ User Perms   │
                 └──────┬───────┘              └──────┬───────┘
                        │                             │
                        └──────────────┬──────────────┘
                                       │ Merge & De-duplicate
                                       ▼
                               ┌──────────────┐
                               │ User Black-  │
                               │  list Perms  │
                               └──────┬───────┘
                                      │ Remove (array_diff)
                                      ▼
                               ┌──────────────┐
                               │ Effective    │
                               │ Permissions  │
                               └──────────────┘
```

### Authorization Logic (`CheckPermission` Middleware)
1. **Bypass Checks**: Users holding the role `root` (Superadmin) or `shop` (with route prefixes matching `shop/*`) bypass permission validation automatically.
2. **Aggregating Permissions**:
   - Fetches the active role permissions (`rolePermissions`) from the cache.
   - Fetches custom user-specific overrides (`userPermissions`).
   - Fetches the user blacklist from `UserNonPermission` (individual permissions revoked from a specific user).
   - Combines and merges role + user permissions, de-duplicates them, and subtracts the user blacklist.
3. **Route Name Mapping**: Compares the request route name (`$request->route()->getName()`) against the user's effective permissions. Automatically maps `.store` to `.create` and `.update` to `.edit` permissions if necessary.

### View-Level Checks (`PermissionServiceProvider`)
Registers a custom Blade directive `@hasPermission('admin.category.create')` that uses this same permission evaluation logic to show/hide navigation elements in administrative layout templates.

---

## 6. Payment Flow

The payment architecture supports multi-vendor cart checkouts. The system consolidates orders from different vendors into a single transaction while keeping track of individual vendor payouts.

```
                       ┌─────────────────────────┐
                       │  Customer Cart Checkout │
                       │    (Multiple Shops)     │
                       └────────────┬────────────┘
                                    │
                                    ▼
                       ┌─────────────────────────┐
                       │ Create consolidated    │
                       │     Payment Model       │
                       └────────────┬────────────┘
                                    │
                                    ▼
                       ┌─────────────────────────┐
                       │ Group products by shop │
                       │    (shop_id)            │
                       └────────────┬────────────┘
                                    │
                         ┌──────────┴──────────┐
                         ▼                     ▼
                  ┌──────────────┐      ┌──────────────┐
                  │ Create Order │      │ Create Order │
                  │  (Shop A)    │      │  (Shop B)    │
                  └──────┬───────┘      └──────┬───────┘
                         │                     │
                         └──────────┬──────────┘
                                    │ Link Order IDs to Payment
                                    ▼
                       ┌─────────────────────────┐
                       │ Dynamic Gateway Route   │
                       │ (Resolve ProcessController)│
                       └────────────┬────────────┘
                                    ▼
                       ┌─────────────────────────┐
                       │ Redirect to Provider URL│
                       └────────────┬────────────┘
                                    │ Success Callback
                                    ▼
                       ┌─────────────────────────┐
                       │ Set Payment status paid │
                       │   & all orders paid     │
                       └─────────────────────────┘
```

### Step-by-Step Checkout Process
1. **Initialize Payment**: The system creates a master `Payment` record with amount `0`.
2. **Group Cart Items**: Groups products by vendor (`shop_id`) to support split-order logic.
3. **Compute Totals**: For each vendor group:
   - Calculates sub-totals, discounts, delivery fees, and tax.
   - Creates a distinct `Order` record linked to the parent `Payment` transaction.
4. **Dynamic Controller Resolution**:
   The `PaymentGatewayController@payment` resolves the payment controller dynamically using the gateway configuration:
   ```php
   $controller = 'App\\Http\Controllers\\Gateway\\' . $gateway->alias . '\\ProcessController';
   $url = $controller::process($gateway, $payment);
   ```
5. **Redirect & Callback Verification**:
   - Redirects the customer to the gateway process URL (Stripe, Paypal, Paytabs, Bkash).
   - Once payment is successful, the gateway triggers a callback that marks the master `Payment` as `is_paid => true` and sets all child orders' `payment_status` to `PAID` in a single operation.

---

## 7. Order Flow

Orders go through distinct states, from creation to final delivery and payout settlement.

```
       ┌───────────┐      ┌───────────┐      ┌────────────┐      ┌───────────┐      ┌───────────┐
  ───> │  Pending  │ ───> │ Confirmed │ ───> │ Processing │ ───> │  Pickup   │ ───> │ Delivered │
       └───────────┘      └───────────┘      └────────────┘      └───────────┘      └─────┬─────┘
                                                                                          │
                                                                                          ▼
                                                                                    ┌───────────┐
                                                                                    │ Payout &  │
                                                                                    │ Settlement│
                                                                                    └───────────┘
```

1. **Pending State**: The order is placed, inventory is decremented (`$product->decrement('quantity')`), and the buyer receives a confirmation email.
2. **Confirmation**: The shop vendor accepts the order, starting the fulfillment process. If the platform runs on a subscription model, this accepts decrements the vendor's active subscription credit counter.
3. **Processing**: The vendor packs the order and readies it for dispatch.
4. **Dispatch & Assignment (Rider Flow)**:
   - Active delivery riders within range receive push notifications via Firebase FCM.
   - Once a rider accepts the order, a record is added to the `drivers_orders` mapping table.
   - The order status shifts to **Pickup**, then **On The Way**.
5. **Delivered & Settlement**:
   - The rider updates the status to **Delivered** (and marks it Paid if it was Cash on Delivery).
   - The platform calculates commission and updates the order status.
   - The vendor's wallet is credited with the order amount. If commission rules apply, the system debits the commission fee from their wallet.
   - The delivery rider's wallet is credited with the order's delivery fee.
   - The customer receives a push notification confirming the delivery.

---

## 8. Vendor Flow

The system supports two business models: **Commission-based** and **Subscription-based**.

### Multi-Vendor Operations
- **Sellers/Shops**: Access routes under `/shop` and hold the `shop` role. They can configure business hours, specify off-days, configure local tax settings, define custom categories, manage inventory, and handle withdrawals.
- **Subscription-based model**:
  - Vendors purchase a subscription plan (`subscription_plans` table).
  - An active subscription record (`shop_subscriptions` table) specifies the package type, allowed product listing count, and remaining order processing limits (`remaining_sales`).
  - Creating new products or accepting customer orders verifies these limits.
- **Payout Management**:
  Sellers request earnings withdrawals (`withdraws` table). Admin approval debits the seller's wallet and processes the withdrawal transaction.

---

## 9. POS Flow

The Point of Sale (POS) system allows physical stores to run quick checkout operations.

```
┌────────────────┐      ┌────────────────┐      ┌────────────────┐      ┌────────────────┐
│ Scan Barcode / │ ───> │ Add Item to    │ ───> │ Apply Discount │ ───> │ Complete Order │
│ Search Product │      │ POS Cart       │      │  (if coupon)   │      │   & Print PDF  │
└────────────────┘      └────────────────┘      └────────────────┘      └────────────────┘
```

1. **Barcode Scanning**: Cashiers scan barcodes. The controller searches the `product_barcodes` and `inward_products` tables to fetch the corresponding item details, sizing, colors, and prices.
2. **Cart Management**: Cart updates are stored in the database (`pos_carts` and `pos_cart_products` tables), preventing cashiers from losing transaction states on page refresh.
3. **Draft Sales**: Cashiers can suspend an active checkout session by saving it as a draft (`is_draft => true`), clearing the view for the next customer.
4. **Direct Checkout**:
   - Standard order validations (like address, shipping, and email dispatching) are bypassed.
   - Creates an order in the database, setting its status directly to **Delivered** and **Paid**.
   - Decrements stock from the product model.
   - Attaches details (quantity, color, size, unit) to the order pivot tables.
   - Renders a clean PDF receipt invoice using `mPdf` (`PDF.invoice` view) and streams it directly to physical thermal receipt printers.

---

## 10. Coupon Flow

Coupons can be configured in two ways:
- **Shop Coupons**: Created by individual vendors and applicable only to products in their storefront.
- **Admin Coupons**: Created by the platform administrator, applicable across multiple shops via the `admin_coupons` mapping table.

### Coupon Application & Validation
1. **Verification**: When a coupon code is applied during checkout, the system:
   - Checks validity dates (`checkValidity`).
   - Verifies the user's limit: counts how many times the user has already applied this coupon (`limit_for_user`).
   - Verifies the order meets the minimum purchase amount (`min_amount`).
2. **Calculation**:
   - **Percentage**: Computes the discount percentage (`$totalAmount * $coupon->discount / 100`) and caps it at the maximum allowed discount limit (`max_discount_amount`).
   - **Fixed**: Applies the fixed discount amount directly.

---

## 11. Wallet System

The wallet system acts as a financial ledger for both shop vendors and delivery riders.

- **Models**: `Wallet`, `Transaction`, `Withdraw`.
- **Ledger Operations**:
  - Payments are processed via `WalletRepository::updateByRequest($wallet, $amount, $type)` where `$type` is `credit` or `debit`.
  - Transaction logs record a detailed description of the operation (e.g., `admin commission added`, `order credit`), indicating whether the transaction was an automated platform ledger adjustment.
- **Payout Approvals**:
  - Vendors submit withdrawal requests. This adds a record to the `withdraws` table with a `pending` status.
  - The administrator reviews the withdrawal request. Admin approval triggers the bank transfer, updates the withdrawal status to `approved`, and debits the seller's wallet balance.

---

## 12. Inventory System

Stock tracking handles both standard online catalog products and bulk inward shipments from suppliers.

- **Product Inventory**: The `products` table tracks standard catalog details (`price`, `discount_price`, `quantity`). Checkouts from the customer app or POS automatically decrement the available quantity.
- **Inward Stocking Flow**:
  - For large shipments, shop owners register an `InwardInvoice` (detailing the vendor source, invoice date, agent, transport, and tax amounts).
  - Individual items in the shipment are saved as `InwardProduct` records, specifying variant parameters (colors, sizes, quantities, purchase rate, HSN codes, and VAT tax categories).
  - This updates the product database, ensuring accurate inventory levels.
- **Voucher Ledger Integration**:
  Buying inventory records a purchase entry in the `product_purchases` table. This triggers double-entry ledger postings via `VoucherService` to log the financial transaction (debiting inventory accounts and crediting vendor payables).

---

## 13. Commission Model

When the platform runs on a commission model, the backend automatically calculates and deducts platform fees on every checkout:

- **Calculation**:
  On order delivery, the system calculates the commission charge based on the settings:
  - **Percentage**: Deducts a percentage of the total order value: `$order->total_amount * $generaleSetting->commission / 100`.
  - **Fixed**: Deducts a flat fee per order.
- **Ledger Settlement**:
  - The vendor's wallet is credited with the order's `total_amount`.
  - The system then creates a `debit` transaction on the vendor's wallet for the calculated `admin_commission` fee, transferring the commission to the platform administrator's account.

---

## 14. Notifications

Notifications are sent via push alerts and in-app message logs to keep users updated.

- **Firebase Cloud Messaging (FCM)**:
  Push notifications are sent using the `kreait/laravel-firebase` SDK wrapper. The class `NotificationServices` loads a service account configuration file (`storage/app/public/firebase_credentials.json`) and triggers multicast push notifications to the user's registered devices:
  ```php
  $messaging->sendMulticast($message, $deviceTokens);
  ```
- **In-App Message Logs**:
  Every system notification creates a record in the `notifications` table (detailing the title, body content, user relationship, read/unread state, and deep-link URLs). This allows users to view their notification history in their inbox.
- **Foreground Broadcasts**:
  For real-time chats, the application broadcasts WebSocket events using Pusher. This updates the customer and vendor interfaces instantly without needing page refreshes.
