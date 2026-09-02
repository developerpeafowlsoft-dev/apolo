<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Product;
use App\Services\Analytics\VisualAnalyticsEngineService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VisualAnalyticsEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = app(VisualAnalyticsEngineService::class);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'Analytics Test Shop', 'email' => 'analytics@test.com', 'phone' => '123']);
    }

    public function test_get_sales_and_stock_analytics_and_rfm()
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        Order::create([
            'uuid' => (string)\Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'order_code' => 'ORD-AN-001',
            'order_number' => 'ORD-AN-001',
            'total_amount' => 1200.00,
            'payable_amount' => 1200.00,
            'total_taxable_amount' => 1016.95,
            'tax_amount' => 183.05,
            'payment_method' => \App\Enums\PaymentMethod::CASH,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
        ]);

        $analytics = $this->analyticsService->getSalesAndStockAnalytics($this->shop->id, $startDate, $endDate);
        $this->assertArrayHasKey('sales_trend', $analytics);
        $this->assertArrayHasKey('payment_distribution', $analytics);

        $rfm = $this->analyticsService->getCustomerRfmSegmentation($this->shop->id);
        $this->assertArrayHasKey('vip_customers', $rfm);
    }
}
