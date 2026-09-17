# Apolo — Legacy Data Migration Runbook

> **⚠️ Partly superseded — 15 Sep 2026.** Written before the full dataset arrived. The folder layout (`2016`, `2021`), the filenames, and the "Step 9 never ran" finding are all out of date. The engine flow, the 15-step model, the CLI usage and the 30-second timeout warning still hold. See **APOLO_MIGRATION_SPEC.md** for current state.

**Scope:** `Web/` folder only (Laravel 11 backend + admin UI). Mobile/pos-device-bridge not covered.
**Written:** 2026-09-14
**Companion doc:** `data_migration_analysis_report.md` (architecture/theory). This file is the *operational* one — what state we are actually in, what is blocking, and the exact order of commands to start.

---

## 1. TL;DR — where things actually stand

The migration engine is **fully built and working**. The problem is not the code — it is that **only part of the pipeline has ever been run, and the browser is the wrong place to run the rest of it.**

| | Status |
|---|---|
| Steps 1–8 (masters + accounts) | ✅ Migrated |
| Step 9 (opening balances) | ❌ **Never run** — `account_balances` is empty |
| Steps 10–11 (catalog) | ✅ Migrated |
| Steps 12–13 (inward + barcodes) | ⚠️ **2016 only** (opening stock). 2021 never run |
| Steps 14–15 (purchases, POS sales, all accounting) | ❌ **Never run** — `vouchers`, `voucher_entries`, `product_purchases`, `order_products` are all 0 |
| Source data for 2017–2020, 2022–2026 | ❌ **Not exported from legacy ERP** — folders exist but are empty |

**Root blocker:** MAMP's web PHP is capped at `max_execution_time = 30`, and no step in the engine calls `set_time_limit()`. Steps 12–15 process 4k–35k rows and take minutes. Clicking "Run" in the admin dashboard **will 500 partway through and leave half-written data.** Run these from the CLI instead — CLI PHP has `max_execution_time = 0`.

---

## 2. The system that was built — code map

Everything hangs off one service class. There are two front doors into it (web UI and CLI), both calling the same `runStep()`.

```
                    ┌──────────────────────────────────────────┐
  Browser  ────────►│  routes/admin.php  (prefix /admin)        │
  /admin/           │    → Admin\DataMigrationController        │  ← 30s timeout. Avoid for steps 12-15.
  data-migration    │       index / runStep / clearStep /       │
                    │       uploadFile / checkPageHealth /      │
                    │       reconciliation / correctAccounts /  │
                    │       blankAccounts                       │
                    └───────────────┬──────────────────────────┘
                                    │
  Terminal ─────────────────────────┤   ← no timeout. USE THIS.
  php artisan                       │
  migrate:legacy-backup             │
    (Console\Commands\              │
     MigrateLegacyBackupCommand)    │
                                    ▼
                    ┌──────────────────────────────────────────┐
                    │  Services\Migration\                     │
                    │  LegacyDataMigrationService  (3,918 ln)  │
                    ├──────────────────────────────────────────┤
                    │  getStepsDefinition()  15 steps          │
                    │  resolveFilePath()     filename aliases  │
                    │  setSelectedYear()     year scoping      │
                    │  streamXlsxRows()      ZipArchive +      │
                    │                        XMLReader (SAX)   │
                    │  getFastRowCount()     64KB chunk count  │
                    │  migrateStep1..15()    per-step handlers │
                    │  clearStepData()       per-step wipe     │
                    │  autoCorrectAccounts() ledger calibrate  │
                    │  getBarcodeReconciliation()              │
                    └───────────────┬──────────────────────────┘
                                    ▼
                     MySQL  apolo_db_new   (shop_id = 14)
```

### Key files

| File | Role |
|---|---|
| `app/Services/Migration/LegacyDataMigrationService.php` | The whole engine. All 15 step handlers, XLSX streaming, transforms, reconciliation. |
| `app/Console/Commands/MigrateLegacyBackupCommand.php` | CLI entry: `migrate:legacy-backup` |
| `app/Http/Controllers/Admin/DataMigrationController.php` | HTTP entry, one action per dashboard button |
| `resources/views/admin/data-migration/index.blade.php` | Dashboard: year switcher, per-step Run/Clear, stage dropdowns, live terminal log |
| `routes/admin.php:597-606` | Route definitions |
| `backup_excel/` | Source data root |

