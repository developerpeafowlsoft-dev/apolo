# System Customization & Re-engineering Report: Ready eCommerce (Apolo)

A comprehensive analysis of the system modifications, ERP integrations, double-entry financial modules, and custom inventory structures overlayed on the original ready-made Codecanyon script.

---

## 1. High-Level Summary of Customizations

The codebase was originally purchased as a multi-vendor eCommerce script. To adapt the software for enterprise use, developers added a custom **ERP Accounting & Inward Inventory System**. 

### ⚙️ Core Re-Engineered Domains
1. **Double-Entry Financial Accounting**: Introduced a ledger system (Vouchers, Accounts, Account Groups, and Balance Sheets) that records transaction logs when inventory is purchased or orders are processed.
2. **Bulk Inward Inventory (Garment Billing)**: Integrated an inward inventory processing system (supporting Design Masters, Item Masters, Counter Masters, colors, sizing variants, and HSN tax code categories) that auto-generates thermal barcode tags.
3. **Advanced POS (Point of Sale)**: Cashier counters are assigned to salesmen. Sales deduct stock and log transactions in cash books.
4. **Spatie ACL Overrides**: Added user-specific overrides and blacklists, allowing administrators to restrict permissions for specific staff members.

---

## 2. Inventory Inward & Purchase Customization Flow

```
                      ┌─────────────────────────┐
                      │  Bulk Supplier Arrival  │
                      │   (Inward Invoice)      │
                      └────────────┬────────────┘
                                   │
                                   ▼
                      ┌─────────────────────────┐
                      │   Compile Item Variants │
                      │  (Item, Sizing, Colors) │
                      └────────────┬────────────┘
                                   │
                                   ▼
                      ┌─────────────────────────┐
                      │ Generate Item Barcodes  │
                      │  (DNS1D Tag Printing)   │
                      └────────────┬────────────┘
                                   │
                                   ▼
                      ┌─────────────────────────┐
                      │ Finalize Purchase Bill  │
                      │ (PurchaseController)    │
                      └────────────┬────────────┘
                                   │
                                   ▼
                      ┌─────────────────────────┐
                      │   Invoke VoucherService │
                      │  (DB Transactions Lock) │
                      └────────────┬────────────┘
                                   │
                         ┌─────────┴─────────┐
                         ▼                   ▼
                  ┌──────────────┐    ┌──────────────┐
                  │ Credit Party │    │ Debit Stock  │
                  │ Account (Cr) │    │ Account (Dr) │
                  └──────────────┘    └──────────────┘
```

---

## 3. Detailed Customization Catalog

### 3.1 ERP & Accounting Ledger Service
- **File Name**: `Web/app/Services/Accounting/VoucherService.php`
- **Purpose**: Manages ledger sequences, balanced entry verification (Debit == Credit), and audit trail logs.
- **Why it was Customized**: Added to turn a simple checkout system into a double-entry accounting platform.
- **ERP Functionality**: Handles dynamic balance entries, sequence locks, and voucher status updates (`posted` vs. `reversed`).
- **Customer-Specific Functionality**: None. Runs as a background bookkeeping process.
- **Database Changes**: Integrates with `vouchers`, `voucher_entries`, `voucher_sequences`, `voucher_audit_logs`, and `account_balances`.
- **API Changes**: Bypasses external REST APIs; interacts directly with internal database query builders.
- **Dependencies**: `DB` transaction facades, `Auth` facades, and accounting models.
- **Risk of Modification**: **Critical**. Any syntax errors or transaction locking issues will disrupt bulk product purchases and billing.
- **Related Modules**: Purchases, POS Cash Registers, Branch settlements.

---

### 3.2 Supplier Bulk Purchases Controller
- **File Name**: `Web/app/Http/Controllers/Shop/PurchaseController.php`
- **Purpose**: Links physical inward inventory invoices to financial vouchers.
- **Why it was Customized**: Created to process bulk inventory receipts, calculate CGST/SGST/IGST tax values, and log accounts payable.
- **ERP Functionality**: Automatically records tax items and calculates taxable amounts and round-off offsets.
- **Customer-Specific Functionality**: Updates product stock quantities instantly, making them available in the online catalog.
- **Database Changes**: Writes to the `product_purchases` table, saving taxable values and assigning a `voucher_id`.
- **API Changes**: Exposes backend endpoints `/shop/purchase-product/store`.
- **Dependencies**: `VoucherService`, `ProductPurchaseRequest`, `InwardInvoiceRepository`.
- **Risk of Modification**: **High**. Errors here can lead to inventory stock mismatches and unbalanced accounting journals.
- **Related Modules**: Inward Invoices, Inventory, Accounting.

