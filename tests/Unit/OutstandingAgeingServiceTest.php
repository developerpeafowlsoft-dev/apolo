<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Services\Accounting\OutstandingAgeingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OutstandingAgeingServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $ageingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ageingService = app(OutstandingAgeingService::class);
        $user = User::first() ?: User::create(['name' => 'Ageing User', 'email' => 'ageinguser@test.com', 'password' => bcrypt('password')]);
        $this->shop = Shop::create(['name' => 'Ageing Test Shop ' . rand(100,999), 'user_id' => $user->id, 'email' => 'ageing' . rand(100,999) . '@test.com', 'phone' => '123']);
    }

    public function test_generate_debtors_and_creditors_outstanding_ageing()
    {
        Order::create([
            'uuid' => (string)\Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'order_code' => 'ORD-AGE-001',
            'order_number' => 'ORD-AGE-001',
            'total_amount' => 2500.00,
            'payable_amount' => 2500.00,
            'total_taxable_amount' => 2118.64,
            'tax_amount' => 381.36,
            'payment_method' => \App\Enums\PaymentMethod::CASH,
            'payment_status' => \App\Enums\PaymentStatus::PENDING->value,
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
            'created_at' => now()->subDays(45),
        ]);

        $ageing = $this->ageingService->generateOutstandingAgeing($this->shop->id, 'DEBTORS');

        $this->assertEquals(2500.00, $ageing['summary']['total_outstanding']);
        $this->assertEquals(2500.00, $ageing['summary']['bucket_31_60']);
    }
}
