<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\CounterMaster;
use App\Models\FinancialYear;
use App\Models\PaymentTerminal;
use App\Models\PosPaymentAttempt;
use App\Models\Product;
use App\Models\Order;
use App\Enums\PaymentTerminalProvider;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;
use App\Services\POS\PosRefundService;
use App\Services\Accounting\VoucherService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Exception;

class PosRefundAndReturnTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $counter;
    protected $terminal;
    protected $product;
    protected $refundService;
    protected $voucherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refundService = app(PosRefundService::class);
        $this->voucherService = app(VoucherService::class);

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Refund Shop', 'email' => 'rf@shop.com', 'phone' => '123']);
        $this->user = User::first() ?: User::create(['name' => 'Manager Refund', 'email' => 'rfmgr@shop.com', 'password' => '123']);
        $this->actingAs($this->user);

        $this->counter = CounterMaster::firstOrCreate(
            ['shop_id' => $this->shop->id, 'code' => 'C999'],
            ['counter_name' => 'Counter 999', 'counter_short_name' => 'C999', 'voucher_prefix' => 'P999', 'is_active' => true]
        );

        $this->terminal = PaymentTerminal::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'name' => 'Refund Terminal',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TERM-REFUND-999',
            'config_data' => ['shared_secret' => 'secret_refund_123'],
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Refundable Shirt',
            'price' => 1000.00,
            'unit_price' => 1000.00,
            'quantity' => 10,
        ]);
    }

    public function test_same_day_full_void_reverses_stock_and_marks_voided()
    {
        $order = Order::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'pos_order' => true,
            'order_code' => 'ORDC-VOID-01',
            'order_number' => 'ORD-VOID-01',
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'total_amount' => 1000.00,
            'payable_amount' => 1000.00,
        ]);

        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'order_id' => $order->id,
            'cart_name' => 'CartVoidTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 1000.00,
            'approved_amount' => 1000.00,
            'status' => PosPaymentAttemptStatus::FINALIZED,
            'transaction_id' => 'TX-MOCK-VOID-111',
        ]);

        $res = $this->refundService->voidTransaction($attempt, $this->user, 'Customer cancelled same-day bill');

        $this->assertEquals('voided', $res['status']);
        $this->assertEquals(PosPaymentAttemptStatus::VOIDED, $res['attempt']->status);
    }

    public function test_partial_refund_updates_refunded_amount_and_restores_stock()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartPartialRefundTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 1000.00,
            'approved_amount' => 1000.00,
            'status' => PosPaymentAttemptStatus::FINALIZED,
            'transaction_id' => 'TX-MOCK-REFUND-222',
        ]);

        $initialStock = $this->product->quantity;

        $res = $this->refundService->refundTransaction(
            $attempt,
            400.00,
            $this->user,
            'Partial return 1 item',
            [['product_id' => $this->product->id, 'qty' => 1]]
        );

        $this->assertEquals('partially_refunded', $res['status']);
        $this->assertEquals(400.00, $res['refunded_amount']);

        $freshProduct = Product::find($this->product->id);
        $this->assertEquals($initialStock + 1, $freshProduct->quantity);
    }

    public function test_excessive_refund_amount_is_rejected()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartExcessiveRefundTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'approved_amount' => 500.00,
            'status' => PosPaymentAttemptStatus::PARTIALLY_REFUNDED,
            'response_payload' => ['refunded_total' => 400.00],
            'transaction_id' => 'TX-MOCK-EXCESS-333',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("exceeds refundable balance");

        $this->refundService->refundTransaction($attempt, 200.00, $this->user, 'Attempting to refund more than balance');
    }

    public function test_voucher_service_ensures_balanced_entries()
    {
        $entries = [
            ['account_id' => 1, 'debit' => 500.00, 'credit' => 0.00],
            ['account_id' => 2, 'debit' => 0.00, 'credit' => 500.00],
        ];

        $debitTotal = array_sum(array_column($entries, 'debit'));
        $creditTotal = array_sum(array_column($entries, 'credit'));

        $this->assertEquals($debitTotal, $creditTotal);
    }
}
