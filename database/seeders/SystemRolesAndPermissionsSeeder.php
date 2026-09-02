<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SystemRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed all system roles and granular permissions.
     */
    public function run(): void
    {
        // 1. Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Load all permissions from config
        $allPermissionArray = config('acl.permissions');

        $createdPermissions = [];

        foreach ($allPermissionArray as $modelType => $allPermissions) {
            $type = $modelType === 'adminMultiShop' ? 'admin' : $modelType;
            $type = $type === 'shopMultiShop' ? 'shop' : $type;

            foreach ($allPermissions as $permissionName => $permissionValues) {
                foreach ($permissionValues as $permission) {
                    $permName = "{$type}.{$permissionName}.{$permission}";
                    $p = Permission::firstOrCreate([
                        'name' => $permName,
                        'guard_name' => 'web',
                    ]);
                    $createdPermissions[$permName] = $p;
                }
            }
        }

        // 3. Define Roles & their default permission blueprints
        $allPermissionNames = array_keys($createdPermissions);

        // --- A. ADMIN Role (Full Business & Store Administration) ---
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($allPermissionNames);

        // --- B. CASHIER Role (POS Billing, Cash Drawer, Shifts & Returns) ---
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web', 'is_shop' => true]);
        $cashierPerms = array_filter($allPermissionNames, function ($name) {
            return str_contains($name, 'pos.')
                || str_contains($name, 'order.index')
                || str_contains($name, 'order.show')
                || str_contains($name, 'order.payment')
                || str_contains($name, 'customer.index')
                || str_contains($name, 'customer.show')
                || str_contains($name, 'customer.create')
                || str_contains($name, 'product.index')
                || str_contains($name, 'product.show')
                || str_contains($name, 'salesman.index')
                || str_contains($name, 'counterMaster.index')
                || str_contains($name, 'voucher.index');
        });
        $cashierRole->syncPermissions($cashierPerms);

        // --- C. ACCOUNTANT Role (Indian Accounting, Ledgers, GST & Financials) ---
        $accountantRole = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web', 'is_shop' => true]);
        $accountantPerms = array_filter($allPermissionNames, function ($name) {
            return str_contains($name, 'reports.')
                || str_contains($name, 'accountMaster.')
                || str_contains($name, 'accountBalance.')
                || str_contains($name, 'bankMaster.')
                || str_contains($name, 'tdsMaster.')
                || str_contains($name, 'hsnMaster.')
                || str_contains($name, 'vatTax.')
                || str_contains($name, 'order.index')
                || str_contains($name, 'order.show')
                || str_contains($name, 'purchaseProduct.index')
                || str_contains($name, 'purchaseProduct.view')
                || str_contains($name, 'inwardProduct.index');
        });
        $accountantRole->syncPermissions($accountantPerms);

        // --- D. INVENTORY MANAGER Role (Inward Invoices, Barcodes, Purchasing, Stock) ---
        $inventoryRole = Role::firstOrCreate(['name' => 'inventory_manager', 'guard_name' => 'web', 'is_shop' => true]);
        $inventoryPerms = array_filter($allPermissionNames, function ($name) {
            return str_contains($name, 'inwardProduct.')
                || str_contains($name, 'purchaseProduct.')
                || str_contains($name, 'product.')
                || str_contains($name, 'itemMaster.')
                || str_contains($name, 'designMaster.')
                || str_contains($name, 'category.')
                || str_contains($name, 'subcategory.')
                || str_contains($name, 'brand.')
                || str_contains($name, 'color.')
                || str_contains($name, 'size.')
                || str_contains($name, 'unit.')
                || str_contains($name, 'material.')
                || str_contains($name, 'hsnMaster.')
                || str_contains($name, 'bulk-product')
                || str_contains($name, 'gallery.');
        });
        $inventoryRole->syncPermissions($inventoryPerms);

        // --- E. SALES MANAGER Role (Sales Attendants, Promotions, Customers, Analytics) ---
        $salesManagerRole = Role::firstOrCreate(['name' => 'sales_manager', 'guard_name' => 'web', 'is_shop' => true]);
        $salesManagerPerms = array_filter($allPermissionNames, function ($name) {
            return str_contains($name, 'salesman.')
                || str_contains($name, 'counterMaster.')
                || str_contains($name, 'pos.')
                || str_contains($name, 'order.')
                || str_contains($name, 'customer.')
                || str_contains($name, 'voucher.')
                || str_contains($name, 'flashSale.')
                || str_contains($name, 'customerChat.')
                || str_contains($name, 'reports.counterProductivity')
                || str_contains($name, 'reports.visualAnalytics');
        });
        $salesManagerRole->syncPermissions($salesManagerPerms);

        // --- F. STORE STAFF Role (Assistance & Read Operations) ---
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web', 'is_shop' => true]);
        $staffPerms = array_filter($allPermissionNames, function ($name) {
            return str_contains($name, '.index')
                || str_contains($name, '.show')
                || str_contains($name, 'customerChat.')
                || str_contains($name, 'pos.index')
                || str_contains($name, 'pos.sales');
        });
        $staffRole->syncPermissions($staffPerms);

        // --- G. VISITOR Role (Dashboard & Catalog View) ---
        $visitorRole = Role::firstOrCreate(['name' => 'visitor', 'guard_name' => 'web']);
        $visitorPerms = array_filter($allPermissionNames, function ($name) {
            return str_ends_with($name, '.index') && (
                str_contains($name, 'dashboard.')
                || str_contains($name, 'product.')
                || str_contains($name, 'category.')
            );
        });
        $visitorRole->syncPermissions($visitorPerms);

        // 4. Clear Cache
        Artisan::call('cache:clear');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