### Why XMLReader instead of PhpSpreadsheet
`Design Master.xlsx` is 30,541 rows; `2021/Barcode Search.xlsx` is 35,460. PhpSpreadsheet builds the whole DOM in RAM and dies. The engine opens the `.xlsx` as a zip, streams `xl/sharedStrings.xml` into an index, then streams `xl/worksheets/sheet1.xml` row-by-row through a callback — one row in memory at a time.

---

## 3. The data — what is actually in `backup_excel/`

Verified row counts (header rows included; the engine skips 2–3 header rows per file):

### Root masters — all present ✅
| File | Rows | Used by |
|---|---:|---|
| `Master/Financial Year.xlsx` | 14 | Step 1 |
| `Master/Department Master.xlsx` | 44 | Step 2 |
| `Master/Brand.xlsx` | 872 | Step 3 |
| `Master/Color.xlsx` | 2,576 | Step 4 |
| `Master/Size.xlsx` | 153 | Step 5 |
| `Master/Material List.xlsx` | 6 | Step 6 |
| `Master/HSN Code_data.xlsx` | 1,414 | Step 7 (preferred) |
| `Master/HSN Code.xlsx` | 444 | Step 7 (fallback only — has zeroed rate columns) |
| `Account/Account.xlsx` | 457 | Step 8 |
| `Account/Opening Balance.xlsx` | 68 | Step 9 |
| `Catelog/Item Master.xlsx` | 4,134 | Step 10 |
| `Catelog/Design Master.xlsx` | 30,541 | Step 11 |
| `Account/DayBook.xlsx` | 138 | ⚠️ **No step consumes this.** See §9. |
| `Master/Color_original_backup.xlsx` | 2,730 | Not used — backup copy |

### Year folders — only 2 of 11 have data ⚠️
| Year | Barcode | Inward | Purchase | Sale | Mode |
|---|---|---|---|---|---|
| **2016** | ✅ 19,193 | — | — | — | **Opening Stock** (inward is derived from the barcode sheet) |
| 2017–2020 | — | — | — | — | **EMPTY FOLDER** |
| **2021** | ✅ 35,460 | ✅ 4,102 | ✅ 537 | ✅ 26,224 | Standard |
| 2022–2026 | — | — | — | — | **EMPTY FOLDER** |

### Filename aliasing
The report and the code use different filenames than what is on disk. `resolveFilePath()` handles this — you do **not** need to rename anything:

| Canonical name in step definition | What is actually on disk |
|---|---|
| `Voucher Detail Wise Inward.xlsx` | `Voucher Wise inward.xlsx` ✅ aliased |
| `Voucher Detail Wise purchase.xlsx` | `Voucher Wise purchase.xlsx` ✅ aliased |
| `Voucher Detail SALES.xlsx` | `Voucher Wise sale.xlsx` ✅ aliased |
| `Barcode Search peafowlweb.xlsx` | `Barcode Search.xlsx` ✅ aliased |

Year-scoped files (steps 12–15) are looked up **strictly inside the selected year folder** — they never leak across years. Masters (steps 1–11) are looked up in `Master/`, `Account/`, `Catelog/`, then the `backup_excel/` root.

---

## 4. The 15 steps and their dependency order

Order matters — each stage's foreign keys point at the previous stage's rows.

```
STAGE 1  Foundation Masters      Steps 1-7    ← run ONCE, year-independent
STAGE 2  Accounts & Balances     Steps 8-9    ← run ONCE, year-independent
STAGE 3  Catalog Hierarchy       Steps 10-11  ← run ONCE, year-independent
STAGE 4  Inward & Barcodes       Steps 12-13  ← run PER YEAR
STAGE 5  Purchases & POS Sales   Steps 14-15  ← run PER YEAR
FINAL    Auto Account Correction              ← run LAST, after all years
```

| # | Step | Source | Target table(s) |
|---:|---|---|---|
| 1 | Financial Years | `Financial Year.xlsx` | `financial_years` |
| 2 | Department Master | `Department Master.xlsx` | `categories`, `shop_categories` |
| 3 | Brand Master | `Brand.xlsx` | `brands` |
| 4 | Color Master | `Color.xlsx` | `colors` |
| 5 | Size Master | `Size.xlsx` | `sizes` |
| 6 | Material List | `Material List.xlsx` | `materials` |
| 7 | HSN Code Master | `HSN Code_data.xlsx` | `hsn_masters`, `hsn_sub_masters` |
| 8 | Account Master | `Account.xlsx` | `account_masters` |
| 9 | Opening Balances | `Opening Balance.xlsx` | `account_balances` |
| 10 | Item Master | `Item Master.xlsx` | `item_masters`, `products` |
| 11 | Design Master | `Design Master.xlsx` | `design_masters` |
| 12 | Inward Challans | `Voucher Wise inward.xlsx` *(or Barcode sheet in opening-stock years)* | `inward_invoices`, `inward_products` |
| 13 | Barcode Generation | `Barcode Search.xlsx` | `product_barcodes` |
| 14 | Purchase Bills | `Voucher Wise purchase.xlsx` | `product_purchases`, `vouchers`, `voucher_entries` |
| 15 | POS Sales | `Voucher Wise sale.xlsx` | `orders (pos_order=1)`, `order_products`, `vouchers`, `product_barcodes.is_sold` |

