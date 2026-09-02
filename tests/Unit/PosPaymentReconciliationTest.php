<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\CounterMaster;
use App\Models\FinancialYear;
use App\Models\PaymentTerminal;
use App\Models\PosPaymentAttempt;
use App\Models\Order;
use App\Enums\PaymentTerminalProvider;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;
use App\Services\POS\PosPaymentReconciliationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Exception;

class PosPaymentReconciliationTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $counter;
    protected $terminal;
    protected $financialYear;
    protected $reconciliationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reconciliationService = app(PosPaymentReconciliationService::class);

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Recon Shop', 'email' => 'recon@shop.com', 'phone' => '123']);
        $this->user = User::first() ?: User::create(['name' => 'Manager User', 'email' => 'mgr@shop.com', 'password' => '123']);
        $this->actingAs($this->user);

        $this->counter = CounterMaster::firstOrCreate(
            ['shop_id' => $this->shop->id, 'code' => 'C99'],
            ['counter_name' => 'Counter 99', 'counter_short_name' => 'C99', 'voucher_prefix' => 'P99', 'is_active' => true]
        );

        $this->financialYear = FinancialYear::first() ?: FinancialYear::create([
            'name' => 'FY 2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31'
        ]);

        $this->terminal = PaymentTerminal::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'name' => 'Reconciliation Terminal',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TERM-RECON-999',
            'config_data' => ['shared_secret' => 'secret_recon_123'],
            'is_active' => true,
        ]);
    }

    public function test_recover_unknown_attempt_rechecks_gateway()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartUnknownTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::UNKNOWN,
        ]);

        $result = $this->reconciliationService->recoverUnknownAttempt($attempt);
        $this->assertArrayHasKey('status', $result);
    }

    public function test_mark_verified_failure_by_manager()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartMarkFailedTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 300.00,
            'status' => PosPaymentAttemptStatus::UNKNOWN,
        ]);

        $failedAttempt = $this->reconciliationService->markVerifiedFailure($attempt, $this->user, 'Verified customer left store');

        $this->assertEquals(PosPaymentAttemptStatus::FAILED, $failedAttempt->status);
        $this->assertStringContainsString('Verified customer left store', $failedAttempt->response_payload['failure_reason'] ?? '');
    }

    public function test_security_rule_prevents_linking_without_transaction_id()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartUnverifiedLinkTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 300.00,
            'status' => PosPaymentAttemptStatus::CREATED,
            'transaction_id' => null,
        ]);

        $order = Order::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'pos_order' => true,
            'order_code' => 'ORDC-101',
            'order_number' => 'ORD-101',
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'total_amount' => 300.00,
            'payable_amount' => 300.00,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Security Constraint Violation");

        $this->reconciliationService->linkPaymentToOrder($attempt, $order, $this->user);
    }

    public function test_artisan_reconciliation_command_executes_cleanly()
    {
        $recon = $this->reconciliationService->reconcileDailyTerminalPayments($this->shop->id, date('Y-m-d'));
        $this->assertNotNull($recon);
        $this->assertEquals($this->shop->id, $recon->shop_id);
    }
}
