<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\Order;
use App\Models\POSReturn;
use App\Services\Accounting\Gstr1ReportingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class Gstr1ReportingTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $gstr1Service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gstr1Service = app(Gstr1ReportingService::class);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'GSTR1 Test Shop', 'email' => 'gstr1@test.com', 'phone' => '123', 'state_id' => 24]);
    }

    public function test_generate_gstr1_data_and_export_json()
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        $order = Order::create([
            'uuid' => (string)\Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'order_code' => 'ORD-GSTR1-001',
            'order_number' => 'ORD-GSTR1-001',
            'total_amount' => 1180.00,
            'payable_amount' => 1180.00,
            'total_taxable_amount' => 1000.00,
            'tax_amount' => 180.00,
            'payment_method' => \App\Enums\PaymentMethod::CASH,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
            'customer_state' => 24,
        ]);

        $gstr1 = $this->gstr1Service->generateGstr1Data($this->shop->id, $startDate, $endDate);

        $this->assertArrayHasKey('b2cs', $gstr1);
        $this->assertArrayHasKey('hsn', $gstr1);
        $this->assertArrayHasKey('doc_summary', $gstr1);

        $json = $this->gstr1Service->exportGstr1Json($this->shop->id, $startDate, $endDate);
        $this->assertJson($json);
    }
}