### Opening-stock mode (2016)
`isOpeningStockYear()` returns true when a year folder has a Barcode sheet but **no** Inward sheet. In that mode Step 12 runs `migrateStep12OpeningStockFromBarcode()` and creates inward invoices, inward products **and barcodes** all in one pass. Step 13 then detects barcodes already exist and no-ops. **This is why 2016 only needs Step 12.**

---

## 5. Current DB state vs. target (the gap)

Measured from `Web/10-09-2026-apolo_db_new.sql`:

| Table | Rows now | Expected after full migration | Gap |
|---|---:|---|---|
| `financial_years` | 11 | 11 | ✅ |
| `categories` / `shop_categories` | 41 / 82 | 41 | ✅ |
| `brands` | 869 | 869 | ✅ |
| `colors` | 2,726 | ~2,573 | ✅ |
| `sizes` | 148 | 149 | ✅ |
| `materials` | 3 | 3 | ✅ |
| `hsn_masters` / `hsn_sub_masters` | 442 / 1,412 | 441 / 1,411 | ✅ |
| `account_masters` | 463 | 454 | ✅ |
| **`account_balances`** | **0** | **~65** | ❌ **Step 9 never ran** |
| `item_masters` / `products` | 4,131 / 4,133 | 4,131 | ✅ |
| `design_masters` | 32,151 | 30,521 | ✅ |
| `inward_invoices` | 67 | 67 (2016) **+ 2021** | ⚠️ 2021 missing |
| `inward_products` | 19,189 | 19,189 (2016) **+ 2021** | ⚠️ 2021 missing |
| `product_barcodes` | 19,189 | 19,189 (2016) **+ ~35,457 (2021)** | ⚠️ 2021 missing |
| **`product_purchases`** | **0** | 2021 purchase bills | ❌ **Step 14 never ran** |
| **`orders` (pos_order=1)** | **0** *(5 rows exist but all `pos_order=0` e-commerce tests)* | 2021 POS invoices | ❌ **Step 15 never ran** |
| **`order_products`** | **0** | 2021 sale lines | ❌ |
| **`vouchers` / `voucher_entries`** | **0 / 0** | all purchase + sales vouchers | ❌ **No accounting posted at all** |

**Chart of accounts is seeded and fine** — `accounts` has 489 rows including `PUR_LOCAL`, `CGST_IN`, `SGST_IN`, `IGST_IN`, `EXP_ROF`, `SALES_POS`, `CASH_DRAWER`, `PETTY_CASH`. Steps 14/15 will resolve these by code, so no seeding work needed.

**Target shop is `id = 14` — "Apolo Family Showroom".** Shop `id = 1` ("My Shop") is demo data. Every command below must carry `--shop=14`.

---

## 6. Blockers found — why the migration is stuck

### B1. Web PHP times out at 30s — **this is the main one**
`/Applications/MAMP/bin/php/php8.2.4/conf/php.ini` → `max_execution_time = 30`. No step calls `set_time_limit()`. `QUEUE_CONNECTION=sync`, so nothing is backgrounded. The dashboard JS uses bare `fetch()` with no timeout handling, so a timeout surfaces as a generic 500 with no useful message.

Steps 12–15 on 2021 read 4k–35k rows and run for minutes. **They cannot complete in the browser.**

→ **Fix: run steps 12–15 from the CLI.** Alternatively raise `max_execution_time` to `0` in the MAMP php.ini and restart Apache, but CLI is cleaner and gives you a progress table.

### B2. Step 9 was skipped
`account_balances` is empty even though `Opening Balance.xlsx` (68 rows) is sitting there. Nothing in the code prevents it — it just was never clicked. Note `clearStepData(8)` truncates `account_balances`, so re-running Step 8 after Step 9 silently wipes the balances. **Always run 8 before 9, never 8 after.**

