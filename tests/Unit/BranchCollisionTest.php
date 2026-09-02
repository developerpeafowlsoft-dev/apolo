<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Branch;
use App\Models\Shop;
use App\Models\FinancialYear;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Services\BranchContext;
use App\Services\Accounting\VoucherService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BranchCollisionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_branch_range_allocation_increments_correctly()
    {
        // Assert clean starting point for ranges
        Branch::query()->delete();

        $context = new BranchContext(['mode' => 'central']);
        
        $start1 = $context->nextVoucherRangeStart();
        $this->assertEquals(500000, $start1);

        $branch1 = Branch::create([
            'branch_code' => 'TEST01',
            'name' => 'Test Branch 1',
            'voucher_range_start' => $start1,
            'voucher_range_end' => $start1 + 100000 - 1,
            'bill_prefix' => 'TEST01-',
            'is_active' => true,
        ]);

        $start2 = $context->nextVoucherRangeStart();
        $this->assertEquals(600000, $start2);

        $branch2 = Branch::create([
            'branch_code' => 'TEST02',
            'name' => 'Test Branch 2',
            'voucher_range_start' => $start2,
            'voucher_range_end' => $start2 + 100000 - 1,
            'bill_prefix' => 'TEST02-',
            'is_active' => true,
        ]);

        $start3 = $context->nextVoucherRangeStart();
        $this->assertEquals(700000, $start3);
    }

    public function test_branches_have_isolated_voucher_sequences()
    {
        // 1. Setup minimal database dependencies
        $shop = Shop::first();
        if (!$shop) {
            $shop = Shop::create([
                'name' => 'Test Shop',
                'prefix' => 'TS',
                'email' => 'test@shop.com',
                'phone' => '1234567890',
            ]);
        }

        $fy = FinancialYear::first();
        if (!$fy) {
            $fy = FinancialYear::create([
                'name' => 'FY2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => 1,
            ]);
        }

        $type = AccountType::firstOrCreate(['name' => 'Asset'], ['is_active' => 1, 'is_default' => 0]);
        $group = AccountGroup::firstOrCreate(['name' => 'Cash'], [
            'code' => 'CASH-GRP',
            'account_type_id' => $type->id,
            'is_editable' => 1,
        ]);

        $accountDr = Account::create([
            'name' => 'Cash Account 1',
            'code' => 'CASH01',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);

        $accountCr = Account::create([
            'name' => 'Revenue Account 1',
            'code' => 'REV01',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);

        // 2. Register Branch 1 (BR100) and Branch 2 (BR200)
        Branch::query()->delete();

        $branch1 = Branch::create([
            'branch_code' => 'BR100',
            'name' => 'Branch 100',
            'voucher_range_start' => 500000,
            'voucher_range_end' => 599999,
            'bill_prefix' => 'BR100-',
            'is_active' => true,
        ]);

        $branch2 = Branch::create([
            'branch_code' => 'BR200',
            'name' => 'Branch 200',
            'voucher_range_start' => 600000,
            'voucher_range_end' => 699999,
            'bill_prefix' => 'BR200-',
            'is_active' => true,
        ]);

        // 3. Generate a voucher under Branch 1 Context
        $context1 = new BranchContext(['mode' => 'branch', 'code' => 'BR100']);
        $this->app->instance(BranchContext::class, $context1);

        $voucherService = app(VoucherService::class);

        $voucher1 = $voucherService->create([
            'shop_id' => $shop->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => 'PAYMENT',
            'date' => '2026-07-22',
            'entries' => [
                ['account_id' => $accountDr->id, 'type' => 'Dr', 'amount' => 100],
                ['account_id' => $accountCr->id, 'type' => 'Cr', 'amount' => 100],
            ]
        ]);

        // 4. Generate a voucher under Branch 2 Context
        $context2 = new BranchContext(['mode' => 'branch', 'code' => 'BR200']);
        $this->app->instance(BranchContext::class, $context2);

        $voucher2 = $voucherService->create([
            'shop_id' => $shop->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => 'PAYMENT',
            'date' => '2026-07-22',
            'entries' => [
                ['account_id' => $accountDr->id, 'type' => 'Dr', 'amount' => 100],
                ['account_id' => $accountCr->id, 'type' => 'Cr', 'amount' => 100],
            ]
        ]);

        // 5. Assertions
        // Branch 1 range starts at 500000, so first voucher is 500000 (padded)
        $this->assertStringContainsString('500000', $voucher1->voucher_no);
        $this->assertEquals($branch1->id, $voucher1->branch_id);

        // Branch 2 range starts at 600000, so first voucher is 600000 (padded)
        $this->assertStringContainsString('600000', $voucher2->voucher_no);
        $this->assertEquals($branch2->id, $voucher2->branch_id);
    }

    public function test_requires_internet_middleware_blocks_restricted_routes_in_branch_mode()
    {
        $context = new BranchContext(['mode' => 'branch', 'code' => 'BR100']);
        $this->app->instance(BranchContext::class, $context);

        $response = $this->postJson('/api/send-otp', []);

        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'message' => 'This action is disabled in offline branch mode.'
        ]);
    }

    public function test_requires_internet_middleware_allows_restricted_routes_in_central_mode()
    {
        $context = new BranchContext(['mode' => 'central', 'code' => null]);
        $this->app->instance(BranchContext::class, $context);

        $response = $this->postJson('/api/send-otp', []);

        $this->assertNotEquals(503, $response->getStatusCode());
    }

    public function test_observers_enqueue_sync_jobs_in_branch_mode()
    {
        $shop = Shop::first();
        if (!$shop) {
            $shop = Shop::create([
                'name' => 'Test Shop',
                'prefix' => 'TS',
                'email' => 'test@shop.com',
                'phone' => '1234567890',
            ]);
        }

        $fy = FinancialYear::first();
        if (!$fy) {
            $fy = FinancialYear::create([
                'name' => 'FY2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => 1,
            ]);
        }

        $type = AccountType::firstOrCreate(['name' => 'Asset'], ['is_active' => 1, 'is_default' => 0]);
        $group = AccountGroup::firstOrCreate(['name' => 'Cash'], [
            'code' => 'CASH-GRP',
            'account_type_id' => $type->id,
            'is_editable' => 1,
        ]);

        $accountDr = Account::create([
            'name' => 'Cash Account 1',
            'code' => 'CASH01',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);

        $accountCr = Account::create([
            'name' => 'Revenue Account 1',
            'code' => 'REV01',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);

        Branch::query()->delete();
        $branch = Branch::create([
            'branch_code' => 'BR100',
            'name' => 'Branch 100',
            'voucher_range_start' => 500000,
            'voucher_range_end' => 599999,
            'bill_prefix' => 'BR100-',
            'is_active' => true,
        ]);

        $context = new BranchContext(['mode' => 'branch', 'code' => 'BR100']);
        $this->app->instance(BranchContext::class, $context);

        // Create and authenticate user as shop owner
        $user = \App\Models\User::create([
            'name' => 'Shop',
            'last_name' => 'Owner',
            'email' => 'shopowner_' . uniqid() . '@test.com',
            'phone' => (string)rand(1000000000, 9999999999),
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => \App\Enums\Roles::SHOP->value, 'guard_name' => 'web']);
        $user->assignRole(\App\Enums\Roles::SHOP->value);
        $this->actingAs($user);

        // Register observers manually since application booted in central mode before test execution
        \App\Models\Order::observe(\App\Observers\BranchSyncObserver::class);
        \App\Models\Voucher::observe(\App\Observers\BranchSyncObserver::class);
        \App\Models\ProductPurchase::observe(\App\Observers\BranchSyncObserver::class);

        \App\Models\SyncQueue::query()->delete();

        $voucherService = app(VoucherService::class);
        $voucher = $voucherService->create([
            'shop_id' => $shop->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => 'PAYMENT',
            'date' => '2026-07-22',
            'entries' => [
                ['account_id' => $accountDr->id, 'type' => 'Dr', 'amount' => 100],
                ['account_id' => $accountCr->id, 'type' => 'Cr', 'amount' => 100],
            ]
        ]);

        $this->assertEquals(1, \App\Models\SyncQueue::count());
        $queueItem = \App\Models\SyncQueue::first();
        $this->assertEquals(\App\Models\Voucher::class, $queueItem->syncable_type);
        $this->assertEquals($voucher->id, $queueItem->syncable_id);
        $this->assertEquals('pending', $queueItem->status);

        $response = $this->getJson('/shop/branch/sync-status');
        $response->assertStatus(200);
        $response->assertJson([
            'pending_count' => 1,
            'failed_count' => 0,
        ]);
    }

    public function test_sync_ingest_requires_api_key()
    {
        $response = $this->postJson('/api/central/sync-ingest', [
            'syncable_type' => \App\Models\Voucher::class,
            'syncable_id' => 999,
            'payload' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_sync_ingest_voucher_ingestion_and_idempotency()
    {
        $shop = Shop::first();
        if (!$shop) {
            $shop = Shop::create([
                'name' => 'Test Shop',
                'prefix' => 'TS',
                'email' => 'test@shop.com',
                'phone' => '1234567890',
            ]);
        }

        $fy = FinancialYear::first();
        if (!$fy) {
            $fy = FinancialYear::create([
                'name' => 'FY2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => 1,
            ]);
        }

        $type = AccountType::firstOrCreate(['name' => 'Asset'], ['is_active' => 1, 'is_default' => 0]);
        $group = AccountGroup::firstOrCreate(['name' => 'Cash'], [
            'code' => 'CASH-GRP',
            'account_type_id' => $type->id,
            'is_editable' => 1,
        ]);

        $accountDr = Account::create([
            'name' => 'Cash Account Ingest',
            'code' => 'CASH_ING',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);

        $accountCr = Account::create([
            'name' => 'Revenue Account Ingest',
            'code' => 'REV_ING',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);

        // Create a branch centrally with an API Key
        Branch::query()->delete();
        $branch = Branch::create([
            'branch_code' => 'BR_CENTRAL',
            'name' => 'Central Branch',
            'voucher_range_start' => 700000,
            'voucher_range_end' => 799999,
            'bill_prefix' => 'BR_C-',
            'api_key' => 'secret-branch-token-xyz',
            'is_active' => true,
        ]);

        $payload = [
            'voucher_no' => 'BR_C-S-00001',
            'voucher_type' => 'PAYMENT',
            'date' => '2026-07-22',
            'shop_id' => $shop->id,
            'financial_year_id' => $fy->id,
            'entries' => [
                ['account_id' => $accountDr->id, 'type' => 'Dr', 'amount' => 150],
                ['account_id' => $accountCr->id, 'type' => 'Cr', 'amount' => 150],
            ]
        ];

        // 1. Ingest successfully
        $response = $this->withHeaders(['X-Branch-API-Key' => 'secret-branch-token-xyz'])
            ->postJson('/api/central/sync-ingest', [
                'syncable_type' => \App\Models\Voucher::class,
                'syncable_id' => 9991, // original_id
                'payload' => $payload,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert record exists
        $voucher = \App\Models\Voucher::where('branch_id', $branch->id)
            ->where('original_id', 9991)
            ->first();
        $this->assertNotNull($voucher);
        $this->assertEquals('BR_C-S-00001', $voucher->voucher_no);

        // 2. Test Idempotency: Send duplicate request -> should return 200 OK but skip insert
        $cntBefore = \App\Models\Voucher::count();
        $responseDuplicate = $this->withHeaders(['X-Branch-API-Key' => 'secret-branch-token-xyz'])
            ->postJson('/api/central/sync-ingest', [
                'syncable_type' => \App\Models\Voucher::class,
                'syncable_id' => 9991,
                'payload' => $payload,
            ]);

        $responseDuplicate->assertStatus(200);
        $this->assertEquals($cntBefore, \App\Models\Voucher::count());
    }

    public function test_sync_ingest_order_stock_reconciliation()
    {
        // Setup branch with API Key
        Branch::query()->delete();
        $branch = Branch::create([
            'branch_code' => 'BR_STOCK',
            'name' => 'Stock Branch',
            'voucher_range_start' => 800000,
            'voucher_range_end' => 899999,
            'bill_prefix' => 'BR_S-',
            'api_key' => 'secret-stock-token',
            'is_active' => true,
        ]);

        $shop = Shop::first();
        if (!$shop) {
            $shop = Shop::create([
                'name' => 'Test Shop',
                'prefix' => 'TS',
                'email' => 'test@shop.com',
                'phone' => '1234567890',
            ]);
        }

        $customer = \App\Models\Customer::first();
        if (!$customer) {
            $customer = \App\Models\Customer::create([
                'user_id' => $user->id,
            ]);
        }

        // Create product with stock = 10
        $product = \App\Models\Product::create([
            'name' => 'Central Stock Product',
            'price' => 100,
            'quantity' => 10,
            'is_active' => 1,
        ]);

        $payload = [
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'order_code' => '00001',
            'prefix' => 'BR_S',
            'payable_amount' => 500,
            'total_amount' => 500,
            'payment_status' => 'Paid',
            'order_status' => 'Delivered',
            'invoice_no' => 'BR_S-00001',
            'products' => [
                [
                    'id' => $product->id,
                    'pivot' => [
                        'quantity' => 12, // sale of 12 units pushes stock below 0
                    ]
                ]
            ]
        ];

        // Ensure clean reconciliation flags
        \App\Models\StockReconciliationFlag::query()->delete();

        // Ingest the order
        $response = $this->withHeaders(['X-Branch-API-Key' => 'secret-stock-token'])
            ->postJson('/api/central/sync-ingest', [
                'syncable_type' => \App\Models\Order::class,
                'syncable_id' => 1234, // original_id
                'payload' => $payload,
            ]);

        $response->assertStatus(200);

        // Verify product stock capped at 0
        $product->refresh();
        $this->assertEquals(0, $product->quantity);

        // Verify stock reconciliation flags entry was created
        $this->assertEquals(1, \App\Models\StockReconciliationFlag::count());
        $flag = \App\Models\StockReconciliationFlag::first();
        $this->assertEquals($branch->id, $flag->branch_id);
        $this->assertEquals($product->id, $flag->product_id);
        $this->assertEquals(12, $flag->reported_sale_qty);
        $this->assertEquals(10, $flag->previous_stock);
        $this->assertEquals(-2, $flag->new_stock);
    }
}
