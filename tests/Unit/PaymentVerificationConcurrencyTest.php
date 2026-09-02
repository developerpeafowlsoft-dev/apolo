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
use App\Models\PosPaymentTender;
use App\Models\PosPrintJob;
use App\Enums\PaymentTerminalProvider;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;
use App\Services\POS\PosPaymentVerificationService;
use App\Services\POS\BridgeRequestSigner;
use App\Exceptions\PaymentAmountMismatchException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Exception;

class PaymentVerificationConcurrencyTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $counter;
    protected $terminal;
    protected $financialYear;
    protected $verificationService;
    protected $signer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verificationService = app(PosPaymentVerificationService::class);
        $this->signer = new BridgeRequestSigner();

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Test Shop', 'email' => 's@s.com', 'phone' => '123']);
        $this->user = User::first() ?: User::create(['name' => 'Cashier', 'email' => 'cashier@c.com', 'password' => '123']);
        $this->actingAs($this->user);

        $this->counter = CounterMaster::firstOrCreate(
            ['shop_id' => $this->shop->id, 'code' => 'C77'],
            ['counter_name' => 'Counter 77', 'counter_short_name' => 'C77', 'voucher_prefix' => 'P77', 'is_active' => true]
        );

        $shiftService = app(\App\Services\POS\POSShiftService::class);
        $shift = $shiftService->getActiveShift($this->shop->id, $this->counter->id, $this->user->id);
        if (!$shift) {
            $shiftService->openShift($this->shop->id, $this->counter->id, $this->user->id, 500);
        }

        $this->financialYear = FinancialYear::first() ?: FinancialYear::create([
            'name' => 'FY 2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31'
        ]);

        $this->terminal = PaymentTerminal::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'name' => 'Active Counter Terminal',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TERM-CONCURRENCY-TEST-777',
            'config_data' => ['shared_secret' => 'secret_verify_123'],
            'is_active' => true,
        ]);
    }

    public function test_verified_success_finalizes_order_tenders_and_print_jobs()
    {
        $product = Product::first() ?: Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Product Verify Test',
            'unit_price' => 500.00,
            'quantity' => 10,
        ]);

        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartVerifySuccess',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::PROCESSING,
        ]);

        $tokenData = $this->signer->createSessionToken(
            userId: $this->user->id,
            shopId: $this->shop->id,
            counterId: $this->counter->id,
            terminalId: $this->terminal->terminal_id,
            attemptUuid: $attempt->id,
            operation: 'sale',
            secretKey: 'secret_verify_123'
        );

        $resultPayload = [
            'bridge_token' => $tokenData['token'],
            'status' => 'success',
            'approved_amount' => 500.00,
            'currency' => 'INR',
            'transaction_id' => 'TX-VERIFY-SUCCESS-111',
            'rrn' => '771234567890',
        ];

        $checkoutData = [
            'counter_id' => $this->counter->id,
            'customer_phone' => '9999999999',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1, 'rate' => 500.00]
            ],
            'subtotal' => 500.00,
            'payable_amount' => 500.00,
        ];

        $res = $this->verificationService->verifyAndFinalize($attempt->id, $resultPayload, $checkoutData, $this->shop, $this->financialYear);

        $this->assertTrue($res['status']);
        $this->assertFalse($res['already_finalized']);
        $this->assertInstanceOf(Order::class, $res['order']);
        $this->assertEquals(PosPaymentAttemptStatus::FINALIZED, $res['attempt']->status);

        $tender = PosPaymentTender::where('pos_payment_attempt_id', $attempt->id)->first();
        $this->assertNotNull($tender);
        $this->assertEquals(500.00, $tender->amount);

        $printJob = PosPrintJob::where('order_id', $res['order']->id)->first();
        $this->assertNotNull($printJob);
    }

    public function test_amount_mismatch_fails_and_locks_attempt()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartMismatchTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::PROCESSING,
        ]);

        $tokenData = $this->signer->createSessionToken(
            userId: $this->user->id,
            shopId: $this->shop->id,
            counterId: $this->counter->id,
            terminalId: $this->terminal->terminal_id,
            attemptUuid: $attempt->id,
            operation: 'sale',
            secretKey: 'secret_verify_123'
        );

        $resultPayload = [
            'bridge_token' => $tokenData['token'],
            'status' => 'success',
            'approved_amount' => 450.00,
            'currency' => 'INR',
            'transaction_id' => 'TX-MISMATCH-111',
        ];

        $this->expectException(PaymentAmountMismatchException::class);

        $this->verificationService->verifyAndFinalize($attempt->id, $resultPayload, [], $this->shop, $this->financialYear);
    }

    public function test_duplicate_success_callback_returns_existing_order_without_double_commit()
    {
        $product = Product::first() ?: Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Product Dup Test',
            'unit_price' => 500.00,
            'quantity' => 10,
        ]);

        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartDupCallbackTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::PROCESSING,
        ]);

        $tokenData = $this->signer->createSessionToken(
            userId: $this->user->id,
            shopId: $this->shop->id,
            counterId: $this->counter->id,
            terminalId: $this->terminal->terminal_id,
            attemptUuid: $attempt->id,
            operation: 'sale',
            secretKey: 'secret_verify_123'
        );

        $resultPayload = [
            'bridge_token' => $tokenData['token'],
            'status' => 'success',
            'approved_amount' => 500.00,
            'currency' => 'INR',
            'transaction_id' => 'TX-DUP-VERIFY-111',
        ];

        $checkoutData = [
            'counter_id' => $this->counter->id,
            'customer_phone' => '9999999999',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1, 'rate' => 500.00]
            ],
            'subtotal' => 500.00,
            'payable_amount' => 500.00,
        ];

        $res1 = $this->verificationService->verifyAndFinalize($attempt->id, $resultPayload, $checkoutData, $this->shop, $this->financialYear);
        $this->assertFalse($res1['already_finalized']);
        $orderId1 = $res1['order']->id;

        $res2 = $this->verificationService->verifyAndFinalize($attempt->id, $resultPayload, $checkoutData, $this->shop, $this->financialYear);
        $this->assertTrue($res2['already_finalized']);
        $this->assertEquals($orderId1, $res2['order']->id);
    }
}