### B3. 9 of 11 year folders are empty
2017–2020 and 2022–2026 have no exports. Steps 12–15 for those years will throw `File ... not found`. These four exports per year must be pulled from the legacy Windows ERP before those years can be migrated. **This is a client/data-supply blocker, not a code blocker.**

### B4. `clearStepData()` is not shop-scoped
It calls `DB::table($table)->truncate()` with `FOREIGN_KEY_CHECKS=0` — it wipes **every shop**, not just shop 14. The `$shopId` argument is accepted and then never used. Treat every Clear button as "clear the whole database table".

### B5. Steps 14 and 15 buffer the entire file in RAM
Unlike steps 1–13, `migrateStep14()` and `migrateStep15()` accumulate all vouchers/orders into a PHP array before writing. For 2021's 26,224-row sales file this is fine under the `ini_set('memory_limit','1024M')` they set, but for a larger year it is the first thing that will break. Watch it.

### B6. Cosmetic: dashboard row count for 2016 is hardcoded
`getAllFilesStatus()` hardcodes `19189` as the displayed Excel row count for any barcode sheet in 2016. Don't read anything into that number for other years.

---

## 7. How to start the migration

### 7.0 Preflight (do this every session)

```bash
open -a MAMP
```

MAMP MySQL is currently **stopped** — start both Apache and MySQL from the MAMP control panel before anything else.

Then confirm the app and DB line up:

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:status | tail -20
```

Expect `Nothing to migrate` / all `Ran`. Confirm `.env` still points at `DB_DATABASE="apolo_db_new"` and `APP_URL="https://apolo"`.

**Take a backup before writing anything.** There is a dump at `Web/10-09-2026-apolo_db_new.sql` but it is from 10 Sep — make a fresh one:

```bash
/Applications/MAMP/Library/bin/mysqldump -u root -p --socket=/Applications/MAMP/tmp/mysql/mysql.sock apolo_db_new > ~/Desktop/apolo_db_new_pre_migration_$(date +%F).sql
```

### 7.1 Always dry-run first

Dry-run parses and counts without touching the DB. Do this for every step before the live run.

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:legacy-backup --step=9 --shop=14 --dry-run
```

### 7.2 The actual sequence to run now

Given the current state, here is the exact order. Everything already ✅ in §5 should **not** be re-run.

**① Step 9 — opening balances (missing, ~5 seconds)**

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:legacy-backup --step=9 --shop=14
```

Verify: `account_balances` should go from 0 → ~65.

**② Year 2021 — Stage 4, inward challans (4,102 rows)**

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:legacy-backup --step=12 --year=2021 --shop=14
```

**③ Year 2021 — Stage 4, barcodes (35,460 rows — the slowest step)**

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:legacy-backup --step=13 --year=2021 --shop=14
```

**④ Year 2021 — Stage 5, purchase bills + vouchers (537 rows)**

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:legacy-backup --step=14 --year=2021 --shop=14
```

**⑤ Year 2021 — Stage 5, POS sales + barcode lifecycle (26,224 rows)**

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:legacy-backup --step=15 --year=2021 --shop=14
```

**⑥ Final — calibrate the ledgers (run once, after ALL years are in)**

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:legacy-backup --correct-accounts --shop=14
```

⚠️ **CLI quirk:** passing `--correct-accounts` *without* an explicit `--step` runs **only** the correction and exits. If you write `--step=all --correct-accounts` it runs all 15 steps *and then* the correction. Both are intentional — just know which one you are invoking.

### 7.3 Adding a new year later

When 2017–2020 / 2022–2026 exports arrive, drop the four files into `backup_excel/<YEAR>/` (any of the aliased filenames work), then:

```bash
cd /Users/ankit/Data/peafowlweb/Client-2026/Apolo/Web && for S in 12 13 14 15; do /Applications/MAMP/bin/php/php8.2.4/bin/php artisan migrate:legacy-backup --step=$S --year=YYYY --shop=14; done
```

Then re-run `--correct-accounts` once at the end. You can also upload files through the dashboard's upload control (max 50 MB, `.xlsx`/`.xls`) — but note web `upload_max_filesize` is 32 MB and `post_max_size` is 8 MB, so for anything over ~8 MB copy the file in by hand instead.

### 7.4 What the dashboard is still good for

Open `https://apolo/admin/data-migration` (login as admin; route is behind `auth` + `checkPermission` + `chack_root_user`). Use it for:

