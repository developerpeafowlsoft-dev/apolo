# Indian Retail ERP Accounting & Analytics Engine Architecture Blueprint
## Comprehensive Full-Stack Specification for Multi-Channel Retail: Local Showroom POS, E-Commerce Web, & Flutter Mobile App
**Standard Benchmark**: Indian Retail ERP (G-Soft ERP, Tally Prime, Marg ERP, Busy Accounting)

---

## Executive Overview

This document specifies the complete, exhaustive architectural blueprint for an integrated Indian Retail ERP Accounting, Tax Compliance, Financial Reporting, Inventory Valuation, and Visual Analytics Engine. It bridges:
1. **Local Showroom POS** (High-speed cashier billing, local device bridge, offline-first sync).
2. **E-Commerce Web Store** (Prepaid gateway, COD, cart checkout, digital invoicing).
3. **Flutter Mobile Application** (Field sales, mobile POS, B2B customer portal).
4. **Head Office (HO) & Multi-Branch / Franchise Synchronization** (Central ledger consolidation).

---

## 1. Audit of Existing System Accounting & Master Modules

Below is the complete audit mapping of pre-existing models, controllers, and routes in the codebase that form the foundation of our ERP system:

### A. Shop Admin Accounting & Master Modules
| Module Name | Controller Path | Primary Model(s) | Route Endpoint | Purpose & Blueprint Enhancement |
| :--- | :--- | :--- | :--- | :--- |
| **Account Master** | `Shop\AccountMasterController` | `AccountMaster` | `/shop/account-master` | Party Debtors/Creditors with GSTIN, PAN, TAN, Bank Details, Credit Limit, Credit Days, Interest Rates. |
| **Account Balance** | `Shop\AccountBalanceController` | `AccountBalance` | `/shop/account-balance` | Stores financial year opening & closing debit/credit balances per shop/branch. |
| **Bank Master** | `Shop\BankMasterController` | `BankMaster` | `/shop/bank-master` | Stores company bank accounts, IFSC, SWIFT, branch, and UPI parameters for BRS. |
| **Counter Master** | `Shop\CounterMasterController` | `CounterMaster` | `/shop/counter-master` | Physical POS cashier billing counters, voucher prefixes, and shift locks. |
| **TDS Master** | `Shop\TDSMasterController` | `TDSMaster` | `/shop/tds-master` | Statutory TDS/TCS section rates (Sec 194Q, 206C) and deduction criteria. |
| **HSN Master** | `Shop\HSNMasterController` | `HsnMaster`, `HsnSubMaster` | `/shop/hsn-master` | HSN/SAC codes, tax slabs (CGST, SGST, IGST, Cess), and GSTR-1 Table 12 requirements. |
| **Item Master** | `Shop\ItemMasterController` | `ItemMaster` | `/shop/item-master` | Core product item master defining stock units, barcodes, colors, sizes, and pricing. |
| **Design Master** | `Shop\DesignMasterController` | `DesignMaster` | `/shop/design-master` | Design/variant definitions for apparel and showroom inventory. |
| **Inward Product** | `Shop\InwardProductController` | `InwardInvoice`, `InwardProduct` | `/shop/inward-product` | Inward vendor stock entry, barcode tagging, and purchase cost tracking. |
| **Purchase** | `Shop\PurchaseController` | `Purchase` | `/shop/purchase` | Vendor purchase invoices with ITC eligibility tagging and Supplier Ledger posting. |

### B. Super Admin Accounting & Master Modules
| Module Name | Controller Path | Primary Model(s) | Route Endpoint | Purpose & Blueprint Enhancement |
| :--- | :--- | :--- | :--- | :--- |
| **Account Type** | `Admin\AccountTypeController` | `AccountType` | `/admin/account-type` | Top-level classifications (Asset, Liability, Income, Expense). |
| **Account Group** | `Admin\AccountManagementController` | `AccountGroup` | `/admin/accounts-group` | Hierarchical group hierarchy (Sundry Debtors, Sundry Creditors, Duties & Taxes, Direct Expenses). |
| **Account** | `Admin\AccountController` | `Account` | `/admin/account` | System-wide Chart of Accounts ledger master linked to Account Groups. |
| **Voucher Type** | `Admin\VoucherTypeController` | `VoucherType` | `/admin/voucher-type` | Voucher types (Sales, Purchase, Payment, Receipt, Journal, Contra, Credit Note, Debit Note). |
| **Voucher Management**| `Shop\VoucherController` | `Voucher`, `VoucherEntry`, `VoucherSequence` | `/shop/voucher` | Core double-entry journal voucher creation, preview numbering, and audit logging. |
| **Financial Years** | Models & Settings | `FinancialYear` | Global Context | Manages 1st April to 31st March financial accounting periods and sequence resets. |

