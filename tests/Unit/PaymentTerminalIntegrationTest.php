<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\ProductBarcode;
use App\Models\Product;
use App\Models\Shop;
use App\Models\FinancialYear;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\CounterMaster;
use App\Models\POSShift;
use App\Models\PosCart;
use App\Models\User;
use App\Services\POS\PosOrderFinalizationService;
use App\Services\POS\POSShiftService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentTerminalIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $fy;
    protected $user;
    protected $counter;
    protected $finalizationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finalizationService = app(PosOrderFinalizationService::class);

        // 1. Create shop
        $this->shop = Shop::first();
        if (!$this->shop) {
            $this->shop = Shop::create([
                'name' => 'POS integration Shop',
                'prefix' => 'PIS',
                'email' => 'pos@integration.com',
                'phone' => '9876543210',
            ]);
        }

        // 2. Create financial year
        $this->fy = FinancialYear::first();
        if (!$this->fy) {
            $this->fy = FinancialYear::create([
                'name' => 'FY2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => 1,
            ]);
        }

        // 3. Create user (cashier)
        $this->user = User::first();
        if (!$this->user) {
            $this->user = User::create([
                'name' => 'Cashier User',
                'email' => 'cashier@pos.com',
                'password' => bcrypt('password'),
            ]);
        }
        $this->actingAs($this->user);

        // 4. Create counter
        $this->counter = CounterMaster::where('shop_id', $this->shop->id)->first();
        if (!$this->counter) {
            $this->counter = CounterMaster::create([
                'shop_id' => $this->shop->id,
                'code' => 'C01',
                'counter_name' => 'Counter 01',
                'counter_short_name' => 'C1',
                'voucher_prefix' => 'POS',
                'is_active' => true,
            ]);
        }

        // 5. Seed accounting accounts
        $type = AccountType::firstOrCreate(['name' => 'Asset'], ['is_active' => 1, 'is_default' => 0]);
        $group = AccountGroup::firstOrCreate(['name' => 'Cash'], [
            'code' => 'CASH-GRP',
            'account_type_id' => $type->id,
            'is_editable' => 1,
        ]);

        Account::firstOrCreate(['code' => 'CASH'], [
            'name' => 'Cash',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);
        Account::firstOrCreate(['code' => 'BANK'], [
            'name' => 'Bank',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);
        Account::firstOrCreate(['code' => 'SALES'], [
            'name' => 'Sales',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);
        Account::firstOrCreate(['code' => 'ROUND'], [
            'name' => 'Round Off',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);
        Account::firstOrCreate(['code' => 'GST_OUT_C'], [
            'name' => 'CGST',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);
        Account::firstOrCreate(['code' => 'GST_OUT_S'], [
            'name' => 'SGST',
            'account_group_id' => $group->id,
            'is_active' => 1,
        ]);
    }

    /**
     * Start a fresh shift session.
     */
    protected function startShift()
    {
        $shiftService = app(POSShiftService::class);
        $active = $shiftService->getActiveShift($this->shop->id, $this->counter->id, $this->user->id);
        if ($active) {
            return $active;
        }
        return $shiftService->openShift($this->shop->id, $this->counter->id, $this->user->id, 1000);
    }

    public function test_existing_cash_sale_finalization_succeeds()
    {
        $this->startShift();

        // Create product with stock
        $product = Product::create([
            'name' => 'Test Cash product',
            'price' => 500,
            'quantity' => 10,
            'is_active' => 1,
        ]);

        $inwardProduct = \App\Models\InwardProduct::first();
        $inwardInvoice = \App\Models\InwardInvoice::first();

        $barcode = ProductBarcode::create([
            'product_id' => $product->id,
            'barcode_number' => '123456789012',
            'shop_id' => $this->shop->id,
            'inward_product_id' => $inwardProduct->id,
            'inward_invoice_id' => $inwardInvoice->id,
            'is_sold' => 0,
        ]);

        $payload = [
            'payment_method' => 'cash',
            'customer_phone' => '9998887776',
            'customer_name' => 'John Doe',
            'counter_id' => $this->counter->id,
            'paid_amount' => 500,
            'items' => [
                [
                    'barcode' => $barcode->barcode_number,
                    'qty' => 1,
                    'rate' => 500,
                    'disc_percent' => 0,
                ]
            ]
        ];

        $order = $this->finalizationService->finalize($payload, $this->shop, $this->fy);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(500, $order->payable_amount);
        $this->assertEquals(\App\Enums\PaymentMethod::CASH, $order->payment_method);
        $this->assertEquals('Paid', $order->payment_status->value);

        // Verify product barcode marked sold
        $barcode->refresh();
        $this->assertEquals(1, $barcode->is_sold);

        // Verify product stock decremented
        $product->refresh();
        $this->assertEquals(9, $product->quantity);
    }

    public function test_duplicate_finalization_with_same_idempotency_key_returns_cached_order()
    {
        $this->startShift();

        $product = Product::create([
            'name' => 'Idempotent Product',
            'price' => 100,
            'quantity' => 10,
            'is_active' => 1,
        ]);

        $inwardProduct = \App\Models\InwardProduct::first();
        $inwardInvoice = \App\Models\InwardInvoice::first();

        $barcode = ProductBarcode::create([
            'product_id' => $product->id,
            'barcode_number' => '9999988888',
            'shop_id' => $this->shop->id,
            'inward_product_id' => $inwardProduct->id,
            'inward_invoice_id' => $inwardInvoice->id,
            'is_sold' => 0,
        ]);

        $payload = [
            'payment_method' => 'cash',
            'customer_phone' => '9998887776',
            'counter_id' => $this->counter->id,
            'items' => [
                [
                    'barcode' => $barcode->barcode_number,
                    'qty' => 1,
                    'rate' => 100,
                ]
            ]
        ];

        $key = 'idemp_key_unique_123';

        $order1 = $this->finalizationService->finalize($payload, $this->shop, $this->fy, $key);
        $this->assertNotNull($order1);

        // Second checkout call with same key should skip and return order1
        $order2 = $this->finalizationService->finalize($payload, $this->shop, $this->fy, $key);
        $this->assertEquals($order1->id, $order2->id);

        // Verify stock decremented only once
        $product->refresh();
        $this->assertEquals(9, $product->quantity);
    }

    public function test_insufficient_stock_fails_and_rolls_back()
    {
        $this->startShift();

        $product = Product::create([
            'name' => 'Low Stock Product',
            'price' => 100,
            'quantity' => 1, // Only 1 in stock
            'is_active' => 1,
        ]);

        $inwardProduct = \App\Models\InwardProduct::first();
        $inwardInvoice = \App\Models\InwardInvoice::first();

        $barcode = ProductBarcode::create([
            'product_id' => $product->id,
            'barcode_number' => '8888877777',
            'shop_id' => $this->shop->id,
            'inward_product_id' => $inwardProduct->id,
            'inward_invoice_id' => $inwardInvoice->id,
            'is_sold' => 0,
        ]);

        $payload = [
            'payment_method' => 'cash',
            'counter_id' => $this->counter->id,
            'items' => [
                [
                    'barcode' => $barcode->barcode_number,
                    'qty' => 5, // Buying 5 units
                    'rate' => 100,
                ]
            ]
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("does not have enough stock");

        $this->finalizationService->finalize($payload, $this->shop, $this->fy);
    }

    public function test_unbalanced_accounting_rolls_back_entire_sale()
    {
        $this->startShift();

        $product = Product::create([
            'name' => 'Balanced Voucher Product',
            'price' => 100,
            'quantity' => 10,
            'is_active' => 1,
        ]);

        $inwardProduct = \App\Models\InwardProduct::first();
        $inwardInvoice = \App\Models\InwardInvoice::first();

        $barcode = ProductBarcode::create([
            'product_id' => $product->id,
            'barcode_number' => '7777766666',
            'shop_id' => $this->shop->id,
            'inward_product_id' => $inwardProduct->id,
            'inward_invoice_id' => $inwardInvoice->id,
            'is_sold' => 0,
        ]);

        $payload = [
            'payment_method' => 'cash',
            'counter_id' => $this->counter->id,
            'items' => [
                [
                    'barcode' => $barcode->barcode_number,
                    'qty' => 1,
                    'rate' => 100,
                ]
            ]
        ];

        // Intentionally delete SALES account to force balancing / creation exception during accounting step
        Account::where('code', 'SALES')->delete();

        try {
            $this->finalizationService->finalize($payload, $this->shop, $this->fy);
            $this->fail("Expected exception due to missing account dependencies.");
        } catch (\Exception $e) {
            // Confirm transaction rolled back stock & barcode state
            $barcode->refresh();
            $this->assertEquals(0, $barcode->is_sold);

            $product->refresh();
            $this->assertEquals(10, $product->quantity);
        }
    }

    public function test_cart_is_not_cleared_if_finalization_fails()
    {
        $this->startShift();

        // Create persistent DB cart
        $cart = PosCart::create([
            'shop_id' => $this->shop->id,
            'name' => 'CashierCart1',
            'subtotal' => 200,
            'total' => 200,
        ]);

        $product = Product::create([
            'name' => 'Failing Cart Product',
            'price' => 200,
            'quantity' => 10,
            'is_active' => 1,
        ]);

        $inwardProduct = \App\Models\InwardProduct::first();
        $inwardInvoice = \App\Models\InwardInvoice::first();

        $barcode = ProductBarcode::create([
            'product_id' => $product->id,
            'barcode_number' => '6666655555',
            'shop_id' => $this->shop->id,
            'inward_product_id' => $inwardProduct->id,
            'inward_invoice_id' => $inwardInvoice->id,
            'is_sold' => 0,
        ]);

        // Cart matches cart_name in checkout payload
        $payload = [
            'payment_method' => 'cash',
            'counter_id' => $this->counter->id,
            'cart_name' => 'CashierCart1',
            'items' => [
                [
                    'barcode' => $barcode->barcode_number,
                    'qty' => 15, // Out of stock to force failure
                    'rate' => 200,
                ]
            ]
        ];

        try {
            $this->finalizationService->finalize($payload, $this->shop, $this->fy);
        } catch (\Exception $e) {
            // Confirm cart record still exists in database
            $this->assertDatabaseHas('pos_carts', [
                'id' => $cart->id,
                'name' => 'CashierCart1'
            ]);
        }
    }
}