---

### 3.3 Garment Inward Invoices Compiler
- **File Name**: `Web/app/Http/Controllers/Shop/InwardProductController.php`
- **Purpose**: Manages bulk garment shipments, creates product variants, and handles tag printing.
- **Why it was Customized**: Replaces standard product uploads with bulk supplier invoicing.
- **ERP Functionality**: Groups item inputs by Design No, HSN codes, colors, sizes, and VAT categories.
- **Customer-Specific Functionality**: Auto-generates thermal barcode tags dynamically using the `DNS1D` barcode generator.
- **Database Changes**: Integrates with the `inward_invoices`, `inward_products`, and `product_barcodes` tables.
- **API Changes**: Bypasses customer APIs; exposes admin/shop routes.
- **Dependencies**: `DNS1D` writer library, `InwardInvoiceRepository`, `DesignMasterRepository`.
- **Risk of Modification**: **High**. Modifying the barcode sequence or design mappings will break tag generation and label printing.
- **Related Modules**: Product Inventory, Design Masters, POS barcode scanner.

---

### 3.4 Cashier checkout counter controller (POS)
- **File Name**: `Web/app/Http/Controllers/Shop/POSController.php`
- **Purpose**: Handles POS cashier billing, draft sales, and customer registrations.
- **Why it was Customized**: Built to support physical counter salesman sales directly from the admin panel.
- **ERP Functionality**: Bypasses standard shopping cart flows, automatically marking checkouts as Paid/Delivered and decrementing product quantities.
- **Customer-Specific Functionality**: Renders and streams print-ready thermal PDF invoices using `mPdf`.
- **Database Changes**: Updates `orders` and `order_products` tables.
- **API Changes**: Exposes endpoints `/store-order`, `/add-to-cart`, `/apply-coupon`, and `/customer-store`.
- **Dependencies**: `mPdf` font resources, `EndroidQrCode` library, `PosCartRepository`.
- **Risk of Modification**: **High**. Changes here can affect local cash collections and physical store checkouts.
- **Related Modules**: Orders, Coupons, Inventory, Customer Management.

---

### 3.5 Spatie User Permission Override Middleware
- **File Name**: `Web/app/Http/Middleware/CheckPermission.php`
- **Purpose**: Merges Spatie roles with custom user blacklists.
- **Why it was Customized**: Custom permission blacklists were added because standard Spatie roles do not support stripping permissions from individual users.
- **ERP Functionality**: None.
- **Customer-Specific Functionality**: Allows managers to restrict specific features for individual cashier or employee accounts.
- **Database Changes**: Queries `user_non_permissions` to apply the blacklist.
- **API Changes**: None.
- **Dependencies**: Spatie permissions config cache.
- **Risk of Modification**: **High**. Security configurations are handled here; errors could result in unauthorized administrative access.
- **Related Modules**: Employee Management, User Settings.

---

## 4. Custom Database Migrations Summary

| Migration File Name | Target DB Table | Purpose / ERP Role |
| :--- | :--- | :--- |
| `create_accounts_table.php` | `accounts` | Chart of Accounts ledger list. |
| `create_account_balances_table.php` | `account_balances` | Tracks opening/closing balances per financial year. |
| `create_vouchers_table.php` | `vouchers` | Records journal voucher headers. |
| `create_voucher_entries_table.php` | `voucher_entries` | Stores individual credit/debit items. |
| `create_inward_invoices_table.php` | `inward_invoices` | Logs supplier challan numbers and transport details. |
| `add_voucher_id_to_product_purchases.php`| `product_purchases` | Links bulk purchases to accounting records. |
| `add_taxable_cgst_sgst_to_purchases.php`| `product_purchases` | Stores CGST, SGST, and IGST tax splits. |
