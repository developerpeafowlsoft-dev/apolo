<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\Order;
use App\Models\POSReturn;
use App\Models\User;
use App\Models\Voucher;
use App\Enums\PaymentMethod;
use App\Services\Accounting\GSTPostingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GSTPostingServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $shop;
    protected $customer;
    protected $gstPostingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gstPostingService = app(GSTPostingService::class);
        $this->shop = Shop::first() ?: Shop::create(['name' => 'GST Test Shop', 'email' => 'gst@test.com', 'phone' => '123', 'state_id' => 24]);
        $this->customer = User::first() ?: User::create(['name' => 'Test Customer', 'email' => 'cust@test.com', 'password' => bcrypt('password')]);
    }

    public function test_resolve_place_of_supply_intra_vs_inter_state()
    {
        $intra = $this->gstPostingService->resolvePlaceOfSupply($this->shop->id, 24);
        $this->assertEquals('intra_state', $intra);

        $inter = $this->gstPostingService->resolvePlaceOfSupply($this->shop->id, 27); // Maharashtra (27)
        $this->assertEquals('inter_state', $inter);
    }

    public function test_post_sales_invoice_voucher_intra_state()
    {
        $order = Order::create([
            'uuid' => (string)\Illuminate\Support\Str::uuid(),
            'shop_id' => $this->shop->id,
            'customer_id' => $this->customer->id,
            'order_code' => 'ORD-GST-101',
            'order_number' => 'ORD-GST-101',
            'total_amount' => 1180.00,
            'payable_amount' => 1180.00,
            'total_taxable_amount' => 1000.00,
            'total_tax_amount' => 180.00,
            'tax_amount' => 180.00,
            'payment_method' => PaymentMethod::CASH,
            'payment_status' => \App\Enums\PaymentStatus::PAID,
            'order_status' => \App\Enums\OrderStatus::DELIVERED,
            'customer_state' => 24, // Intra-State Gujarat
        ]);

        $voucher = $this->gstPostingService->postSalesInvoiceVoucher($order);

        $this->assertInstanceOf(Voucher::class, $voucher);
        $this->assertEquals('Sales', $voucher->voucher_type);
        $this->assertCount(4, $voucher->entries); // Dr Cash, Cr Sales, Cr CGST, Cr SGST
    }

    public function test_post_sales_return_voucher()
    {
        $posReturn = POSReturn::create([
            'return_code' => 'RET-GST-201',
            'return_no' => 'RET-GST-201',
            'shop_id' => $this->shop->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 590.00,
            'refund_status' => 'completed',
        ]);

        $voucher = $this->gstPostingService->postSalesReturnVoucher($posReturn);

        $this->assertInstanceOf(Voucher::class, $voucher);
        $this->assertEquals('Credit Note', $voucher->voucher_type);
        $this->assertCount(4, $voucher->entries);
    }
}
