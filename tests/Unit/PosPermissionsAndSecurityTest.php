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
use App\Enums\PosPaymentMethod;
use App\Repositories\PosTerminalEventRepository;
use App\Services\POS\BridgeResponseVerifier;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PosPermissionsAndSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $counter;
    protected $terminal;
    protected $eventRepository;
    protected $responseVerifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = app(PosTerminalEventRepository::class);
        $this->responseVerifier = app(BridgeResponseVerifier::class);

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Security Shop', 'email' => 'sec@shop.com', 'phone' => '123']);
        $this->user = User::first() ?: User::create(['name' => 'Sec User', 'email' => 'sec@user.com', 'password' => '123']);
        $this->actingAs($this->user);

        $this->counter = CounterMaster::firstOrCreate(
            ['shop_id' => $this->shop->id, 'code' => 'SEC1'],
            ['counter_name' => 'Security Counter', 'counter_short_name' => 'SEC1', 'voucher_prefix' => 'SEC', 'is_active' => true]
        );

        $this->terminal = PaymentTerminal::create([
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'name' => 'Secure Terminal',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TERM-SEC-100',
            'config_data' => ['shared_secret' => 'super_secret_key_999', 'merchant_key' => 'merch_key_123'],
            'is_active' => true,
        ]);
    }

    public function test_config_data_is_hidden_and_encrypted_in_payment_terminal()
    {
        $array = $this->terminal->toArray();
        $this->assertArrayNotHasKey('config_data', $array);

        $freshTerminal = PaymentTerminal::find($this->terminal->id);
        $this->assertEquals('super_secret_key_999', $freshTerminal->config_data['shared_secret']);
    }

    public function test_sensitive_payload_redaction_in_event_repository()
    {
        $attempt = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $this->terminal->id,
            'terminal_id' => $this->terminal->terminal_id,
            'shop_id' => $this->shop->id,
            'cashier_id' => $this->user->id,
            'cart_name' => 'CartSecurityTest',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
            'status' => PosPaymentAttemptStatus::PROCESSING,
        ]);

        $sensitivePayload = [
            'card_number' => '4532015588881234',
            'cvv' => '999',
            'pin' => '1234',
            'safe_field' => 'allowed_value',
        ];

        $event = $this->eventRepository->logEvent(
            $attempt->id,
            'SECURITY_PAYLOAD_TEST',
            'created',
            'processing',
            $sensitivePayload
        );

        $payload = $event->payload;
        $this->assertEquals('[REDACTED]', $payload['card_number']);
        $this->assertEquals('[REDACTED]', $payload['cvv']);
        $this->assertEquals('[REDACTED]', $payload['pin']);
        $this->assertEquals('allowed_value', $payload['safe_field']);
        $this->assertArrayHasKey('_audit', $payload);
        $this->assertEquals($this->user->id, $payload['_audit']['user_id']);
    }

    public function test_bridge_response_verifier_validates_hmac_signature()
    {
        $signer = app(\App\Services\POS\BridgeRequestSigner::class);
        $secret = 'super_secret_key_999';

        $sessionData = $signer->createSessionToken(
            userId: $this->user->id,
            shopId: $this->shop->id,
            counterId: $this->counter->id,
            terminalId: $this->terminal->terminal_id,
            attemptUuid: 'ATT-1234',
            operation: 'sale',
            secretKey: $secret,
            ttlSeconds: 60
        );

        $expectedContext = [
            'user_id' => $this->user->id,
            'shop_id' => $this->shop->id,
            'counter_id' => $this->counter->id,
            'terminal_id' => $this->terminal->terminal_id,
            'attempt_id' => 'ATT-1234',
        ];

        $decoded = $this->responseVerifier->verifySessionToken($sessionData['token'], $expectedContext, $secret);
        $this->assertEquals('ATT-1234', $decoded['attempt_id']);
    }
}
