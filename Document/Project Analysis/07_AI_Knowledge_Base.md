# Master AI Knowledge Base & Developer Playbook: Ready eCommerce (Apolo)

A permanent, comprehensive technical knowledge base and reference guide for future developers and AI agents. This document defines the business logic, directory layouts, database relationships, custom ERP workflows, coding standards, and constraints for the Ready eCommerce (Apolo) codebase.

---

## 1. Terminology & Domain Glossary

- **Niche / Service**: The vertical domain of the storefront (e.g. `ecommerce`, `food`, `grocery`, `pharmacy`).
- **Core Route**: The Flutter tab navigation coordinator (prefixed dynamically with the niche name).
- **POS counter**: A physical store cashier checkout point mapped to specific salesmen and cash books.
- **Inward Invoice**: A bulk inventory arrival invoice from a supplier listing garment variants, design numbers, and HSN tax codes.
- **Chart of Accounts**: The structured accounting directory (`accounts`, `account_groups`, `account_types`) used for the double-entry bookkeeping ledger.
- **Journal Voucher**: An accounting record (`vouchers`, `voucher_entries`) documenting debit/credit ledger changes.
- **Wallet Ledger**: An account record (`transactions`) tracking withdrawal approvals (`withdraws`) and order balances for vendors/drivers.
- **User Non-Permission**: A Spatie ACL override table (`user_non_permissions`) containing blacklisted permissions for individual employees.

---

## 2. Business Rules & Logic Workflows

### 2.1 The Multi-Vendor Checkout Split
1. **Consolidated Payment**: When checking out a cart with items from multiple shops, the system creates a master `Payment` record with the total combined amount.
2. **Order Splits**: The cart is split by `shop_id`. A separate `Order` record is generated for each shop, complete with its own tax, discounts, and delivery fees.
3. **Sequential Code Generation**: Each child order is assigned a unique, sequential order code based on the database index count.

### 2.2 Payment Gateway Redirection (Web vs. Mobile)
- **Web Storefront (Vue.js)**: 
  - Submitting `/place-order` returns an `order_payment_url` (e.g., Stripe, Paypal).
  - The client opens this URL in a centered browser popup window.
  - An interval tracker watches `win.location.pathname`. On redirecting to `/payment/success` or `/payment/cancel`, the popup closes, the parent window updates, and the user is redirected.
- **Mobile Storefront (Flutter)**:
  - Opens the `order_payment_url` in an in-app Webview.
  - Monitors URL navigation. It intercepts `/payment/success` or `/payment/cancel` redirects to close the Webview and update the app view.

### 2.3 POS Cashier Checkout & double-entry Ledgers
1. **Direct Validation Bypass**: Bypasses customer shipping validation, address validation, and email notification queues.
2. **Immediate Settlement**: Creates an `Order` with `DELIVERED` status and `PAID` payment status.
3. **Inventory Updates**: Decrements product quantities immediately across selected sizing/color pivot items.
4. **Voucher Postings**: Finalizing inventory purchases or cash sales calls the `VoucherService` to post double-entry bookkeeping items (Asset debits vs Liability/Equity credits) within database transactions.

### 2.4 Commissions & Subscriptions
- **Commission Model**: Admin commissions (percentage/fixed) are deducted from the vendor's wallet balance on order delivery. The seller's wallet is credited with the order total, then debited for the commission fee.
- **Subscription Model**: Vendors purchase listing packages (`shop_subscriptions`). Every confirmed order decrements their remaining quota (`remaining_sales`). When this reaches zero, checkout is blocked until they renew.

---

## 3. Database Relationships & Schema Map

```
  ┌──────────────┐          ┌──────────────┐          ┌──────────────┐
  │    Users     │ ───────> │  Customers   │ ───────> │  Addresses   │
  └──────┬───────┘          └──────┬───────┘          └──────┬───────┘
         │                         │                         │
         ▼                         ▼                         ▼
  ┌──────────────┐          ┌──────────────┐          ┌──────────────┐
  │   Wallets    │          │    Orders    │ <─────── │   Payments   │
  └──────┬───────┘          └──────┬───────┘          └──────────────┘
         │                         │
         ▼                         ▼
  ┌──────────────┐          ┌──────────────┐
  │ Transactions │          │ OrderDetails │
  └──────────────┘          └──────┬───────┘
                                   │
                                   ▼
                            ┌──────────────┐
                            │   Products   │
                            └──────────────┘
```