---

## 2. Exhaustive List of Accounting Features & Modules Included

The system includes **100% of Indian Retail ERP accounting capabilities** (G-Soft / Tally Prime Standard):

### 1. Chart of Accounts & Party Master
- Multi-Level Account Groups (Assets, Liabilities, Capital, Income, Expenses, Duties & Taxes, Sundry Debtors, Sundry Creditors).
- Party Master (`AccountMaster`) with GSTIN validation, PAN, TAN, Bank IFSC, Credit Limit, Credit Days, and Overdue Interest Rate.

### 2. Double-Entry Voucher Engine
- **Sales Voucher**: Showroom POS, E-Commerce Web, Mobile App.
- **Purchase Voucher**: Goods Inward, Inward Carriage/Freight, Tax ITC.
- **Receipt Voucher**: Cash/Bank/UPI/EDC Card Customer Collections.
- **Payment Voucher**: Vendor Payments, Expense Payments.
- **Contra Voucher**: Cash Deposit to Bank, Cash Withdrawal from Bank, Bank-to-Bank Transfer.
- **Journal Voucher**: Adjustment Entries, Depreciation, Opening Balances.
- **Credit Note Voucher**: POS Sales Returns, Price Adjustments, GST Tax Reversal.
- **Debit Note Voucher**: Purchase Returns, Vendor Discounts, GST Debit Note.

### 3. Complete Financial Statements
- **Day Book**: Real-time daily cash, bank, sales, purchase, and journal transaction log.
- **Cash Book & Bank Book**: Detailed cash movement and bank balance logs.
- **Party Ledger Account Statement**: Running debit/credit balance statement with PDF/Excel export.
- **Trial Balance**: 4-column (Opening, Debit Total, Credit Total, Closing) Trial Balance.
- **Profit & Loss Statement (P&L)**: Trading Account (Sales, Purchases, COGS, Gross Profit) and P&L Account (Indirect Expenses, Net Profit).
- **Balance Sheet**: Vertical & Horizontal Balance Sheet (Assets, Liabilities, Capital Account, Working Capital).

### 4. Indian GST Statutory Engine
- **GSTR-1 Outward Return**: B2B (Table 4), B2C Large (Table 5), B2C Small (Table 7), Credit/Debit Notes (Table 9B), HSN Summary (Table 12), Document Summary (Table 13), JSON Export for GST Portal offline tool.
- **GSTR-2A / GSTR-2B Auto-Reconciliation**: Matches Vendor GSTR-2B JSON with Purchase Books (`Matched`, `Mismatched`, `Missing in Books`, `Missing in Portal`).
- **GSTR-3B Auto-Tax Computation**: Tax payable calculation after adjusting Input Tax Credit (ITC set-off rules: IGST -> CGST -> SGST).
- **Reverse Charge Mechanism (RCM)**: RCM liability & ITC creation for unregistered purchases and Goods Transport Agency (GTA).
- **E-Invoicing (IRN)**: NIC Portal API for 64-character IRN generation and B2B Signed QR code printing.
- **E-Way Bill Generation**: Consignment movement > ₹50,000 with Transporter ID & Vehicle tracking.

### 5. Cash, Bank Management & Shift Audit
- **POS Shift Cash Drawer Reconciliation**: Cashier shift close reconciliation (`Opening Cash + Cash Sales - Cash Return` vs counted cash).
- **Cash Excess / Shortage Posting**: Automatic voucher posting for cashier cash shortages or excess.
- **Bank Reconciliation Statement (BRS)**: Imported bank CSV matching with clearing dates and gateway payouts.
- **Cheque & PDC Management**: Post-dated cheque tracking and cheque bounce journal posting.

