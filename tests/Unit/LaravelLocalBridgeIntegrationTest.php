<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\CounterMaster;
use App\Models\PaymentTerminal;
use App\Models\PosPaymentAttempt;
use App\Enums\PaymentTerminalProvider;
use App\Enums\PosPaymentAttemptStatus;
use App\Services\POS\BridgeRequestSigner;
use App\Services\POS\BridgeResponseVerifier;
use App\Exceptions\BridgeAuthenticationException;
use App\Exceptions\PaymentAmountMismatchException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LaravelLocalBridgeIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $counter;
    protected $terminal;
    protected $signer;
    protected $verifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signer = new BridgeRequestSigner();
        $this->verifier = new BridgeResponseVerifier();

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Test Shop', 'email' => 's@s.com', 'phone' => '123']);
        $this->user = User::first() ?: User::create(['name' => 'Cashier', 'email' => 'cashier@c.com', 'password' => '123']);
        $this->actingAs($this->user);

        $this->counter = CounterMaster::firstOrCreate(
            ['shop_id' => $this->shop->id, 'code' => 'C88'],
            ['counter_name' => 'Counter 88', 'counter_short_name' => 'C88', 'voucher_prefix' => 'P88', 'is_active' => true]
        );

        $this->terminal = PaymentTerminal::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'name' => 'Active Counter Terminal',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TERM-BRIDGE-TEST-888',
            'config_data' => ['shared_secret' => 'super_secret_key_123'],
            'is_active' => true,
        ]);
    }

    public function test_invalid_bridge_signature_is_rejected()
    {
        $tokenData = $this->signer->createSessionToken(
            userId: $this->user->id,
            shopId: $this->shop->id,
            counterId: $this->counter->id,
            terminalId: $this->terminal->terminal_id,
            attemptUuid: 'uuid-123',
            operation: 'sale',
            secretKey: 'super_secret_key_123'
        );

        $this->expectException(BridgeAuthenticationException::class);
        $this->expectExceptionMessage("Bridge session token signature mismatch");

        $this->verifier->verifySessionToken($tokenData['token'], [
            'attempt_id' => 'uuid-123',
        ], 'WRONG_SECRET_KEY');
    }

    public function test_expired_token_is_rejected()
    {
        $tokenData = $this->signer->createSessionToken(
            userId: $this->user->id,
            shopId: $this->shop->id,
            counterId: $this->counter->id,
            terminalId: $this->terminal->terminal_id,
            attemptUuid: 'uuid-123',
            operation: 'sale',
            secretKey: 'super_secret_key_123',
            ttlSeconds: -10
        );

        $this->expectException(BridgeAuthenticationException::class);
        $this->expectExceptionMessage("expired");

        $this->verifier->verifySessionToken($tokenData['token'], [
            'attempt_id' => 'uuid-123',
        ], 'super_secret_key_123');
    }

    public function test_mismatched_attempt_uuid_or_counter_is_rejected()
    {
        $tokenData = $this->signer->createSessionToken(
            userId: $this->user->id,
            shopId: $this->shop->id,
            counterId: $this->counter->id,
            terminalId: $this->terminal->terminal_id,
            attemptUuid: 'uuid-correct',
            operation: 'sale',
            secretKey: 'super_secret_key_123'
        );

        $this->expectException(BridgeAuthenticationException::class);
        $this->expectExceptionMessage("attempt_id mismatch");

        $this->verifier->verifySessionToken($tokenData['token'], [
            'attempt_id' => 'uuid-WRONG-ATTEMPT',
        ], 'super_secret_key_123');
    }

    public function test_browser_cannot_modify_requested_amount_and_triggers_mismatch()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartAmountCheck',
            'provider' => 'mock',
            'payment_method' => 'card',
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
            secretKey: 'super_secret_key_123'
        );

        $this->withoutExceptionHandling();
        $this->expectException(PaymentAmountMismatchException::class);

        $response = $this->postJson("/shop/pos/payments/{$attempt->id}/result", [
            'bridge_token' => $tokenData['token'],
            'status' => 'success',
            'approved_amount' => 100.00,
            'transaction_id' => 'TX-TAMPERED-001',
        ]);
    }

    public function test_duplicate_result_callback_is_idempotent()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartIdempotentCallback',
            'provider' => 'mock',
            'payment_method' => 'card',
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::SUCCESS,
        ]);

        $tokenData = $this->signer->createSessionToken(
            userId: $this->user->id,
            shopId: $this->shop->id,
            counterId: $this->counter->id,
            terminalId: $this->terminal->terminal_id,
            attemptUuid: $attempt->id,
            operation: 'sale',
            secretKey: 'super_secret_key_123'
        );

        $response = $this->postJson("/shop/pos/payments/{$attempt->id}/result", [
            'bridge_token' => $tokenData['token'],
            'status' => 'success',
            'approved_amount' => 500.00,
            'transaction_id' => 'TX-DUP-001',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'already_processed' => true,
        ]);
    }
}
