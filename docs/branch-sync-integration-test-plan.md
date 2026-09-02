# Branch Synchronization & Offline Mode Integration Test Plan

This checklist outlines the procedures for executing live integration testing of the branch offline billing and cloud background synchronization systems.

---

## 1. Offline Billing Capabilities

- [ ] **Simulate Internet Outage**
  - Sever connectivity between the local store branch server and the central API server.
  - *Method*: Disable network interface, pull LAN cable, or temporarily map central domain (e.g., `cloud.readyecommerce.com`) to `127.0.0.1` in `/etc/hosts`.
  - *Verification*: Confirm that accessing external services (e.g. online gateways or OTP routes) returns `503 Service Unavailable` with a clear message indicating offline mode is active.

- [ ] **Offline POS Sales Execution**
  - Run a complete transaction through the local POS billing screen while offline.
  - *Verification*:
    - The transaction saves successfully to the local database.
    - Local inventory decrements locally.
    - A row is immediately created inside `sync_queue` in the local DB with a status of `pending`.
    - Double-entry voucher ledger balances are created correctly locally.

---

## 2. Collision-Free Document Numbering

- [ ] **Multi-Branch Bill Number Generation**
  - Perform multiple transactions in succession on two separate offline branch servers.
  - *Verification*:
    - Branch 1 produces bills prefixed with its code, e.g. `5063-S-00001`, `5063-S-00002`.
    - Branch 2 produces bills prefixed with its code, e.g. `9821-S-00001`, `9821-S-00002`.
    - There must be no bill number overlaps or sequence conflicts.

- [ ] **Voucher Sequence Range Isolation**
  - Confirm voucher ranges are correctly allocated according to branch registration boundaries.
  - *Verification*:
    - Branch 1 vouchers increment within range `500,000 - 599,999`.
    - Branch 2 vouchers increment within range `600,000 - 699,999`.
    - Voucher sequences never intersect or overwrite each other.

---

## 3. Re-connection & Automated Ingestion

- [ ] **Restore Network Connectivity**
  - Re-establish connectivity to the central cloud API (re-enable network interface or restore host resolution).
  - *Verification*:
    - Check endpoint `HEAD /api/central/ping` is reachable.
    - Run the Artisan health reporter `php artisan branch:health` on the branch server and verify that `Connectivity` switches to `Connected`.

- [ ] **Auto-Trigger Sync Processing**
  - Wait for the scheduled task (`PushToCentralSync` dispatched every minute) or manually execute:
    ```bash
    php artisan queue:work --once
    ```
  - *Verification*:
    - All local `pending` sync queue items change status to `synced`.
    - Corresponding rows are successfully inserted in central `orders`, `vouchers`, `voucher_entries`, and `product_purchases` tables.
    - Central voucher records match branch voucher numbers and pass double-entry balance check validations.
    - The branch's `last_synced_at` field updates centrally and locally.

---

## 4. Conflict Resolution & Inventory Safeguards

- [ ] **Deliberate Oversell Scenario**
  - While both branch servers are offline, perform a sale of 8 units of Product A on Branch 1, and 8 units of Product A on Branch 2.
  - Central stock is set to exactly 10 units.
  - *Reconnection Ingestion*: Restore connectivity and process sync.
  - *Verification*:
    - The first branch's sync decreases central stock from 10 to 2 units.
    - The second branch's sync (requires 8 units, only 2 left) caps central stock at 0.
    - Ingestion does not crash; instead, it completes successfully.
    - A row is written to `stock_reconciliation_flags` centrally, recording:
      - `reported_sale_qty` = 8
      - `previous_stock` = 2
      - `new_stock` = -6
      - `reason` = "Sale of 8 units pushed central stock negative (previous: 2)."