- **Reading status** — per-step Excel row count vs DB record count, per-year file presence matrix
- **Barcode reconciliation panel** — generated vs sold vs in-stock
- **Dr/Cr audit banner** — shows whether the trial balance nets to zero
- **"Verify All Store Pages"** — hits each step's target admin page and checks it renders + CRUD works
- **Steps 1–11** — these are fast enough to survive the 30s cap

Do **not** use it to run steps 12–15 unless you first raise `max_execution_time`.

---

## 8. Verification after each stage

Run these against MySQL after each step. Connect with:

```bash
/Applications/MAMP/Library/bin/mysql -u root -p --socket=/Applications/MAMP/tmp/mysql/mysql.sock apolo_db_new
```

| After step | Check | Expect |
|---|---|---|
| 9 | `SELECT COUNT(*) FROM account_balances;` | ~65 |
| 12 | `SELECT COUNT(*) FROM inward_invoices;` | 67 + 2021 challans |
| 13 | `SELECT COUNT(*) FROM product_barcodes;` | 19,189 + ~35,457 |
| 14 | `SELECT COUNT(*) FROM product_purchases;` | > 0 |
| 14 | `SELECT COUNT(*) FROM vouchers WHERE voucher_type='purchase';` | > 0 |
| 15 | `SELECT COUNT(*) FROM orders WHERE pos_order=1;` | > 0 |
| 15 | `SELECT COUNT(*) FROM orders WHERE pos_order=0;` | **still 5** — e-commerce must not be polluted |
| 15 | `SELECT is_sold, COUNT(*) FROM product_barcodes GROUP BY is_sold;` | a real split, not all-zero |
| final | `SELECT ve.type, SUM(ve.amount) FROM voucher_entries ve JOIN vouchers v ON v.id=ve.voucher_id WHERE v.shop_id=14 GROUP BY ve.type;` | **Dr total == Cr total** |

The CLI prints a Barcode Reconciliation table automatically after steps 13/15 — `Total Generated = Total Sold + Total In-Stock` must hold.

Then spot-check the UI: `/shop/account-balance`, `/shop/inward-product`, `/shop/pos/sales`, `/shop/accounting/vouchers`.

---

## 9. Known gaps and open items

1. **`Account/DayBook.xlsx` (138 rows) has no migration step.** It is the legacy journal/daybook. If cash/bank/journal vouchers outside purchase and sales need to come across, a Step 16 has to be written. Confirm with the client whether it is in scope.
2. **Nine year folders are empty.** 2017–2020 and 2022–2026 need Inward / Purchase / Sale / Barcode exports from the legacy Windows ERP. Until then only 2016 and 2021 can be migrated.
3. **`clearStepData()` is not shop-scoped** (§B4) — worth fixing before anyone uses Clear on a database that has real shop-1 data.
4. **No `set_time_limit()` anywhere** (§B1) — adding `set_time_limit(0)` to `runStep()` would make the dashboard usable for the big steps.
5. **Steps 14/15 buffer whole files in RAM** (§B5) — will need chunking for bigger years.
6. **Re-run safety:** steps are written to be idempotent via natural keys (`shop_id` + `design_number`, `shop_id` + `barcode_number`, `inward_voucher_no`). A failed mid-run step can generally be re-run. If a step is genuinely half-written, use the matching Clear (mind §B4) and re-run it rather than running it twice.

---

## 10. Quick reference

```bash
# Dry-run a single step
artisan migrate:legacy-backup --step=N --year=YYYY --shop=14 --dry-run

# Live-run a single step
artisan migrate:legacy-backup --step=N --year=YYYY --shop=14

# Everything for a year, then calibrate
artisan migrate:legacy-backup --step=all --year=YYYY --shop=14 --correct-accounts

# Calibrate ledgers only
artisan migrate:legacy-backup --correct-accounts --shop=14
```

Prefix `artisan` with `/Applications/MAMP/bin/php/php8.2.4/bin/php` and run from `Apolo/Web`.

| Option | Default | Notes |
|---|---|---|
| `--step` | `all` | `1`–`15` or `all` |
| `--year` | none | year folder, or `all`. Only affects steps 12–15 |
| `--shop` | `14` | always 14 for Apolo Family Showroom |
| `--dry-run` | off | parse + count, no writes |
| `--correct-accounts` | off | alone = correction only; with explicit `--step` = run then correct |

Dashboard: `https://apolo/admin/data-migration`
