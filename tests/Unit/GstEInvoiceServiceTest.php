<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\Order;
use App\Services\Accounting\GstEInvoiceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GstEInvoiceServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $eInvoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eInvoiceService = app(GstEInvoiceService::class);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'EInvoice Test Shop', 'email' => 'einv@test.com', 'phone' => '123']);
    }

    public function test_generate_einvoice_irn_and_eway_bill()
    {
        $order = Order::create([
            'uuid' => (string)\Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'order_code' => 'ORD-EINV-001',
            'order_number' => 'ORD-EINV-001',
            'total_amount' => 60000.00,
            'payable_amount' => 60000.00,
            'total_taxable_amount' => 50847.46,
            'tax_amount' => 9152.54,
            'payment_method' => \App\Enums\PaymentMethod::CASH,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
        ]);

        $einvoice = $this->eInvoiceService->generateEInvoiceIRN($order);
        $this->assertEquals(64, strlen($einvoice['irn']));
        $this->assertNotEmpty($einvoice['signed_qr_code']);

        $eway = $this->eInvoiceService->generateEWayBill($order, [
            'transporter_id' => '27AAAAA0000A1Z5',
            'vehicle_no' => 'GJ01AB1234',
        ]);
        $this->assertNotEmpty($eway['eway_bill_no']);
    }
}