### 6. Bill-by-Bill Outstanding & Collections
- **Bill-by-Bill Allocation**: Links receipts and payments to specific invoice numbers.
- **Debtors & Creditors Ageing Ratios**: **0-30**, **31-60**, **61-90**, **90+ Days** ageing buckets.
- **Overdue Interest Calculation**: Automated interest calculation for delayed party payments.
- **Automated Collection Reminders**: WhatsApp/SMS reminders with payment link.
- **Credit Limit & Overdue Days Enforcement**: Locks billing if customer exceeds credit limit or overdue days.

### 7. Inventory Valuation & Costing Engine
- **Valuation Methods**: FIFO (First-In, First-Out) & Weighted Average Costing using `InwardProduct` cost rates.
- **Cost of Goods Sold (COGS)**: Real-time COGS posting during invoice finalization.
- **Physical Stock Adjustment**: Stock audit variance vouchers for damaged/expired inventory.

### 8. Advanced Reports & Visual Analytics (G-Soft / Marg Standard)
- **FSN Fast/Dead Stock Tracing**: Calculates Inventory Turnover Ratios ($\text{ITR} = \frac{\text{COGS}}{\text{Avg Stock}}$) over 30, 60, 90, 180 days to flag **Fast-Moving (F)**, **Slow-Moving (S)**, and **Dead Stock (N)**.
- **Salesman Commission Engine**: Item-wise, brand-wise, or slab-wise commission payout vouchers.
- **Customer RFM Loyalty**: Recency, Frequency, & Monetary customer segmentation.
- **HO & Multi-Branch Matrix**: Consolidated Head Office P&L, stock valuation, and cash balance matrix across showrooms and channels.
- **Audit Trail & Event Log**: Tracks every voucher creation, edit, deletion, or backdated entry with user ID, IP, and timestamp.

---

## 3. Automated Double-Entry Journal Posting Matrix

#### Scenario 1: Showroom POS Card / UPI Invoice (Intra-State GST - Gujarat Store to Gujarat Customer)
*Bill Amount: ₹1,000.00 + 18% GST (9% CGST ₹90.00 + 9% SGST ₹90.00) = Total ₹1,180.00*

```
DEBIT  : Paytm EDC Card / PhonePe Clearing A/c    ₹1,180.00
CREDIT : POS Showroom Sales A/c                   ₹1,000.00
CREDIT : CGST Output A/c                          ₹   90.00
CREDIT : SGST Output A/c                          ₹   90.00
```

#### Scenario 2: E-Commerce Inter-State Sale (Gujarat Store to Maharashtra Customer)
*Bill Amount: ₹2,000.00 + 18% IGST ₹360.00 = Total ₹2,360.00*

```
DEBIT  : E-Commerce Payment Gateway Clearing A/c   ₹2,360.00
CREDIT : E-Commerce Web Sales A/c                  ₹2,000.00
CREDIT : IGST Output A/c                           ₹  360.00
```

#### Scenario 3: POS Sales Return / Credit Note with Inventory Reversal
*Return Value: ₹500.00 + 18% GST (₹45 CGST + ₹45 SGST) = Total ₹590.00*

```
DEBIT  : POS Sales Return A/c                     ₹  500.00
DEBIT  : CGST Output A/c (Tax Reversal)           ₹   45.00
DEBIT  : SGST Output A/c (Tax Reversal)           ₹   45.00
CREDIT : Counter Cash Drawer / Credit Note A/c    ₹  590.00
```

#### Scenario 4: Vendor Stock Purchase with Input Tax Credit (ITC)
*Invoice: ₹10,000.00 + 18% IGST ₹1,800.00 = Total ₹11,800.00*

```
DEBIT  : Inter-State Goods Purchase A/c           ₹10,000.00
DEBIT  : IGST Input (ITC) A/c                     ₹ 1,800.00
CREDIT : M/s Fabric Supplier Sundry Creditor A/c  ₹11,800.00
```
