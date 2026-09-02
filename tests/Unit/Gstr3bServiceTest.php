<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\Order;
use App\Services\Accounting\Gstr3bService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class Gstr3bServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $gstr3bService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gstr3bService = app(Gstr3bService::class);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'GSTR3B Test Shop', 'email' => 'gstr3b@test.com', 'phone' => '123', 'state_id' => 24]);
    }

    public function test_compute_gstr3b_tax_summary_and_net_cash_payable()
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        Order::create([
            'uuid' => (string)\Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'order_code' => 'ORD-GSTR3B-001',
            'order_number' => 'ORD-GSTR3B-001',
            'total_amount' => 1180.00,
            'payable_amount' => 1180.00,
            'total_taxable_amount' => 1000.00,
            'tax_amount' => 180.00,
            'payment_method' => \App\Enums\PaymentMethod::CASH,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
            'customer_state' => 24,
        ]);

        $gstr3b = $this->gstr3bService->computeGstr3b($this->shop->id, $startDate, $endDate);

        $this->assertEquals(1000.00, $gstr3b['outward_supplies']['taxable_value']);
        $this->assertEquals(90.00, $gstr3b['outward_supplies']['cgst']);
        $this->assertEquals(90.00, $gstr3b['outward_supplies']['sgst']);
        $this->assertGreaterThanOrEqual(0, $gstr3b['net_cash_payable']['total_cash_payable']);
    }
}
