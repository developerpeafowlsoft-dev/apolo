# Branch Server Deployment Guide

This guide describes how to stand up a new offline-capable physical store branch server using the unified codebase.

## Prerequisites
- PHP 8.2 or 8.3
- MySQL 8.0+
- Composer

## Installation Steps

1. **Clone & Setup Codebase**
   Clone the repository to the local branch server.

2. **Install Dependencies**
   Run composer to install PHP dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configure Environment File**
   Copy the branch template to `.env`:
   ```bash
   cp .env.branch.example .env
   ```
   Open `.env` and configure:
   - `APP_KEY` (generate with `php artisan key:generate`)
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (pointing to the store's local MySQL instance)
   - `BRANCH_MODE=branch`
   - `BRANCH_CODE=BRXYZ` (fill in the registered branch code, e.g. `BR001`)

4. **Register the Branch on Central Cloud DB**
   *(Note: This step is run on the central server first to allocate voucher ranges).*
   Run the following on the central server to register this new branch and get its allocated range:
   ```bash
   php artisan branch:register BRXYZ "Miami Store"
   ```
   Use the exact `BRXYZ` branch code in the local `.env` of the branch server.

5. **Run Migrations on Local DB**
   Migrate the local MySQL database schema:
   ```bash
   php artisan migrate --force
   ```

6. **Start Background Sync Worker**
   Run the queue worker to process synchronization jobs linking the branch to the central cloud instance:
   ```bash
   php artisan queue:work
   ```
