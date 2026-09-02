<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PaymentTerminal;
use App\Models\PosPaymentAttempt;
use App\Models\PosPaymentTender;
use App\Models\PosTerminalEvent;
use App\Models\PosPrintJob;
use App\Models\PaymentReconciliation;
use App\Models\Shop;
use App\Models\Order;
use App\Models\User;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;
use App\Enums\PaymentTerminalProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class PaymentTerminalDatabaseTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payment_terminal_schema_structures_exist()
    {
        $this->assertTrue(Schema::hasTable('payment_terminals'));
        $this->assertTrue(Schema::hasTable('pos_payment_attempts'));
        $this->assertTrue(Schema::hasTable('pos_payment_tenders'));
        $this->assertTrue(Schema::hasTable('pos_terminal_events'));
        $this->assertTrue(Schema::hasTable('pos_print_jobs'));
        $this->assertTrue(Schema::hasTable('payment_reconciliations'));
    }

    public function test_check_constraints_prevent_negative_values()
    {
        $shop = Shop::first() ?: Shop::create(['name' => 'Test', 'phone' => '123', 'email' => 't@t.com']);
        $user = User::first() ?: User::create(['name' => 'Cashier', 'email' => 'c@c.com', 'password' => '123']);

        $terminal = PaymentTerminal::create([
            'shop_id' => $shop->id,
            'name' => 'Test Term',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TEST-TERM-UUID-999',
            'shared_secret' => 'secret',
        ]);

        $this->expectException(\Exception::class);

        PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $terminal->id,
            'shop_id' => $shop->id,
            'cashier_id' => $user->id,
            'cart_name' => 'CartName',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => -100.00,
            'status' => PosPaymentAttemptStatus::CREATED,
        ]);
    }

    public function test_unique_provider_transaction_id_constraint()
    {
        $shop = Shop::first() ?: Shop::create(['name' => 'Test', 'phone' => '123', 'email' => 't@t.com']);
        $user = User::first() ?: User::create(['name' => 'Cashier', 'email' => 'c@c.com', 'password' => '123']);

        $terminal = PaymentTerminal::create([
            'shop_id' => $shop->id,
            'name' => 'Test Term',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TEST-TERM-UUID-998',
            'shared_secret' => 'secret',
        ]);

        PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $terminal->id,
            'shop_id' => $shop->id,
            'cashier_id' => $user->id,
            'cart_name' => 'CartName',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 100.00,
            'status' => PosPaymentAttemptStatus::CREATED,
            'transaction_id' => 'TX-DUP-111',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $terminal->id,
            'shop_id' => $shop->id,
            'cashier_id' => $user->id,
            'cart_name' => 'CartName',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 200.00,
            'status' => PosPaymentAttemptStatus::CREATED,
            'transaction_id' => 'TX-DUP-111',
        ]);
    }

    public function test_encrypted_casts_are_properly_applied()
    {
        $shop = Shop::first() ?: Shop::create(['name' => 'Test', 'phone' => '123', 'email' => 't@t.com']);
        
        $terminal = PaymentTerminal::create([
            'shop_id' => $shop->id,
            'name' => 'Secret configs',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TERM-SECRET',
            'config_data' => ['secret_api_key' => 'very-secret-token-key-123'],
            'shared_secret' => 'secret',
        ]);

        $terminal->refresh();

        $this->assertEquals('very-secret-token-key-123', $terminal->config_data['secret_api_key']);

        $raw = DB::table('payment_terminals')->where('id', $terminal->id)->value('config_data');
        $this->assertStringNotContainsString('very-secret-token-key-123', $raw);
    }

    public function test_model_scopes_and_status_relationships()
    {
        $shop = Shop::first() ?: Shop::create(['name' => 'Test', 'phone' => '123', 'email' => 't@t.com']);
        $user = User::first() ?: User::create(['name' => 'Cashier', 'email' => 'c@c.com', 'password' => '123']);

        $terminal = PaymentTerminal::create([
            'shop_id' => $shop->id,
            'name' => 'Test Term',
            'provider' => PaymentTerminalProvider::MOCK,
            'terminal_id' => 'TEST-TERM-UUID-888',
            'shared_secret' => 'secret',
        ]);

        $attempt1 = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $terminal->id,
            'shop_id' => $shop->id,
            'cashier_id' => $user->id,
            'cart_name' => 'CartName',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 100.00,
            'status' => PosPaymentAttemptStatus::SENT_TO_TERMINAL,
        ]);

        $attempt2 = PosPaymentAttempt::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => $terminal->id,
            'shop_id' => $shop->id,
            'cashier_id' => $user->id,
            'cart_name' => 'CartName',
            'provider' => 'mock',
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 100.00,
            'status' => PosPaymentAttemptStatus::SUCCESS,
        ]);

        $pending = PosPaymentAttempt::pending()->pluck('id');
        $successful = PosPaymentAttempt::successful()->pluck('id');

        $this->assertTrue($pending->contains($attempt1->id));
        $this->assertFalse($pending->contains($attempt2->id));

        $this->assertTrue($successful->contains($attempt2->id));
        $this->assertFalse($successful->contains($attempt1->id));
    }
}
