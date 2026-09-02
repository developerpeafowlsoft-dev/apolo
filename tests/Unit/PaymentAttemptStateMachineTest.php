<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\CounterMaster;
use App\Models\POSShift;
use App\Models\PaymentTerminal;
use App\Models\PosPaymentAttempt;
use App\Enums\PaymentTerminalProvider;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;
use App\Services\POS\PosPaymentAttemptService;
use App\Services\POS\POSShiftService;
use App\Repositories\PosPaymentAttemptRepository;
use App\Exceptions\IllegalStateTransitionException;
use App\Exceptions\TerminalUnavailableException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Exception;

class PaymentAttemptStateMachineTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $counter;
    protected $shift;
    protected $terminal;
    protected $attemptService;
    protected $attemptRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attemptService = app(PosPaymentAttemptService::class);
        $this->attemptRepository = app(PosPaymentAttemptRepository::class);

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Test Shop', 'email' => 's@s.com', 'phone' => '123']);
        $this->user = User::first() ?: User::create(['name' => 'Cashier', 'email' => 'cashier@c.com', 'password' => '123']);
        $this->actingAs($this->user);

        $this->counter = CounterMaster::firstOrCreate(
            ['shop_id' => $this->shop->id, 'code' => 'C99'],
            ['counter_name' => 'Counter 99', 'counter_short_name' => 'C99', 'voucher_prefix' => 'P99', 'is_active' => true]
        );

        $shiftService = app(POSShiftService::class);
        $this->shift = $shiftService->getActiveShift($this->shop->id, $this->counter->id, $this->user->id);
        if (!$this->shift) {
            $this->shift = $shiftService->openShift($this->shop->id, $this->counter->id, $this->user->id, 500);
        }

        $this->terminal = PaymentTerminal::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'name' => 'Active Counter Terminal',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TERM-STATE-TEST-' . rand(100, 999),
            'shared_secret' => 'secret_test',
            'is_active' => true,
        ]);
    }

    public function test_one_active_attempt_per_bill_and_two_rapid_clicks_prevention()
    {
        $payload = [
            'counter_id' => $this->counter->id,
            'cart_name' => 'CartRapid123',
            'amount' => 500.00,
            'payment_method' => 'card',
        ];

        $attempt1 = $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
        $this->assertNotNull($attempt1['attempt_id']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("An unresolved payment attempt");

        $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
    }

    public function test_inactive_terminal_throws_exception()
    {
        $this->terminal->update(['is_active' => false]);

        $payload = [
            'counter_id' => $this->counter->id,
            'cart_name' => 'CartInactiveTest',
            'amount' => 500.00,
        ];

        $this->expectException(TerminalUnavailableException::class);

        $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
    }

    public function test_closed_shift_throws_exception()
    {
        $shiftService = app(POSShiftService::class);
        $shiftService->closeShift($this->shift, 500);

        $payload = [
            'counter_id' => $this->counter->id,
            'cart_name' => 'CartClosedShift',
            'amount' => 500.00,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No active cashier shift session found");

        $this->attemptService->createAttempt($payload, $this->shop, $this->user->id);
    }

    public function test_illegal_status_transition_is_rejected()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'IllegalTransitionCart',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::CREATED,
        ]);

        $this->expectException(IllegalStateTransitionException::class);

        $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::FINALIZED);
    }

    public function test_valid_state_transition_sequence_succeeds()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'ValidTransitionCart',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::CREATED,
        ]);

        $t1 = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::SENT_TO_TERMINAL);
        $this->assertEquals(PosPaymentAttemptStatus::SENT_TO_TERMINAL, $t1->status);

        $t2 = $this->attemptRepository->transition($t1, PosPaymentAttemptStatus::AWAITING_CUSTOMER);
        $this->assertEquals(PosPaymentAttemptStatus::AWAITING_CUSTOMER, $t2->status);

        $t3 = $this->attemptRepository->transition($t2, PosPaymentAttemptStatus::PROCESSING);
        $this->assertEquals(PosPaymentAttemptStatus::PROCESSING, $t3->status);

        $t4 = $this->attemptRepository->transition($t3, PosPaymentAttemptStatus::SUCCESS);
        $this->assertEquals(PosPaymentAttemptStatus::SUCCESS, $t4->status);

        $t5 = $this->attemptRepository->transition($t4, PosPaymentAttemptStatus::VERIFIED);
        $this->assertEquals(PosPaymentAttemptStatus::VERIFIED, $t5->status);

        $t6 = $this->attemptRepository->transition($t5, PosPaymentAttemptStatus::FINALIZED);
        $this->assertEquals(PosPaymentAttemptStatus::FINALIZED, $t6->status);
        $this->assertNotNull($t6->finalized_at);
    }

    public function test_reuse_of_terminal_transaction_id_throws_exception()
    {
        PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'TxReuse1',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::SUCCESS,
            'transaction_id' => 'TX-UNIQUE-REF-777',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'TxReuse2',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::SUCCESS,
            'transaction_id' => 'TX-UNIQUE-REF-777',
        ]);
    }
}
