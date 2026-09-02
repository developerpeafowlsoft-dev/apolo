<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\CounterMaster;
use App\Models\FinancialYear;
use App\Models\PaymentTerminal;
use App\Models\PosPaymentAttempt;
use App\Models\Product;
use App\Models\Order;
use App\Models\POSShift;
use App\Enums\PaymentTerminalProvider;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;
use App\Services\POS\PosPaymentAttemptService;
use App\Services\POS\PosPaymentVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Exception;

class PosTerminalFeatureAndRegressionTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $counter;
    protected $terminal;
    protected $financialYear;
    protected $product;
    protected $shift;
    protected $attemptService;
    protected $verificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attemptService = app(PosPaymentAttemptService::class);
        $this->verificationService = app(PosPaymentVerificationService::class);

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Feature Test Shop', 'email' => 'feat@shop.com', 'phone' => '123']);
        $this->user = User::first() ?: User::create(['name' => 'Feature User', 'email' => 'feat@user.com', 'password' => '123']);
        $this->actingAs($this->user);

        $this->counter = CounterMaster::firstOrCreate(
            ['shop_id' => $this->shop->id, 'code' => 'F1'],
            ['counter_name' => 'Feature Counter', 'counter_short_name' => 'F1', 'voucher_prefix' => 'FT1', 'is_active' => true]
        );

        $this->financialYear = FinancialYear::first() ?: FinancialYear::create([
            'name' => 'FY 2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31'
        ]);

        $this->terminal = PaymentTerminal::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'name' => 'Feature EDC Terminal',
            'provider' => PaymentTerminalProvider::PAYTM,
            'terminal_id' => 'TERM-PAYTM-777',
            'config_data' => ['shared_secret' => 'secret_paytm_777', 'merchant_id' => 'MERCH-PAYTM-777'],
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Test Product Variant A',
            'price' => 250.00,
            'unit_price' => 250.00,
            'quantity' => 50,
        ]);

        $this->shift = POSShift::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'user_id' => $this->user->id,
            'opening_cash' => 1000.00,
            'status' => 'open',
            'start_time' => now(),
        ]);
    }

    public function test_initiate_paytm_card_payment()
    {
        $payload = [
            'counter_id' => $this->counter->id,
            'cart_name' => 'CartPaytmCard',
            'provider' => 'paytm',
            'payment_method' => 'card',
            'amount' => 250.00,
            'items' => [['product_id' => $this->product->id, 'qty' => 1, 'rate' => 250.00]],
        ];

        $res = $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
        $this->assertArrayHasKey('attempt_id', $res);
        $this->assertEquals('paytm', $res['provider']);
    }

    public function test_initiate_paytm_upi_payment()
    {
        $payload = [
            'counter_id' => $this->counter->id,
            'cart_name' => 'CartPaytmUPI',
            'provider' => 'paytm',
            'payment_method' => 'upi',
            'amount' => 250.00,
            'items' => [['product_id' => $this->product->id, 'qty' => 1, 'rate' => 250.00]],
        ];

        $res = $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
        $this->assertEquals('upi', $res['payment_method']);
    }

    public function test_initiate_phonepe_card_payment()
    {
        $phonePeTerminal = PaymentTerminal::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'name' => 'PhonePe EDC Terminal',
            'provider' => PaymentTerminalProvider::PHONEPE,
            'terminal_id' => 'TERM-PPE-888',
            'config_data' => ['shared_secret' => 'secret_ppe_888'],
            'is_active' => true,
        ]);

        $payload = [
            'counter_id' => $this->counter->id,
            'cart_name' => 'CartPhonePeCard',
            'provider' => 'phonepe',
            'payment_method' => 'card',
            'amount' => 250.00,
            'items' => [['product_id' => $this->product->id, 'qty' => 1, 'rate' => 250.00]],
        ];

        $res = $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
        $this->assertEquals('phonepe', $res['provider']);
    }

    public function test_inactive_terminal_rejection()
    {
        $this->terminal->update(['is_active' => false]);

        $this->expectException(Exception::class);
        $payload = [
            'counter_id' => $this->counter->id,
            'cart_name' => 'CartInactiveTerminal',
            'provider' => 'paytm',
            'payment_method' => 'card',
            'amount' => 250.00,
            'items' => [],
        ];

        $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
    }

    public function test_duplicate_initiation_on_active_cart_rejected()
    {
        $payload = [
            'counter_id' => $this->counter->id,
            'cart_name' => 'CartActiveDupeCheck',
            'provider' => 'paytm',
            'payment_method' => 'card',
            'amount' => 250.00,
            'items' => [['product_id' => $this->product->id, 'qty' => 1, 'rate' => 250.00]],
        ];

        $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("unresolved payment attempt");

        $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
    }

    public function test_cash_billing_regression_flow()
    {
        $order = Order::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'pos_order' => true,
            'order_code' => 'ORDC-CASH-001',
            'order_number' => 'ORD-CASH-001',
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'payment_method' => \App\Enums\PaymentMethod::CASH,
            'total_amount' => 250.00,
            'payable_amount' => 250.00,
        ]);

        $this->assertNotNull($order);
        $this->assertEquals(\App\Enums\PaymentStatus::PAID, $order->payment_status);
    }
}
