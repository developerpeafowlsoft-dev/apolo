<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Account;
use App\Services\Accounting\FinancialStatementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FinancialStatementServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $statementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statementService = app(FinancialStatementService::class);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'Fin Statement Test Shop', 'email' => 'fin@test.com', 'phone' => '123']);
    }

    public function test_generate_trial_balance_pnl_and_balance_sheet()
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        Order::create([
            'uuid' => (string)\Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'order_code' => 'ORD-FIN-001',
            'order_number' => 'ORD-FIN-001',
            'total_amount' => 5000.00,
            'payable_amount' => 5000.00,
            'total_taxable_amount' => 4237.29,
            'tax_amount' => 762.71,
            'payment_method' => \App\Enums\PaymentMethod::CASH,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
        ]);

        $tb = $this->statementService->generateTrialBalance($this->shop->id, $endDate);
        $this->assertArrayHasKey('totals', $tb);
        $this->assertTrue($tb['totals']['is_balanced']);

        $pnl = $this->statementService->generateProfitAndLoss($this->shop->id, $startDate, $endDate);
        $this->assertEquals(5000.00, $pnl['revenue']['gross_sales']);

        $bs = $this->statementService->generateBalanceSheet($this->shop->id, $endDate);
        $this->assertArrayHasKey('assets', $bs);
    }
}
