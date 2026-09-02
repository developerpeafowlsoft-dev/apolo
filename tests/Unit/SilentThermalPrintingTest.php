<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\PosPrintJob;
use App\Enums\PosPrintJobStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Services\POS\PosPrintJobService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SilentThermalPrintingTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $user;
    protected $order;
    protected $printJobService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->printJobService = app(PosPrintJobService::class);

        $this->shop = Shop::first() ?: Shop::create(['name' => 'Test Thermal Shop', 'email' => 'ts@ts.com', 'phone' => '123']);
        $this->user = User::first() ?: User::create(['name' => 'Cashier Thermal', 'email' => 'ct@ct.com', 'password' => '123']);
        $this->actingAs($this->user);

        $this->order = Order::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'pos_order' => true,
            'order_code' => 'ORDC-9988',
            'order_number' => 'ORD-PRINT-TEST-001',
            'order_status' => OrderStatus::DELIVERED,
            'payment_status' => PaymentStatus::PAID,
            'payment_method' => \App\Enums\PaymentMethod::CASH,
            'total_amount' => 1000.00,
            'payable_amount' => 1000.00,
        ]);
    }

    public function test_create_print_job_for_order()
    {
        $job = $this->printJobService->createJobForOrder($this->order);

        $this->assertInstanceOf(PosPrintJob::class, $job);
        $this->assertEquals(PosPrintJobStatus::PENDING, $job->status);
        $this->assertEquals($this->order->id, $job->order_id);

        $payload = is_array($job->print_payload) ? $job->print_payload : json_decode($job->print_payload, true);
        $this->assertEquals(80, $payload['paper_width_mm']);
        $this->assertTrue($payload['pulse_cash_drawer']);
    }

    public function test_mark_printed_success()
    {
        $job = $this->printJobService->createJobForOrder($this->order);
        $printedJob = $this->printJobService->markPrinted($job, ['bridge_status' => 'printed']);

        $this->assertEquals(PosPrintJobStatus::PRINTED, $printedJob->status);
        $this->assertEquals(1, $printedJob->attempts);
    }

    public function test_mark_failed_does_not_affect_paid_order()
    {
        $job = $this->printJobService->createJobForOrder($this->order);
        $failedJob = $this->printJobService->markFailed($job, 'Thermal paper out');

        $this->assertEquals(PosPrintJobStatus::FAILED, $failedJob->status);

        $freshOrder = Order::withoutGlobalScopes()->find($this->order->id);
        $this->assertEquals(PaymentStatus::PAID, $freshOrder->payment_status);
    }

    public function test_reprint_order_creates_audited_reprint_job()
    {
        $reprintJob = $this->printJobService->reprintOrder($this->order, $this->user, 'Customer duplicate bill');

        $this->assertStringContainsString('REPRINT', $reprintJob->error_message);
        $this->assertEquals(PosPrintJobStatus::PENDING, $reprintJob->status);
    }
}