### ERP Accounting Schema Relationships
- **Account Ledger**:
  - `accounts` belongs to `account_groups`.
  - `account_groups` belongs to `account_types`.
  - `account_balances` maps `accounts` to `financial_years` and `shops`.
- **Journal Vouchers**:
  - `voucher_entries` belongs to `vouchers`.
  - `vouchers` belongs to `voucher_sequences` (for sequential numbering).
  - `vouchers` logs audit trails in `voucher_audit_logs`.
- **Inventory Purchases**:
  - `inward_products` belongs to `inward_invoices`.
  - `product_purchases` links `inward_invoices` to `vouchers` via `voucher_id`.

---

## 4. Key API Endpoint Map

All client endpoints are prefixed with `/api` and require a `Bearer <api_token>` header for authorized access.

| End Point URL | Request Method | Parameters | Returns | Trigger Module |
| :--- | :--- | :--- | :--- | :--- |
| `/master` | `GET` | None | JSON configurations | App Bootup Config |
| `/registration` | `POST` | `name`, `phone`, `email`, `password`, `country` | User Object + Bearer Token | SignUp Registration |
| `/login` | `POST` | `phone`, `password`, `device_key`, `device_type` | User Object + Bearer Token | Auth Login |
| `/favorite-add-or-remove`| `POST` | `product_id` | Success Confirmation | Wishlist Update |
| `/cart/checkout` | `POST` | `shop_ids` array, `coupon_code` | Subtotals, Taxes, Shipping Fees | Checkout Summary |
| `/shipping/calculate` | `POST` | `address_id`, `shop_ids` array, `payment_method` | Delivery details and charges | Checkout Calculations |
| `/place-order` | `POST` | `shop_ids`, `address_id`, `payment_method`, `coupon_code`, `note`, `shipping_details` | Order Details + `order_payment_url` | Checkout Submission |
| `/update-last-seen` | `POST` | None | Status Code | User Online Tracker |

---

## 5. Coding Conventions & Development Rules

### 5.1 Laravel Backend Conventions
1. **Repository Pattern**: Do not query models directly in controllers. Write queries in repository classes (`app/Repositories/`) and inject them.
2. **Balanced Accounting Transactions**: Modifying any financial transactions must use the `VoucherService` and run within DB transactions to prevent ledger discrepancies:
   ```php
   DB::transaction(function () use ($data) { ... });
   ```
3. **Localisation Headers**: Use localized translations for all API return messages: `__('Message')`.

### 5.2 Vue.js Frontend Conventions
1. **Pinia Stores**: Keep shared client states (authentication details, cart tallies, layout settings) in their respective Pinia stores, enabling local persistence: `persist: true`.
2. **Dynamic Layout Imports**: Page routes must specify their corresponding layout configuration inside the meta settings object:
   ```javascript
   meta: { layout: defaultLayout, title: "Title" }
   ```
3. **Unified Error Capture**: Catch validation errors (HTTP 422) and bind them to the template views using the reactive `errors` variable.

### 5.3 Flutter Mobile Conventions
1. **Dynamic Niche Prefixing**: Every route must prefix the niche namespace to support dynamic views:
   ```dart
   static String getHomeViewRouteName(String service) => '$service$homeView';
   ```
2. **State Management**: Use **Riverpod** with `StateNotifier` and `StateNotifierProvider` to manage screen states.
3. **Hive Database Boxes**: Persist settings, user profiles, and active cart data locally using their designated Hive boxes (`appSettings`, `laundrySeller_authBox`, etc.).

---

## 6. Constraints: Things AI/Developers Should NEVER Modify

> [!CAUTION]
> **DO NOT modify or refactor the following core logic modules without explicit approval. Doing so will break system security, double-entry financial balances, and payment processing:**

1. **Voucher Balanced Verification**: Do not bypass `ensureBalanced(array $entries)` inside `VoucherService.php`. The sum of Debits must equal the sum of Credits.
2. **CheckPermission Spatie Override**: Do not modify the permission merging logic in `CheckPermission.php`. Bypassing this middleware can expose administrative routes.
3. **Online Payment popup tracking**: Do not change the centered browser popup window execution or the URL tracking interval (`trackURLChanges`) in the Vue storefront. This will break online checkout processing.
4. **Device FCM key registrations**: Do not remove the `device_key` and `device_type` injections during mobile auth API calls. Doing so will stop push notification alerts in the mobile apps.
5. **Dynamic prefix routing table**: Do not refactor the dynamically resolved switch-case route builders in Flutter (`routes.dart`). This will break navigation flows across different niche views.
